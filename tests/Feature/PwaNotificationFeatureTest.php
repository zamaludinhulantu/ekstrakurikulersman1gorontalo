<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaNotificationFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_manifest_service_worker_and_offline_page_are_accessible(): void
    {
        $this->get(route('pwa.manifest'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/manifest+json')
            ->assertSee('"display": "standalone"', false);

        $this->get(route('pwa.service-worker'))
            ->assertOk()
            ->assertHeader('Service-Worker-Allowed', '/')
            ->assertSee('self.addEventListener', false);

        $this->get(route('offline'))
            ->assertOk()
            ->assertSee('Perangkat sedang offline');
    }

    public function test_user_only_sees_own_notifications(): void
    {
        $studentOne = $this->userByEmail('siswa1@gmail.com');
        $studentTwo = $this->userByEmail('siswa2@gmail.com');

        $studentOne->notify(new \App\Notifications\GenericAppNotification([
            'title' => 'Notifikasi Siswa 1',
            'message' => 'Hanya boleh terlihat oleh siswa pertama.',
            'url' => route('notifications.index'),
            'category' => \App\Models\NotificationPreference::CATEGORY_REGISTRATION_STATUS,
        ]));

        $studentTwo->notify(new \App\Notifications\GenericAppNotification([
            'title' => 'Notifikasi Siswa 2',
            'message' => 'Hanya boleh terlihat oleh siswa kedua.',
            'url' => route('notifications.index'),
            'category' => \App\Models\NotificationPreference::CATEGORY_REGISTRATION_STATUS,
        ]));

        $notificationOne = $studentOne->notifications()->latest()->firstOrFail();
        $notificationTwo = $studentTwo->notifications()->latest()->firstOrFail();

        $this->actingAs($studentOne)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Notifikasi Siswa 1')
            ->assertDontSee('Notifikasi Siswa 2');

        $this->actingAs($studentOne)
            ->post(route('notifications.read', $notificationOne->id))
            ->assertRedirect();

        $this->assertNotNull($notificationOne->fresh()->read_at);

        $this->actingAs($studentOne)
            ->post(route('notifications.read', $notificationTwo->id))
            ->assertNotFound();
    }

    public function test_push_subscription_requires_login_and_is_not_duplicated(): void
    {
        $payload = [
            'endpoint' => 'https://example.com/push/subscription-1',
            'keys' => [
                'p256dh' => 'test-public-key',
                'auth' => 'test-auth-key',
            ],
            'contentEncoding' => 'aes128gcm',
            'deviceName' => 'Chrome Windows',
        ];

        $this->postJson(route('push-subscriptions.store'), $payload)->assertUnauthorized();

        $student = $this->userByEmail('siswa1@gmail.com');
        $otherStudent = $this->userByEmail('siswa2@gmail.com');

        $this->actingAs($student)
            ->postJson(route('push-subscriptions.store'), $payload)
            ->assertOk()
            ->assertJson(['status' => 'subscribed']);

        $this->actingAs($student)
            ->postJson(route('push-subscriptions.store'), $payload)
            ->assertOk()
            ->assertJson(['status' => 'subscribed']);

        $this->assertDatabaseCount('push_subscriptions', 1);

        $this->actingAs($otherStudent)
            ->postJson(route('push-subscriptions.status'), ['endpoint' => $payload['endpoint']])
            ->assertOk()
            ->assertJson([
                'status' => 'linked_to_other_account',
                'requires_confirmation' => true,
            ]);

        $this->actingAs($otherStudent)
            ->postJson(route('push-subscriptions.store'), $payload)
            ->assertStatus(409)
            ->assertJson([
                'status' => 'confirmation_required',
                'requires_confirmation' => true,
            ]);

        $this->actingAs($otherStudent)
            ->postJson(route('push-subscriptions.store'), [...$payload, 'takeover' => true])
            ->assertOk()
            ->assertJson(['status' => 'subscribed']);

        $this->assertDatabaseCount('push_subscriptions', 1);
        $this->assertDatabaseHas('push_subscriptions', [
            'endpoint' => $payload['endpoint'],
            'user_id' => $otherStudent->id,
        ]);

        $secondPayload = [
            ...$payload,
            'endpoint' => 'https://example.com/push/subscription-2',
        ];

        $this->actingAs($otherStudent)
            ->postJson(route('push-subscriptions.store'), $secondPayload)
            ->assertOk()
            ->assertJson(['status' => 'subscribed']);

        $this->assertDatabaseCount('push_subscriptions', 2);

        $this->actingAs($otherStudent)
            ->deleteJson(route('push-subscriptions.destroy-all'))
            ->assertOk()
            ->assertJson([
                'status' => 'unsubscribed_all',
                'deleted' => 2,
            ]);

        $this->assertDatabaseCount('push_subscriptions', 0);
    }

    public function test_notification_open_route_redirects_guest_to_login_and_preserves_intended_url(): void
    {
        $student = $this->userByEmail('siswa1@gmail.com');

        $student->notify(new \App\Notifications\GenericAppNotification([
            'title' => 'Status pendaftaran diperbarui',
            'message' => 'Status detail hanya terlihat setelah login.',
            'url' => route('student.registrations.index'),
            'category' => \App\Models\NotificationPreference::CATEGORY_REGISTRATION_STATUS,
        ]));

        $notification = $student->notifications()->latest()->firstOrFail();

        $this->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('login'));

        $this->post(route('login.attempt'), [
            'email' => $student->email,
            'password' => '11111111',
        ])->assertRedirect(route('notifications.open', $notification->id));

        $this->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('student.registrations.index', ['_notification_redirect' => 1]));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_notification_open_route_rejects_external_targets(): void
    {
        $student = $this->userByEmail('siswa1@gmail.com');

        $student->notify(new \App\Notifications\GenericAppNotification([
            'title' => 'Tes target eksternal',
            'message' => 'Tidak boleh membuka URL luar.',
            'url' => 'https://example.org/evil',
            'category' => \App\Models\NotificationPreference::CATEGORY_ADMIN_ALERT,
        ]));

        $notification = $student->notifications()->latest()->firstOrFail();

        $this->actingAs($student)
            ->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');
    }

    public function test_idle_timeout_redirect_uses_guest_redirect_and_preserves_intended_url(): void
    {
        config(['session.idle_timeout' => 1]);

        $student = $this->userByEmail('siswa1@gmail.com');

        $response = $this->actingAs($student)
            ->withSession([
                'authenticated_last_activity_at' => now()->subMinutes(5)->timestamp,
            ])
            ->get(route('settings.pwa-notifications'));

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHas('url.intended', route('settings.pwa-notifications'));
    }

    public function test_registration_status_update_creates_database_notification(): void
    {
        $admin = $this->userByEmail('admin@gmail.com');
        $registration = Registration::query()
            ->where('status', Registration::STATUS_PENDING)
            ->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.registrations.update-status', $registration), [
                'decision' => 'approve',
                'notes' => 'Disetujui untuk pengujian notifikasi.',
            ])
            ->assertRedirect();

        $studentUser = $registration->fresh()->student->user;

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $studentUser->id,
        ]);

        $this->actingAs($studentUser)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Status pendaftaran diperbarui');
    }

    private function userByEmail(string $email): User
    {
        return User::query()->where('email', $email)->firstOrFail();
    }
}
