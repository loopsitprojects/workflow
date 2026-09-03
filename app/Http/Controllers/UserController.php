<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Invitation;
use App\Models\Brand;
use App\Http\Controllers\Admin\MaintenanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:30|alpha_dash|unique:users',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role'     => ['required', Rule::in(['Admin', 'Operations Manager', 'Writer', 'Approver', 'Approver Coordinator', 'Brand Manager', 'Designer', 'Coordinator'])],
        ]);

        $user = User::create([
            'name'     => strtolower($validated['username']),
            'username' => strtolower($validated['username']),
            'email'    => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        if ($user->role === 'Operations Manager') {
            $user->brands()->sync(\App\Models\Brand::pluck('id')->toArray());
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => ['required', Rule::in(['Admin', 'Operations Manager', 'Writer', 'Approver', 'Approver Coordinator', 'Brand Manager', 'Designer', 'Coordinator'])],
        ]);

        $user->name     = strtolower($validated['username']);
        $user->username = strtolower($validated['username']);
        $user->email    = strtolower($validated['email']);
        $user->role     = $validated['role'];

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if ($user->role === 'Operations Manager') {
            $user->brands()->syncWithoutDetaching(\App\Models\Brand::pluck('id')->toArray());
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'You cannot delete yourself.');
        }

        try {
            $user->delete();
            return redirect()->route('users.index')->with('success', 'User deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('users.index')->with('error', 'Integrity Error: This user is assigned to active projects or brands and cannot be removed.');
        }
    }

    /**
     * Admin Settings Dashboard
     */
    public function invite(Request $request)
    {
        $request->validate(['role' => 'required|string']);
        
        $token = \Illuminate\Support\Str::random(10);
        
        \App\Models\Invitation::create([
            'token' => $token,
            'role' => $request->role,
            'expires_at' => now()->addDays(7),
        ]);
        
        $url = url('/invite/' . $token);
        
        return response()->json(['url' => $url]);
    }

    public function settings()
    {
        $maintenance = \App\Http\Controllers\Admin\MaintenanceController::getStatus();
        $clientReviewEnabled = \App\Services\FeatureManager::isClientReviewEnabled();
        return view('admin.settings', compact('maintenance', 'clientReviewEnabled'));
    }

    public function toggleClientReview(Request $request)
    {
        $current = \App\Services\FeatureManager::isClientReviewEnabled();
        $newStatus = !$current;

        \App\Services\FeatureManager::setClientReviewEnabled($newStatus);

        $msg = $newStatus
            ? 'Send to Client feature has been ENABLED. Team members can now send artwork proofs to clients.'
            : 'Send to Client feature has been DISABLED. External client review sessions are now paused.';

        return redirect()->route('admin.settings')->with('success', $msg);
    }
}

