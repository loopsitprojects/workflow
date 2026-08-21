<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\MaintenanceController;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $status = MaintenanceController::getStatus();

        if ($status['enabled']) {
            // 1. Logged in Admin users are exempt
            if (auth()->check() && method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin()) {
                return $next($request);
            }

            // 2. Allow login/logout & admin routes so admins can authenticate and manage system
            if ($request->is('login') || $request->is('logout') || $request->is('admin/*')) {
                return $next($request);
            }

            // 3. Block all non-admin users with 503 maintenance screen
            return response()->view('errors.503', [
                'message' => $status['message'],
            ], 503);
        }

        return $next($request);
    }
}
