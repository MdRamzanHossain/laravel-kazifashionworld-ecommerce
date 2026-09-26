<?php

namespace App\Services;

use App\Models\User;
use App\Models\LoyaltyTransaction;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    public static function addPoints(User $user, int $points, string $description, ?int $orderId = null, ?int $reviewId = null): void
    {
        if ($points <= 0) return;

        DB::transaction(function () use ($user, $points, $description, $orderId, $reviewId) {
            LoyaltyTransaction::create([
                'user_id' => $user->id,
                'type' => 'earned',
                'points' => $points,
                'description' => $description,
                'order_id' => $orderId,
                'review_id' => $reviewId
            ]);
            $user->increment('points_balance', $points);
        });
    }

    public static function redeemPoints(User $user, int $points, string $description, ?int $orderId = null): void
    {
        if ($points <= 0 || $user->points_balance < $points) return;

        DB::transaction(function () use ($user, $points, $description, $orderId) {
            LoyaltyTransaction::create([
                'user_id' => $user->id,
                'type' => 'redeemed',
                'points' => -$points,
                'description' => $description,
                'order_id' => $orderId
            ]);
            $user->decrement('points_balance', $points);
        });
    }
}
