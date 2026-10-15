<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (request()->server('HTTP_X_FORWARDED_PROTO') === 'https' || request()->isSecure() || env('FORCE_HTTPS', false)) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\Gate::before(function (\App\Models\User $user, $ability) {
            if ($user->isAdmin() || in_array($user->role, ['Admin', 'Owner'])) {
                return true;
            }
        });

        \Illuminate\Support\Facades\Gate::define('create-deliverable', function (\App\Models\User $user, ?\App\Models\Project $project = null) {
            if ($project && in_array($project->workflow_type, ['campaign', 'pitch'])) {
                return true;
            }
            return in_array($user->role, [
                'Admin',
                'Owner',
                'Brand Manager',
                'Operations Manager',
                'Writer',
                'Assignee',
                'Coordinator',
                'Approver Coordinator',
                'Approver',
                'Designer',
            ]);
        });
    }
}
