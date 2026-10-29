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
            'username'  => 'required|string|max:30|alpha_dash|unique:users',
            'job_title' => 'nullable|string|max:100',
            'email'     => 'required|string|email|max:255|unique:users',
            'password'  => 'required|string|min:8|confirmed',
            'role'      => ['required', Rule::in(['Admin', 'Operations Manager', 'Writer', 'Approver', 'Approver Coordinator', 'Brand Manager', 'Designer', 'Coordinator'])],
        ]);

        $user = User::create([
            'name'      => strtolower($validated['username']),
            'username'  => strtolower($validated['username']),
            'job_title' => $validated['job_title'] ?? null,
            'email'     => strtolower($validated['email']),
            'password'  => Hash::make($validated['password']),
            'role'      => $validated['role'],
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
            'username'  => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'job_title' => 'nullable|string|max:100',
            'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password'  => 'nullable|string|min:8|confirmed',
            'role'      => ['required', Rule::in(['Admin', 'Operations Manager', 'Writer', 'Approver', 'Approver Coordinator', 'Brand Manager', 'Designer', 'Coordinator'])],
        ]);

        $user->name      = strtolower($validated['username']);
        $user->username  = strtolower($validated['username']);
        $user->job_title = $validated['job_title'] ?? null;
        $user->email     = strtolower($validated['email']);
        $user->role      = $validated['role'];

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
        $directDesignEnabled = \App\Services\FeatureManager::isDirectDesignEnabled();
        $analyticsEnabled = \App\Services\FeatureManager::isAnalyticsEnabled();
        $halloweenThemeEnabled = \App\Services\FeatureManager::isHalloweenThemeEnabled();
        $activeTheme = \App\Services\FeatureManager::getActiveTheme();
        $projectTypesStatus = \App\Services\FeatureManager::getProjectTypesStatus();
        return view('admin.settings', compact('maintenance', 'clientReviewEnabled', 'directDesignEnabled', 'analyticsEnabled', 'halloweenThemeEnabled', 'activeTheme', 'projectTypesStatus'));
    }

    public function toggleAnalytics(Request $request)
    {
        $newStatus = \App\Services\FeatureManager::toggleAnalytics();

        $msg = $newStatus
            ? 'Analytics & Reports tab has been ENABLED. Team members can now view the analytics reports.'
            : 'Analytics & Reports tab has been DISABLED. The analytics tab is now hidden.';

        return redirect()->to(url()->previous(route('admin.settings')))->with('success', $msg);
    }

    public function toggleDirectDesign(Request $request)
    {
        $newStatus = \App\Services\FeatureManager::toggleDirectDesign();

        $msg = $newStatus
            ? 'Direct Design feature has been ENABLED. Team members can now create direct design deliverables.'
            : 'Direct Design feature has been DISABLED. Direct design creation has been paused.';

        return redirect()->to(url()->previous(route('admin.settings')))->with('success', $msg);
    }

    public function toggleClientReview(Request $request)
    {
        $current = \App\Services\FeatureManager::isClientReviewEnabled();
        $newStatus = !$current;

        \App\Services\FeatureManager::setClientReviewEnabled($newStatus);

        $msg = $newStatus
            ? 'Send to Client feature has been ENABLED. Team members can now send artwork proofs to clients.'
            : 'Send to Client feature has been DISABLED. External client review sessions are now paused.';

        return redirect()->to(url()->previous(route('admin.settings')))->with('success', $msg);
    }

    public function toggleHalloweenTheme(Request $request)
    {
        $newStatus = \App\Services\FeatureManager::toggleHalloweenTheme();

        $msg = $newStatus
            ? 'Halloween Theme (Spooky Mode) has been ENABLED across the platform. All workspaces are now in Halloween theme.'
            : 'Halloween Theme (Spooky Mode) has been DISABLED. Standard themes restored.';

        return redirect()->to(url()->previous(route('admin.settings')))->with('success', $msg);
    }

    public function toggleChristmasTheme(Request $request)
    {
        $newStatus = \App\Services\FeatureManager::toggleChristmasTheme();

        $msg = $newStatus
            ? 'Christmas Theme (Festive Mode 🎄) has been ENABLED across the platform. All workspaces are now in holiday mode.'
            : 'Christmas Theme (Festive Mode 🎄) has been DISABLED. Standard themes restored.';

        return redirect()->to(url()->previous(route('admin.settings')))->with('success', $msg);
    }

    public function updateActiveTheme(Request $request)
    {
        $validated = $request->validate([
            'theme' => 'required|string|in:halloween,christmas,dark,light',
        ]);

        \App\Services\FeatureManager::setActiveTheme($validated['theme']);

        $themeNames = [
            'halloween' => 'Halloween Theme (Spooky Mode 🎃)',
            'christmas' => 'Christmas Holiday Theme (Festive Mode 🎄)',
            'dark' => 'Standard Dark Theme 🌙',
            'light' => 'Standard Light Theme ☀️',
        ];
        $chosen = $themeNames[$validated['theme']] ?? 'Theme';

        return redirect()->route('admin.settings')->with('success', "Platform active theme changed to {$chosen}.");
    }

    public function toggleProjectType(Request $request)
    {
        $validated = $request->validate([
            'project_type' => 'required|string|in:retainer,campaign,pitch',
        ]);

        $type = $validated['project_type'];

        try {
            $newStatus = \App\Services\FeatureManager::toggleProjectType($type);
            $typeName = ucfirst($type);
            $msg = $newStatus
                ? "{$typeName} project type has been ENABLED. Team members can now create {$typeName} projects."
                : "{$typeName} project type has been DISABLED. {$typeName} is now hidden from project creation.";

            return redirect()->to(url()->previous(route('admin.settings')))->with('success', $msg);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.settings')->with('error', $e->getMessage());
        }
    }
}

