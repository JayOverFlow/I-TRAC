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

        // 2. Resolve the user's currently active role based on the active session role context
        $allRoles = $request->user()->roles;
        $activeRoleId = session('active_role_id') ?? ($allRoles->first()?->role_id ?? null);
        $activeRole = $allRoles->where('role_id', $activeRoleId)->first() ?? $allRoles->first();

        // Get the active role type, or fall back to their user type (e.g. Faculty/Staff) if they have no assigned roles
        $activeRoleGen = $activeRole ? $activeRole->gen_role : $request->user()->user_type;

        // 3. Check if the active role matches at least one of the required roles/permissions
        $hasRole = in_array($activeRoleGen, $roles);

        // 4. If they don't have the role, gracefully redirect them to their allowed home page
        if (!$hasRole) {
            if ($activeRole) {
                switch ($activeRole->gen_role) {
                    case 'Head':
                        return redirect()->route('show.dashboard');
                    case 'Procurement':
                    case 'Supply':
                        return redirect()->route('show.procure');
                }
            }
            // Default fallback for unassigned users
            return redirect()->route('show.mr');
        }

        // 5. If they pass, allow them to proceed to the controller
        return $next($request);
    }
}