<?php

namespace App\Http\Controllers;

use App\Mail\SendOtpMail;
use App\Models\PasswordOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'We could not find an account associated with this email address.',
        ]);

        $email = strtolower(trim($request->input('email')));
        $otp = sprintf('%06d', random_int(0, 999999));

        // Delete any existing OTPs for this email
        PasswordOtp::where('email', $email)->delete();

        // Create new OTP valid for 15 minutes
        PasswordOtp::create([
            'email'      => $email,
            'otp'        => $otp,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Send email
        try {
            Mail::to($email)->send(new SendOtpMail($otp));
        } catch (Exception $e) {
            Log::error('Failed to send OTP email: ' . $e->getMessage());
        }

        session(['reset_email' => $email]);

        return redirect()->route('password.otp.show')->with('success', 'An OTP has been sent to your email address.');
    }

    public function showVerifyOtpForm()
    {
        $email = session('reset_email');
        if (!$email) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-otp', compact('email'));
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp'   => ['required', 'string', 'size:6'],
        ]);

        $email = strtolower(trim($request->input('email')));
        $otpRecord = PasswordOtp::where('email', $email)
            ->where('otp', trim($request->input('otp')))
            ->first();

        if (!$otpRecord) {
            return back()->withErrors(['otp' => 'Invalid OTP code. Please check and try again.'])->withInput();
        }

        if ($otpRecord->isExpired()) {
            return back()->withErrors(['otp' => 'This OTP code has expired. Please request a new code.'])->withInput();
        }

        // Mark OTP verified in session
        session([
            'otp_verified_email' => $email,
        ]);

        return redirect()->route('password.reset')->with('success', 'OTP verified successfully. Please enter your new password.');
    }

    public function showResetPasswordForm()
    {
        $email = session('otp_verified_email');
        if (!$email) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password', compact('email'));
    }

    public function resetPassword(Request $request)
    {
        $email = session('otp_verified_email');
        if (!$email) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('password.request')->withErrors(['email' => 'User account not found.']);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        // Clean up OTPs and session tokens
        PasswordOtp::where('email', $email)->delete();
        session()->forget(['reset_email', 'otp_verified_email']);

        return redirect()->route('login')->with('success', 'Your password has been reset successfully! Please sign in with your new password.');
    }
}
