<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Invitation;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function show(Request $request, $token)
    {
        $invitation = \App\Models\Invitation::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            abort(403, 'Invalid or expired invitation link.');
        }

        $role = $invitation->role;
        
        return view('auth.register', compact('role', 'token'));
    }

    public function store(Request $request, $token)
    {
        $invitation = \App\Models\Invitation::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            abort(403, 'Invalid or expired invitation link.');
        }

        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $role = $invitation->role;

        $user = User::create([
            'name' => strtolower($request->username),
            'username' => strtolower($request->username),
            'email' => strtolower($request->email),
            'password' => Hash::make($request->password),
            'role' => $role,
        ]);

        if ($role === 'Operations Manager') {
            $user->brands()->syncWithoutDetaching(\App\Models\Brand::pluck('id')->toArray());
        }

        $invitation->delete();

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Account created successfully!');
    }
}
