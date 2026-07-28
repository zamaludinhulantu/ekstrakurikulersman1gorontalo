<?php

namespace App\Jobs;

use App\Models\NotificationPreference;
use App\Models\User;
use App\Support\PushNotificationSender;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendPushNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $userId,
        public string $notificationId,
    ) {
    }

    public function handle(PushNotificationSender $sender): void
    {
        if (! $sender->configured()) {
            return;
        }

        $user = User::query()
            ->with(['pushSubscriptions', 'notifications'])
            ->find($this->userId);

        if (! $user) {
            return;
        }

        if (! $user->is_active) {
            $user->pushSubscriptions()->delete();

            return;
        }

        $notification = $user->notifications()->find($this->notificationId);

        if (! $notification) {
            return;
        }

        $safePushContent = $this->safePushContent((string) data_get($notification->data, 'category', 'general'));
        $payload = [
            'title' => $safePushContent['title'],
            'body' => $safePushContent['body'],
            'icon' => asset('pwa/icon-192.png'),
            'badge' => asset('pwa/badge-96.png'),
            'url' => route('notifications.open', $notification->id),
            'tag' => (string) data_get($notification->data, 'push.tag', 'general'),
            'category' => (string) data_get($notification->data, 'push.category', 'general'),
        ];

        foreach ($user->pushSubscriptions->unique('endpoint') as $subscription) {
            try {
                $sender->send($subscription, $payload);
            } catch (\Throwable $exception) {
                Log::warning('Push notification delivery failed.', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'notification_id' => $notification->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }

    private function safePushContent(string $category): array
    {
        return match ($category) {
            NotificationPreference::CATEGORY_REGISTRATION_STATUS => [
                'title' => 'Status pendaftaran diperbarui',
                'body' => 'Status pendaftaran Anda telah diperbarui.',
            ],
            NotificationPreference::CATEGORY_SCHEDULE_ACTIVITY => [
                'title' => 'Jadwal kegiatan tersedia',
                'body' => 'Ada jadwal kegiatan yang perlu Anda cek.',
            ],
            NotificationPreference::CATEGORY_SCHEDULE_CHANGES => [
                'title' => 'Perubahan jadwal kegiatan',
                'body' => 'Ada perubahan jadwal kegiatan.',
            ],
            NotificationPreference::CATEGORY_ANNOUNCEMENTS => [
                'title' => 'Pengumuman baru',
                'body' => 'Ada pengumuman baru.',
            ],
            NotificationPreference::CATEGORY_TALENT_TEST => [
                'title' => 'Informasi tes bakat',
                'body' => 'Ada pembaruan terkait tes bakat Anda.',
            ],
            NotificationPreference::CATEGORY_ATTENDANCE => [
                'title' => 'Presensi diperbarui',
                'body' => 'Ada pembaruan presensi kegiatan Anda.',
            ],
            NotificationPreference::CATEGORY_ASSESSMENT => [
                'title' => 'Hasil penilaian tersedia',
                'body' => 'Hasil penilaian terbaru tersedia.',
            ],
            NotificationPreference::CATEGORY_SCHOOL_NEWS => [
                'title' => 'Berita sekolah baru',
                'body' => 'Ada informasi sekolah baru yang perlu Anda cek.',
            ],
            NotificationPreference::CATEGORY_ADMIN_ALERT => [
                'title' => 'Pemberitahuan baru',
                'body' => 'Ada pemberitahuan baru untuk akun Anda.',
            ],
            default => [
                'title' => 'Pemberitahuan baru',
                'body' => 'Ada pembaruan baru untuk akun Anda.',
            ],
        };
    }
}
