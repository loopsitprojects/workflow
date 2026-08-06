<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function show(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired invitation link.');
        }

        $role = $request->query('role');
        
        return view('auth.register', compact('role'));
    }

    public function store(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired invitation link.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $role = $request->query('role');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
        ]);

        if ($role === 'Operations Manager') {
            $brands = \App\Models\Brand::all();
            foreach ($brands as $brand) {
                $brand->team()->attach($user->id, ['role' => 'Operations Manager']);
            }
        }

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Account created successfully!');
    }
}
