<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticatedSessionIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $timeoutMinutes = max(1, (int) config('session.idle_timeout', 15));
        $timeoutSeconds = $timeoutMinutes * 60;
        $sessionKey = 'authenticated_last_activity_at';
        $lastActivityAt = (int) $request->session()->get($sessionKey, 0);
        $now = now()->timestamp;

        if ($lastActivityAt > 0 && ($now - $lastActivityAt) >= $timeoutSeconds) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi Anda sudah berakhir karena tidak ada aktivitas. Silakan masuk kembali.',
                ], 401);
            }

            return redirect()
                ->route('login')
                ->with('error', 'Sesi Anda sudah berakhir karena tidak ada aktivitas. Silakan masuk kembali.');
        }

        $request->session()->put($sessionKey, $now);

        return $next($request);
    }
}
