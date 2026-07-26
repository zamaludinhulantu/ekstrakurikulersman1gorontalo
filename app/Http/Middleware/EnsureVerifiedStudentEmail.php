<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerifiedStudentEmail
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole(User::ROLE_STUDENT) || $user->hasVerifiedEmail()) {
            return $next($request);
        }

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('verification.notice', ['email' => $user->email])
            ->with('error', 'Email akun siswa belum diverifikasi. Silakan cek inbox email aktif Anda terlebih dahulu.');
    }
}
