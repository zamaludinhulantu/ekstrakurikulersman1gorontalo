<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit', [
            'user' => auth()->user()->loadMissing('student'),
            'classOptions' => Student::registrationClassOptions(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user()->loadMissing('student');
        $emailChanged = $user->email !== ($request->input('email') ?? $user->email);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'nis' => ['nullable', 'string', 'max:50', Rule::unique('students', 'nis')->ignore($user->student?->id)],
            'class_name' => ['nullable', Rule::in(array_keys(Student::registrationClassOptions()))],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'date_of_birth' => ['nullable', 'date'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
        ]);

        if ($emailChanged && $user->hasRole(User::ROLE_STUDENT) && $this->mailIsNotConfiguredForDelivery() && ! $this->shouldExposeVerificationLinkPreview()) {
            return back()->with('error', 'Email tidak dapat diubah sekarang karena pengiriman email verifikasi belum dikonfigurasi. Hubungi admin untuk mengaktifkan email sistem.');
        }

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            ...((isset($validated['password'])) ? ['password' => $validated['password']] : []),
        ]);

        if ($user->hasRole(\App\Models\User::ROLE_STUDENT) && $user->student) {
            $user->student->update([
                'nis' => filled($validated['nis'] ?? null) ? $validated['nis'] : null,
                'class_name' => filled($validated['class_name'] ?? null) ? Student::normalizeClassName($validated['class_name']) : null,
                'gender' => $validated['gender'] ?? $user->student->gender,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'address' => $validated['address'] ?? null,
                'parent_name' => $validated['parent_name'] ?? null,
                'parent_phone' => $validated['parent_phone'] ?? null,
            ]);
        }

        if ($emailChanged && $user->hasRole(User::ROLE_STUDENT)) {
            $user->markEmailAsUnverified();

            $verificationPreviewLink = $this->sendOrPreviewVerificationEmail($user);

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $redirect = redirect()
                ->route('verification.notice', ['email' => $user->email])
                ->with('success', 'Email berhasil diperbarui. Silakan verifikasi email baru sebelum login kembali.');

            if ($verificationPreviewLink) {
                $redirect->with('verification_link_preview', $verificationPreviewLink);
            }

            return $redirect;
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    private function shouldExposeVerificationLinkPreview(): bool
    {
        return app()->environment('local') && in_array(config('mail.default'), ['log', 'array'], true);
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
