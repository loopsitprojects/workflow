<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\MaintenanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->intended(route('dashboard'));
        }
        $maintenance = \App\Http\Controllers\Admin\MaintenanceController::getStatus();
        $googleAuthEnabled = \App\Services\FeatureManager::isGoogleAuthEnabled();
        return response()
            ->view('auth.login', compact('maintenance', 'googleAuthEnabled'))
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $login = strtolower(trim(strip_tags($request->input('login'))));
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (!Auth::attempt([$field => $login, 'password' => $request->input('password')], $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'The provided credentials do not match our records.']);
        }

        $user = Auth::user();

        $maintenance = \App\Http\Controllers\Admin\MaintenanceController::getStatus();
        if (!empty($maintenance['enabled']) && !$user->isAdmin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'The system is currently under maintenance. Only Administrators can log in at this time.']);
        }

        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
