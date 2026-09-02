<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'logout',
            'api/*',
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Gracefully handle 419 Page / Session Expired across the app
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->is('logout') || $request->is('*/logout')) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login');
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesi telah kedaluwarsa.'], 419);
            }

            return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir. Silakan masuk kembali.');
        });
    })->create();
