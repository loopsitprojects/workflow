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
        \Illuminate\Support\Facades\Gate::before(function (\App\Models\User $user, $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });

        \Illuminate\Support\Facades\Gate::define('create-deliverable', function (\App\Models\User $user) {
            return in_array($user->role, ['Brand Manager', 'Writer']);
        });
    }
}
