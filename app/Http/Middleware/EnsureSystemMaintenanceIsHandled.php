<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSystemMaintenanceIsHandled
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedRoutes = [
            'login',
            'login.attempt',
            'logout',
            'password.request',
            'password.email',
            'password.reset',
            'password.update',
        ];

        if ($request->routeIs(...$allowedRoutes)) {
            return $next($request);
        }

        $isMaintenanceEnabled = filter_var(
            SystemSetting::getValue('maintenance_enabled', false),
            FILTER_VALIDATE_BOOL
        );

        if (! $isMaintenanceEnabled) {
            return $next($request);
        }

        $user = $request->user();

        if ($user?->hasRole(User::ROLE_SUPER_ADMIN)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Sistem sedang dalam pemeliharaan. Silakan coba lagi nanti.',
            ], 503);
        }

        return response()->view('maintenance', [
            'message' => SystemSetting::getValue(
                'maintenance_message',
                'Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo sedang menjalani pemeliharaan.'
            ),
        ], 503);
    }
}
