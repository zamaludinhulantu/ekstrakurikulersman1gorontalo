<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
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
        return view('auth.register', [
            'classOptions' => Student::registrationClassOptions(),
        ]);
    }

    public function showForgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $status = Password::sendResetLink([
            'email' => $validated['email'],
        ]);

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    public function showResetPasswordForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __($status))
            : back()->withErrors(['email' => __($status)])->withInput($request->only('email'));
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ], [
            'email' => 'email',
            'password' => 'password',
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
            'class_name' => ['nullable', Rule::in(array_keys(Student::registrationClassOptions()))],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'date_of_birth' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah terdaftar, gunakan email lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'class_name.in' => 'Pilihan kelas tidak valid.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Pilihan jenis kelamin tidak valid.',
            'date_of_birth.date' => 'Tanggal lahir tidak valid.',
            'phone.max' => 'No. telepon maksimal 30 karakter.',
            'parent_phone.max' => 'No. telepon orang tua maksimal 30 karakter.',
            'parent_name.max' => 'Nama orang tua / wali maksimal 255 karakter.',
        ], [
            'name' => 'nama lengkap',
            'email' => 'email',
            'password' => 'password',
            'password_confirmation' => 'konfirmasi password',
            'class_name' => 'kelas',
            'gender' => 'jenis kelamin',
            'date_of_birth' => 'tanggal lahir',
            'phone' => 'no. telepon',
            'address' => 'alamat',
            'parent_name' => 'nama orang tua / wali',
            'parent_phone' => 'no. telepon orang tua',
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
                'class_name' => Student::normalizeClassName($validated['class_name'] ?? null),
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
