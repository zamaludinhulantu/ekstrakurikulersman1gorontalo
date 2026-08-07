<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Models\Extracurricular;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm(): \Illuminate\Http\Response
    {
        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
    }

    public function showRegistrationForm(): \Illuminate\Http\Response
    {
        return response()
            ->view('auth.register', [
                'classOptions' => Student::registrationClassOptions(),
                'prefill' => session('registration_form_data', []),
            ])
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
    }

    public function showForgotPasswordForm(): \Illuminate\Http\Response
    {
        return response()
            ->view('auth.forgot-password')
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
    }


    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user) {
            return back()->with('success', 'Jika email terdaftar, tautan reset password akan dikirim ke email tersebut.');
        }

        if ($this->shouldExposeResetLinkPreview()) {
            $token = Password::broker()->createToken($user);

            return back()
                ->with('success', 'Tautan reset berhasil dibuat untuk pengujian lokal.')
                ->with('reset_link_preview', route('password.reset', [
                    'token' => $token,
                    'email' => $user->email,
                ]));
        }

        if ($this->mailIsNotConfiguredForDelivery()) {
            return back()->with('error', 'Fitur reset password belum aktif karena email server belum dikonfigurasi. Hubungi admin untuk mengaktifkan pengiriman email.');
        }

        $status = Password::sendResetLink([
            'email' => $validated['email'],
        ]);

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    public function showResetPasswordForm(Request $request, string $token): \Illuminate\Http\Response
    {
        return response()
            ->view('auth.reset-password', [
                'token' => $token,
                'email' => $request->string('email')->toString(),
            ])
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
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
        if ($this->mailIsNotConfiguredForDelivery() && ! $this->shouldExposeVerificationLinkPreview()) {
            return back()
                ->with('error', 'Registrasi belum dapat diproses karena pengiriman email verifikasi belum dikonfigurasi. Hubungi admin untuk mengaktifkan email sistem.')
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        $validator = Validator::make($request->all(), [
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

        $validator->after(function ($validator): void {
            $dateOfBirth = $validator->getData()['date_of_birth'] ?? null;
            if (! filled($dateOfBirth)) {
                return;
            }

            try {
                $birthDate = Carbon::parse($dateOfBirth)->startOfDay();
            } catch (\Throwable) {
                return;
            }

            $today = Carbon::today();
            if ($birthDate->gte($today)) {
                $validator->errors()->add(
                    'date_of_birth',
                    'Tanggal lahir harus sebelum '.Carbon::today()->translatedFormat('d F Y').'.'
                );

                return;
            }

            $minimumBirthDate = Carbon::today()->subYears(Student::MIN_REGISTRATION_AGE);
            if ($birthDate->gt($minimumBirthDate)) {
                $validator->errors()->add(
                    'date_of_birth',
                    'Usia minimal saat registrasi adalah '.Student::MIN_REGISTRATION_AGE.' tahun.'
                );
            }
        });

        $validated = $validator->validate();

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

        $verificationPreviewLink = $this->sendOrPreviewVerificationEmail($user);
        $request->session()->put('registration_form_data', collect($validated)
            ->except(['password'])
            ->all());

        $redirect = redirect()
            ->route('verification.notice', ['email' => $user->email])
            ->with('success', 'Pendaftaran akun berhasil. Silakan cek email aktif Anda untuk verifikasi akun sebelum login.');

        if ($verificationPreviewLink) {
            $redirect->with('verification_link_preview', $verificationPreviewLink);
        }

        return $redirect;
    }

    public function showVerificationNotice(Request $request): View
    {
        return view('auth.verify-email', [
            'email' => trim($request->string('email')->toString()),
            'verificationLinkPreview' => session('verification_link_preview'),
        ]);
    }

    public function sendVerificationEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $user = User::query()
            ->where('email', $validated['email'])
            ->where('role', User::ROLE_STUDENT)
            ->first();

        if (! $user) {
            return back()->with('success', 'Jika akun siswa ditemukan, tautan verifikasi akan dikirim ke email tersebut.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route('login')
                ->with('success', 'Email akun ini sudah diverifikasi. Silakan login.');
        }

        if ($this->mailIsNotConfiguredForDelivery() && ! $this->shouldExposeVerificationLinkPreview()) {
            return back()->with('error', 'Pengiriman email verifikasi belum aktif. Hubungi admin untuk mengaktifkan email sistem.');
        }

        $verificationPreviewLink = $this->sendOrPreviewVerificationEmail($user);

        $redirect = back()->with('success', 'Tautan verifikasi berhasil dikirim. Silakan cek inbox email aktif Anda.');

        if ($verificationPreviewLink) {
            $redirect->with('verification_link_preview', $verificationPreviewLink);
        }

        return $redirect;
    }

    public function verifyEmail(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::query()
            ->whereKey($id)
            ->where('role', User::ROLE_STUDENT)
            ->firstOrFail();

        abort_unless(hash_equals((string) $hash, sha1($user->getEmailForVerification())), 403);

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        $request->session()->forget('registration_form_data');

        $pendingExtracurricularId = $request->session()->get('pending_extracurricular_id');

        if ($pendingExtracurricularId) {
            $extracurricular = Extracurricular::query()
                ->whereKey($pendingExtracurricularId)
                ->where('is_active', true)
                ->first();

            if ($extracurricular) {
                return redirect()
                    ->route('login')
                    ->with('success', 'Email berhasil diverifikasi. Silakan login untuk melanjutkan pendaftaran ekstrakurikuler.');
            }
        }

        return redirect()
            ->route('login')
            ->with('success', 'Email berhasil diverifikasi. Silakan login menggunakan akun Anda.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logout berhasil.');
    }

    private function shouldExposeResetLinkPreview(): bool
    {
        return app()->environment('local') && in_array(config('mail.default'), ['log', 'array'], true);
    }

    private function shouldExposeVerificationLinkPreview(): bool
    {
        return $this->shouldExposeResetLinkPreview();
    }

    private function sendOrPreviewVerificationEmail(User $user): ?string
    {
        if ($this->shouldExposeVerificationLinkPreview()) {
            return URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes((int) config('auth.verification.expire', 60)),
                [
                    'id' => $user->getKey(),
                    'hash' => sha1($user->getEmailForVerification()),
                ]
            );
        }

        $user->sendEmailVerificationNotification();

        return null;
    }

    private function mailIsNotConfiguredForDelivery(): bool
    {
        if (app()->runningUnitTests()) {
            return false;
        }

        $mailer = config('mail.default');

        if (in_array($mailer, ['log', 'array'], true)) {
            return true;
        }

        if ($mailer !== 'smtp') {
            return false;
        }

        return blank(config('mail.mailers.smtp.host'))
            || blank(config('mail.from.address'))
            || config('mail.from.address') === 'hello@example.com';
    }
}
