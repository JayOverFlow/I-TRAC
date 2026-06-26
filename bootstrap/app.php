<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\CheckRole;

use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // <-- Add this line
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api', // <-- And add this line
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\UpdateUserLastSeen::class,
        ]);
        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
            'role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Please try again later.',
                    'errors' => [
                        'email' => ['Too many requests. Please try again later.'],
                        'username' => ['Too many requests. Please try again later.'],
                        'tup_id' => ['Too many requests. Please try again later.']
                    ]
                ], 422);
            }

            return back()->withErrors([
                'all_fields' => 'Too many requests. Please try again later.',
                'username' => 'Too many requests. Please try again later.'
            ])->withInput();
        });
    })->create();
