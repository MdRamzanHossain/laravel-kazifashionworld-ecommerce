<?php

namespace App\Services;

use App\Models\ShortUrl;
use Illuminate\Support\Str;

class UrlShortenerService
{
    /**
     * Generate or retrieve a shortened URL.
     */
    public static function shorten(string $destinationUrl, ?int $orderId = null): string
    {
        $destinationUrl = trim($destinationUrl);

        if (empty($destinationUrl)) {
            return '';
        }

        // Check if an active short code already exists for this URL and Order
        $existing = ShortUrl::where('destination_url', $destinationUrl)
            ->where('order_id', $orderId)
            ->first();

        if ($existing) {
            return url('/s/' . $existing->code);
        }

        // Generate a unique 6-character code
        do {
            $code = Str::random(6);
        } while (ShortUrl::where('code', $code)->exists());

        ShortUrl::create([
            'code'            => $code,
            'destination_url' => $destinationUrl,
            'order_id'        => $orderId,
            'clicks'          => 0,
        ]);

        return url('/s/' . $code);
    }
}
