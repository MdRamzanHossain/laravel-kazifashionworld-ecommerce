<?php

namespace App\Observers;

use App\Models\Review;
use App\Services\LoyaltyService;
use App\Services\SettingService;
use App\Models\LoyaltyTransaction;

class ReviewObserver
{
    public function updated(Review $review): void
    {
        if ($review->isDirty('is_approved') && $review->is_approved) {
            $this->rewardPoints($review);
        }
    }

    protected function rewardPoints(Review $review): void
    {
        if (!SettingService::isLoyaltyEnabled()) return;
        if (!$review->user_id) return;

        $user = $review->user;
        if (!$user) return;

        // Check if already rewarded
        $exists = LoyaltyTransaction::where('review_id', $review->id)
                    ->where('type', 'earned')
                    ->exists();
        if ($exists) return;

        $points = (int) SettingService::getLoyaltyPointsPerReview();

        if ($points > 0) {
            LoyaltyService::addPoints(
                $user,
                $points,
                "Reward for reviewing Product #{$review->product_id}",
                null,
                $review->id
            );
        }
    }
}
