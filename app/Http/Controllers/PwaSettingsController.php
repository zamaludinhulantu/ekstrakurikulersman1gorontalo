<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
}
