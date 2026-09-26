<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use App\Models\ShortUrlClick;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class ShortUrlController extends Controller
{
    /**
     * Redirect shortened link to its destination and track click history.
     */
    public function redirect(Request $request, string $code): RedirectResponse
    {
        $shortUrl = ShortUrl::where('code', $code)->first();

        if (!$shortUrl) {
            return redirect()->route('home')->with('error', 'Tracking link not found.');
        }

        // Increment aggregate click counter
        $shortUrl->increment('clicks');

        // Determine device type
        $userAgent = (string) $request->userAgent();
        $deviceType = 'Desktop';
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $userAgent)) {
            $deviceType = 'Tablet';
        } elseif (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile|iphone)/i', $userAgent)) {
            $deviceType = 'Mobile';
        }

        // Record individual click history
        try {
            ShortUrlClick::create([
                'short_url_id' => $shortUrl->id,
                'ip_address'   => $request->ip(),
                'user_agent'   => substr($userAgent, 0, 500),
                'device_type'  => $deviceType,
                'referer'      => substr((string) $request->header('referer'), 0, 500) ?: null,
                'clicked_at'   => now(),
            ]);
        } catch (Throwable $e) {
            // Silently continue redirection if click logging fails
        }

        return redirect()->away($shortUrl->destination_url, 302);
    }
}
