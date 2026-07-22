<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Mail\SystemTestMail;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class SystemController extends Controller
{
    public function index(): View
    {
        return view('super-admin.system.index', [
            'activeSuperAdmins' => User::query()
                ->where('role', User::ROLE_SUPER_ADMIN)
                ->where('is_active', true)
                ->count(),
            'activeAdmins' => User::query()
                ->where('role', User::ROLE_ADMIN)
                ->where('is_active', true)
                ->count(),
            'mailMailer' => (string) config('mail.default'),
            'mailFromAddress' => (string) config('mail.from.address'),
            'appEnvironment' => (string) config('app.env'),
            'emailSettings' => [
                'mail_mailer' => (string) SystemSetting::getValue('mail_mailer', config('mail.default', 'smtp')),
                'mail_smtp_host' => (string) SystemSetting::getValue('mail_smtp_host', config('mail.mailers.smtp.host')),
                'mail_smtp_port' => (string) SystemSetting::getValue('mail_smtp_port', config('mail.mailers.smtp.port')),
                'mail_smtp_username' => (string) SystemSetting::getValue('mail_smtp_username', config('mail.mailers.smtp.username')),
                'mail_smtp_encryption' => (string) SystemSetting::getValue('mail_smtp_encryption', config('mail.mailers.smtp.encryption')),
                'mail_from_address' => (string) SystemSetting::getValue('mail_from_address', config('mail.from.address')),
                'mail_from_name' => (string) SystemSetting::getValue('mail_from_name', config('mail.from.name')),
            ],
        ]);
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mail_mailer' => ['required', 'string', 'max:40'],
            'mail_smtp_host' => ['required', 'string', 'max:255'],
            'mail_smtp_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'mail_smtp_username' => ['nullable', 'string', 'max:255'],
            'mail_smtp_password' => ['nullable', 'string', 'max:255'],
            'mail_smtp_encryption' => ['nullable', 'string', 'max:20'],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:255'],
        ]);

        foreach ([
            'mail_mailer',
            'mail_smtp_host',
            'mail_smtp_port',
            'mail_smtp_username',
            'mail_smtp_encryption',
            'mail_from_address',
            'mail_from_name',
        ] as $key) {
            SystemSetting::setValue($key, $validated[$key] ?? null);
        }

        if (filled($validated['mail_smtp_password'] ?? null)) {
            SystemSetting::setValue('mail_smtp_password', $validated['mail_smtp_password'], true);
        }

        $this->recordAudit(
            action: 'system.email.updated',
            description: 'Konfigurasi email sistem diperbarui.',
            metadata: collect($validated)->except(['mail_smtp_password'])->all()
        );

        return back()->with('success', 'Konfigurasi email berhasil diperbarui.');
    }

    public function maintenance(): View
    {
        return view('super-admin.system.maintenance', [
            'maintenanceDriver' => (string) config('app.maintenance.driver'),
            'isMaintenanceEnabled' => filter_var(SystemSetting::getValue('maintenance_enabled', false), FILTER_VALIDATE_BOOL),
            'maintenanceMessage' => (string) SystemSetting::getValue(
                'maintenance_message',
                'Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo sedang menjalani pemeliharaan.'
            ),
        ]);
    }

    public function sendTestEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'test_email' => ['required', 'email', 'max:255'],
        ]);

        try {
            Mail::to($validated['test_email'])->send(
                new SystemTestMail(
                    "Ini adalah email uji dari Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo pada ".now()->translatedFormat('d F Y H:i')."."
                )
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Email uji gagal dikirim. Periksa kembali host, port, username, password, atau enkripsi SMTP.');
        }

        $this->recordAudit(
            action: 'system.email.test_sent',
            description: 'Super admin mengirim email uji konfigurasi.',
            metadata: [
                'recipient' => $validated['test_email'],
            ]
        );

        return back()->with('success', 'Email uji berhasil dikirim.');
    }

    public function updateMaintenance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'maintenance_enabled' => ['nullable', 'boolean'],
            'maintenance_message' => ['required', 'string', 'max:500'],
        ]);

        $isEnabled = $request->boolean('maintenance_enabled');

        SystemSetting::setValue('maintenance_enabled', $isEnabled ? '1' : '0');
        SystemSetting::setValue('maintenance_message', $validated['maintenance_message']);

        $this->recordAudit(
            action: $isEnabled ? 'system.maintenance.enabled' : 'system.maintenance.disabled',
            description: $isEnabled ? 'Mode maintenance diaktifkan.' : 'Mode maintenance dinonaktifkan.',
            metadata: [
                'message' => $validated['maintenance_message'],
            ]
        );

        return back()->with('success', $isEnabled
            ? 'Mode maintenance berhasil diaktifkan.'
            : 'Mode maintenance berhasil dinonaktifkan.');
    }

    public function auditLogs(Request $request): View
    {
        $search = $request->string('search')->toString();

        $logs = AuditLog::query()
            ->with('user')
            ->when($search, function ($query, $searchValue) {
                $query->where(function ($subQuery) use ($searchValue): void {
                    $subQuery->where('action', 'like', "%{$searchValue}%")
                        ->orWhere('description', 'like', "%{$searchValue}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$searchValue}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('super-admin.system.audit-logs', [
            'logs' => $logs,
            'search' => $search,
        ]);
    }

    private function recordAudit(string $action, string $description, array $metadata = []): void
    {
        $user = auth()->user();

        AuditLog::query()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'subject_type' => 'system',
            'subject_id' => null,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
