<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Models\Extracurricular;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau password tidak sesuai.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        if (! Auth::user()->is_active) {
            Auth::logout();

            return redirect()->route('login')->with('error', 'Akun Anda tidak aktif.');
        }

        $pendingExtracurricularId = $request->session()->pull('pending_extracurricular_id');

        if ($pendingExtracurricularId && Auth::user()->hasRole(User::ROLE_STUDENT)) {
            $extracurricular = Extracurricular::query()
                ->whereKey($pendingExtracurricularId)
                ->where('is_active', true)
                ->first();

            if ($extracurricular) {
                return redirect()
                    ->route('student.extracurriculars.show', $extracurricular)
                    ->with('success', 'Login berhasil. Silakan lanjutkan pendaftaran ekstrakurikuler.');
            }
        }

        return redirect()->intended(route('dashboard'));
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'date_of_birth' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => User::ROLE_STUDENT,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'is_active' => true,
            ]);

            Student::create([
                'user_id' => $user->id,
                'nis' => null,
                'class_name' => null,
                'gender' => $validated['gender'],
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'address' => $validated['address'] ?? null,
                'parent_name' => $validated['parent_name'] ?? null,
                'parent_phone' => $validated['parent_phone'] ?? null,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        $pendingExtracurricularId = $request->session()->pull('pending_extracurricular_id');

        if ($pendingExtracurricularId) {
            $extracurricular = Extracurricular::query()
                ->whereKey($pendingExtracurricularId)
                ->where('is_active', true)
                ->first();

            if ($extracurricular) {
                return redirect()
                    ->route('student.extracurriculars.show', $extracurricular)
                    ->with('success', 'Akun siswa berhasil dibuat. Silakan lanjutkan pendaftaran ekstrakurikuler.');
            }
        }

        return redirect()
            ->route('student.dashboard')
            ->with('success', 'Akun siswa berhasil dibuat dan Anda sudah masuk ke sistem.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logout berhasil.');
    }
}
