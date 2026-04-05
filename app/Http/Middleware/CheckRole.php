<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     * The "...$roles" allows us to pass multiple roles like 'role:Head,Procurement'
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Get all 'gen_role' values for the authenticated user
        // Using pluck() is a fast way to get an array of just the gen_roles
        // Use $request instead of auth()
        $userRoles = $request->user()->roles->pluck('gen_role')->toArray();

        // 3. Check if the user has AT LEAST ONE of the required roles
        $hasRole = !empty(array_intersect($roles, $userRoles));

        // 4. If they don't have the role, block them with a 403 Forbidden error
        if (!$hasRole) {
            abort(403, 'Unauthorized access. You do not have the right role.');
        }

        // 5. If they pass, allow them to proceed to the controller
        return $next($request);
    }
}