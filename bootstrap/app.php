<?php

use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\EnsureAuthenticatedSessionIsActive;
use App\Http\Middleware\EnsureSystemMaintenanceIsHandled;
use App\Http\Middleware\EnsureVerifiedStudentEmail;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'idle.auth' => EnsureAuthenticatedSessionIsActive::class,
            'role' => RoleMiddleware::class,
            'system.maintenance' => EnsureSystemMaintenanceIsHandled::class,
            'verified.student' => EnsureVerifiedStudentEmail::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi Anda sudah berakhir. Silakan masuk kembali.',
                ], 419);
            }

            return redirect()
                ->guest(route('login'))
                ->with('error', 'Sesi Anda sudah berakhir karena tidak ada aktivitas. Silakan masuk kembali.');
        });

        $exceptions->render(function (\Throwable $exception, Request $request) {
            if (! $request->boolean('_notification_redirect')) {
                return null;
            }

            $statusCode = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : null;

            if (! in_array($statusCode, [403, 404], true)) {
                return null;
            }

            return redirect()
                ->route('dashboard')
                ->with('error', 'Halaman tujuan notifikasi tidak tersedia atau Anda tidak memiliki akses.');
        });
    })->create();
