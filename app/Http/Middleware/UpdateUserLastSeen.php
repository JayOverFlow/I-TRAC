<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            Cache::put('last_seen_user_' . Auth::id(), now(), now()->addMinutes(2));
        }

        return $next($request);
    }
}
