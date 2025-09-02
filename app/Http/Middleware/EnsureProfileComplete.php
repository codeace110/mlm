<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip middleware for guest users
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Skip middleware for admin users
        if ($user->is_admin) {
            return $next($request);
        }

        // Routes that don't require profile completion
        $excludedRoutes = [
            'onboarding',
            'onboarding.update',
            'logout',
            'verification.notice',
            'verification.verify',
            'verification.send'
        ];

        if (in_array($request->route()->getName(), $excludedRoutes)) {
            return $next($request);
        }

        // Check if user has completed their profile
        if (!$user->phone || !$user->address || !$user->city || !$user->province || !$user->shipping_name) {
            return redirect()->route('onboarding');
        }

        return $next($request);
    }
}
