<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Supported social providers
     */
    protected array $supportedProviders = ['google', 'facebook'];

    /**
     * Redirect customer to Google or Facebook OAuth consent screen.
     */
    public function redirect(string $provider): RedirectResponse
    {
        if (!in_array($provider, $this->supportedProviders)) {
            return redirect()->route('login')->with('error', "Authentication provider '{$provider}' is not supported.");
        }

        $clientId = config("services.{$provider}.client_id");
        $clientSecret = config("services.{$provider}.client_secret");

        // If credentials are not set in .env yet, inform the user with friendly guidance
        if (empty($clientId) || empty($clientSecret)) {
            return redirect()->route('login')->with(
                'error',
                ucfirst($provider) . " authentication credentials are not configured yet in .env (Add " . strtoupper($provider) . "_CLIENT_ID and " . strtoupper($provider) . "_CLIENT_SECRET)."
            );
        }

        try {
            return Socialite::driver($provider)->redirect();
        } catch (Exception $e) {
            Log::error("{$provider} OAuth redirect failed: " . $e->getMessage());
            return redirect()->route('login')->with('error', "Failed to connect to " . ucfirst($provider) . ". Please try again.");
        }
    }

    /**
     * Handle provider OAuth callback after customer authorization.
     */
    public function callback(string $provider): RedirectResponse
    {
        if (!in_array($provider, $this->supportedProviders)) {
            return redirect()->route('login')->with('error', "Authentication provider '{$provider}' is not supported.");
        }

        try {
            $socialUser = Socialite::driver($provider)->user();

            $socialId = $socialUser->getId();
            $email = $socialUser->getEmail();
            $name = $socialUser->getName() ?? $socialUser->getNickname() ?? ucfirst($provider) . ' User';
            $avatar = $socialUser->getAvatar();

            // 1. Try to find user by specific social ID
            $user = User::where($provider . '_id', $socialId)->first();

            // 2. If not found by social ID, try matching by verified email address
            if (!$user && !empty($email)) {
                $user = User::where('email', $email)->first();
            }

            if ($user) {
                // Link the social provider ID and avatar if not present
                $updateData = [$provider . '_id' => $socialId];
                if (empty($user->avatar) && !empty($avatar)) {
                    $updateData['avatar'] = $avatar;
                }
                $user->update($updateData);
            } else {
                // 3. Create a new Customer account seamlessly
                $user = User::create([
                    'name'              => $name,
                    'email'             => $email ?? "{$provider}_{$socialId}@kazifashionworld.com",
                    'role'              => 'customer',
                    'is_active'         => true,
                    $provider . '_id'   => $socialId,
                    'avatar'            => $avatar,
                ]);
            }

            // Check if user is deactivated/banned
            if (!$user->is_active) {
                return redirect()->route('login')->with('error', 'Your account has been deactivated. Please contact support.');
            }

            // Log customer in and regenerate session
            Auth::login($user, true);
            session()->regenerate();

            return redirect()->intended('/')->with('success', "Signed in successfully with " . ucfirst($provider) . "! Welcome, {$user->name}.");
        } catch (Exception $e) {
            Log::error("{$provider} OAuth callback error: " . $e->getMessage());
            return redirect()->route('login')->with('error', "Failed to authenticate with " . ucfirst($provider) . ". Please try again or use standard login.");
        }
    }
}
