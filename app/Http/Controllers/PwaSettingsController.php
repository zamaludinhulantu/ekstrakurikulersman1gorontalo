<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use App\Notifications\GenericAppNotification;
use App\Support\PushNotificationSender;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PwaSettingsController extends Controller
{
    public function show(Request $request): View
    {
        $preference = $request->user()->notificationPreference
            ?? $request->user()->notificationPreference()->create([
                'in_app_preferences' => NotificationPreference::defaultInAppPreferences(),
                'push_preferences' => NotificationPreference::defaultPushPreferences(),
                'email_preferences' => NotificationPreference::defaultEmailPreferences(),
            ]);

        return view('settings.pwa-notifications', [
            'preference' => $preference,
            'categories' => [
                NotificationPreference::CATEGORY_REGISTRATION_STATUS => 'Status pendaftaran',
                NotificationPreference::CATEGORY_SCHEDULE_ACTIVITY => 'Jadwal kegiatan',
                NotificationPreference::CATEGORY_SCHEDULE_CHANGES => 'Perubahan jadwal',
                NotificationPreference::CATEGORY_ANNOUNCEMENTS => 'Pengumuman ekskul',
                NotificationPreference::CATEGORY_TALENT_TEST => 'Tes bakat',
                NotificationPreference::CATEGORY_ATTENDANCE => 'Presensi',
                NotificationPreference::CATEGORY_ASSESSMENT => 'Penilaian',
                NotificationPreference::CATEGORY_SCHOOL_NEWS => 'Berita sekolah',
                NotificationPreference::CATEGORY_ADMIN_ALERT => 'Pemberitahuan penting',
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $preference = $request->user()->notificationPreference
            ?? $request->user()->notificationPreference()->create([
                'in_app_preferences' => NotificationPreference::defaultInAppPreferences(),
                'push_preferences' => NotificationPreference::defaultPushPreferences(),
                'email_preferences' => NotificationPreference::defaultEmailPreferences(),
            ]);

        $validated = $request->validate([
            'push_preferences' => ['nullable', 'array'],
            'push_preferences.*' => ['nullable', 'boolean'],
        ]);

        $resolvedPushPreferences = NotificationPreference::defaultPushPreferences();
        foreach ($resolvedPushPreferences as $category => $enabled) {
            $resolvedPushPreferences[$category] = $request->boolean("push_preferences.{$category}");
        }

        $preference->update([
            'push_preferences' => $resolvedPushPreferences,
        ]);

        return back()->with('success', 'Pengaturan notifikasi berhasil diperbarui.');
    }

    public function testPush(Request $request, PushNotificationSender $sender): RedirectResponse
    {
        $user = $request->user();
        $subscriptions = $user->pushSubscriptions()->get();

        if (! $sender->configured()) {
            return back()->with('error', 'Konfigurasi VAPID belum lengkap di server.');
        }

        if ($subscriptions->isEmpty()) {
            return back()->with('error', 'Perangkat ini belum memiliki push subscription aktif.');
        }

        $notification = new GenericAppNotification([
            'title' => 'Tes push notification',
            'message' => 'Tes notifikasi dijalankan dari halaman pengaturan PWA.',
            'url' => route('notifications.index'),
            'category' => NotificationPreference::CATEGORY_ADMIN_ALERT,
            'tag' => 'diagnostic-test-push',
        ]);
        $notification->id = (string) Str::uuid();
        $user->notify($notification);

        $payload = [
            'title' => 'Tes push notification',
            'body' => 'Jika pesan ini muncul, pengiriman push perangkat sudah berhasil.',
            'icon' => asset('pwa/icon-192.png'),
            'badge' => asset('pwa/badge-96.png'),
            'url' => route('notifications.open', $notification->id),
            'tag' => 'diagnostic-test-push',
            'category' => NotificationPreference::CATEGORY_ADMIN_ALERT,
        ];

        $successCount = 0;
        $errors = [];

        foreach ($subscriptions->unique('endpoint') as $subscription) {
            try {
                $sender->send($subscription, $payload);
                $successCount++;
            } catch (\Throwable $exception) {
                $errors[] = sprintf(
                    '%s (%s)',
                    $subscription->device_name ?: 'Perangkat tanpa nama',
                    $exception->getMessage()
                );

                Log::warning('Manual test push notification failed.', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'endpoint' => $subscription->endpoint,
                    'device_name' => $subscription->device_name,
                    'diagnostics' => $sender->diagnosticSummary(),
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        if ($successCount > 0 && $errors === []) {
            return back()->with('success', "Tes push berhasil dikirim ke {$successCount} perangkat.");
        }

        $diagnosticText = sprintf(
            'Push terkirim ke %d/%d perangkat. Diagnostik: OpenSSL=%s, queue=%s, APP_URL=%s.',
            $successCount,
            $subscriptions->count(),
            $sender->diagnosticSummary()['openssl_extension_loaded'] ? 'aktif' : 'tidak aktif',
            $sender->diagnosticSummary()['queue_connection'] ?: '-',
            $sender->diagnosticSummary()['app_url'] ?: '-'
        );

        if ($errors !== []) {
            $diagnosticText .= ' Error: '.implode(' | ', $errors);
        }

        return back()->with($successCount > 0 ? 'success' : 'error', $diagnosticText);
    }
}
