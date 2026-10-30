<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\MaintenanceController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect()
    {
        $driver = Socialite::driver('google');

        $hostedDomain = config('services.google.hosted_domain');
        if (!empty($hostedDomain)) {
            $driver->with(['hd' => ltrim($hostedDomain, '@')]);
        }

        return $driver->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('login')->withErrors([
                'login' => 'Google sign-in was cancelled or encountered an error.',
            ]);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Exception $e) {
            Log::error('Google OAuth callback error: ' . $e->getMessage());
            return redirect()->route('login')->withErrors([
                'login' => 'Failed to connect with Google. Please try again.',
            ]);
        }

        $email = strtolower(trim($googleUser->getEmail()));
        $hostedDomain = config('services.google.hosted_domain');

        // Enforce corporate domain if specified
        if (!empty($hostedDomain)) {
            $expectedDomain = '@' . ltrim($hostedDomain, '@');
            if (!Str::endsWith($email, $expectedDomain)) {
                return redirect()->route('login')->withErrors([
                    'login' => "Access denied. Only {$expectedDomain} Google accounts are authorized to sign in.",
                ]);
            }
        }

        // 1. Locate existing pre-provisioned user by google_id or existing email
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $email)
            ->first();

        // 2. Reject unregistered users (pre-provisioned policy)
        if (!$user) {
            return redirect()->route('login')->withErrors([
                'login' => 'No active Loops account found matching this Google email. Please contact an administrator for an invitation link.',
            ]);
        }

        // 3. Link Google ID & update avatar if not already set
        $updates = [];
        if ($user->google_id !== $googleUser->getId()) {
            $updates['google_id'] = $googleUser->getId();
        }
        if (!$user->avatar && $googleUser->getAvatar()) {
            $updates['avatar'] = $googleUser->getAvatar();
        }
        if (!empty($updates)) {
            $user->update($updates);
        }

        // 4. Maintenance mode check (consistent with standard password login)
        $maintenance = MaintenanceController::getStatus();
        if (!empty($maintenance['enabled']) && !$user->isAdmin()) {
            return redirect()->route('login')->withErrors([
                'login' => 'The system is currently under maintenance. Only Administrators can log in at this time.',
            ]);
        }

        // 5. Log user in and regenerate session
        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
