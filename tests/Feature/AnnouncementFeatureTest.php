<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Coach;
use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use App\Support\ScheduledAnnouncementPublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AnnouncementFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $coachUser;

    private User $otherCoachUser;

    private User $studentUser;

    private Extracurricular $managedActivity;

    private Extracurricular $otherActivity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
        $this->coachUser = User::factory()->create([
            'role' => User::ROLE_COACH,
            'is_active' => true,
        ]);
        $this->otherCoachUser = User::factory()->create([
            'role' => User::ROLE_COACH,
            'is_active' => true,
        ]);
        $coach = Coach::query()->create([
            'user_id' => $this->coachUser->id,
            'nip' => 'COACH-ANNOUNCEMENT-1',
        ]);
        $otherCoach = Coach::query()->create([
            'user_id' => $this->otherCoachUser->id,
            'nip' => 'COACH-ANNOUNCEMENT-2',
        ]);

        $this->managedActivity = Extracurricular::query()->create([
            'name' => 'Kegiatan Binaan Pengumuman',
            'description' => 'Kegiatan untuk pengujian pengumuman.',
            'is_active' => true,
        ]);
        $this->otherActivity = Extracurricular::query()->create([
            'name' => 'Kegiatan Pembina Lain',
            'description' => 'Kegiatan lain untuk pengujian pembatasan akses.',
            'is_active' => true,
        ]);
        $coach->extracurriculars()->attach($this->managedActivity);
        $otherCoach->extracurriculars()->attach($this->otherActivity);

        $this->studentUser = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'is_active' => true,
        ]);
        $student = Student::query()->create([
            'user_id' => $this->studentUser->id,
            'nis' => 'ANNOUNCEMENT-STUDENT-1',
            'class_name' => 'X - 1',
            'gender' => 'L',
        ]);
        Registration::query()->create([
            'student_id' => $student->id,
            'extracurricular_id' => $this->managedActivity->id,
            'registration_date' => now()->toDateString(),
            'status' => Registration::STATUS_APPROVED,
        ]);
    }

    public function test_admin_can_create_draft_and_publish_to_all_students_once(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.announcements.store'), $this->payload([
                'target_scope' => 'all_students',
                'extracurricular_id' => null,
                'publication_action' => Announcement::STATUS_DRAFT,
            ]))
            ->assertRedirect(route('admin.announcements.index'));

        $announcement = Announcement::query()->where('title', 'Informasi Pengujian')->firstOrFail();
        $this->assertSame(Announcement::STATUS_DRAFT, $announcement->publication_status);
        $this->assertDatabaseCount('notifications', 0);

        $this->actingAs($this->admin)
            ->patch(route('admin.announcements.publish', $announcement))
            ->assertRedirect();
        $this->assertDatabaseCount('notifications', 1);

        $this->actingAs($this->admin)
            ->put(route('admin.announcements.update', $announcement), $this->payload([
                'title' => 'Informasi Pengujian Diperbarui',
                'target_scope' => 'all_students',
                'extracurricular_id' => null,
                'publication_action' => Announcement::STATUS_PUBLISHED,
            ]))
            ->assertRedirect(route('admin.announcements.index'));
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_coach_can_only_target_and_manage_owned_activity(): void
    {
        $this->actingAs($this->coachUser)
            ->post(route('coach.announcements.store'), $this->payload([
                'extracurricular_id' => $this->otherActivity->id,
            ]))
            ->assertSessionHasErrors('extracurricular_id');

        $this->actingAs($this->coachUser)
            ->post(route('coach.announcements.store'), $this->payload([
                'target_scope' => 'all_students',
                'extracurricular_id' => null,
            ]))
            ->assertSessionHasErrors('target_scope');

        $this->actingAs($this->coachUser)
            ->post(route('coach.announcements.store'), $this->payload([
                'extracurricular_id' => $this->managedActivity->id,
                'publication_action' => Announcement::STATUS_DRAFT,
            ]))
            ->assertRedirect(route('coach.announcements.index'));

        $otherAnnouncement = Announcement::query()->create([
            'title' => 'Milik Pembina Lain',
            'content' => 'Tidak boleh diakses pembina pertama.',
            'extracurricular_id' => $this->otherActivity->id,
            'published_by' => $this->otherCoachUser->id,
            'priority' => Announcement::PRIORITY_NORMAL,
            'publication_status' => Announcement::STATUS_DRAFT,
            'is_active' => false,
        ]);

        $this->actingAs($this->coachUser)
            ->get(route('coach.announcements.edit', $otherAnnouncement))
            ->assertForbidden();

        $this->actingAs($this->coachUser)
            ->get(route('coach.announcements.index', ['extracurricular_id' => $this->otherActivity->id]))
            ->assertForbidden();
    }

    public function test_published_announcement_cannot_be_deleted_but_draft_can(): void
    {
        $published = $this->announcement([
            'publication_status' => Announcement::STATUS_PUBLISHED,
            'is_active' => true,
            'publish_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.announcements.destroy', $published))
            ->assertSessionHasErrors('announcement');
        $this->assertDatabaseHas('announcements', ['id' => $published->id]);

        $draft = $this->announcement();
        $this->actingAs($this->admin)
            ->delete(route('admin.announcements.destroy', $draft))
            ->assertRedirect();
        $this->assertDatabaseMissing('announcements', ['id' => $draft->id]);
    }

    public function test_scheduled_announcement_is_published_and_notified_once(): void
    {
        Carbon::setTestNow('2026-07-31 10:00:00');
        $announcement = $this->announcement([
            'extracurricular_id' => $this->managedActivity->id,
            'publication_status' => Announcement::STATUS_SCHEDULED,
            'is_active' => true,
            'publish_at' => now()->subMinute(),
        ]);

        $publisher = app(ScheduledAnnouncementPublisher::class);
        $this->assertSame(1, $publisher->publishDue());
        $this->assertSame(Announcement::STATUS_PUBLISHED, $announcement->refresh()->publication_status);
        $this->assertDatabaseCount('notifications', 1);

        $this->assertSame(0, $publisher->publishDue());
        $this->assertDatabaseCount('notifications', 1);
        Carbon::setTestNow();
    }

    public function test_management_list_filters_sorts_and_paginates_server_side(): void
    {
        foreach (range(1, 13) as $number) {
            $this->announcement([
                'title' => sprintf('Pengumuman Filter %02d', $number),
                'priority' => $number === 13 ? Announcement::PRIORITY_URGENT : Announcement::PRIORITY_NORMAL,
            ]);
        }

        $this->actingAs($this->admin)
            ->get(route('admin.announcements.index', [
                'search' => 'Filter',
                'priority' => Announcement::PRIORITY_URGENT,
                'sort' => 'title',
                'direction' => 'desc',
                'per_page' => 10,
            ]))
            ->assertOk()
            ->assertSee('Pengumuman Filter 13')
            ->assertDontSee('Pengumuman Filter 12')
            ->assertSee('Hapus semua filter');

        $this->actingAs($this->admin)
            ->get(route('admin.announcements.index', ['per_page' => 10]))
            ->assertOk()
            ->assertSee('Menampilkan 1-10 dari 13 pengumuman');
    }

    public function test_validation_rejects_blank_long_and_invalid_period_values(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.announcements.store'), $this->payload([
                'title' => '   ',
                'content' => '   ',
            ]))
            ->assertSessionHasErrors(['title', 'content']);

        $this->actingAs($this->admin)
            ->post(route('admin.announcements.store'), $this->payload([
                'title' => str_repeat('A', 121),
                'content' => str_repeat('B', 5001),
            ]))
            ->assertSessionHasErrors(['title', 'content']);

        $this->actingAs($this->admin)
            ->post(route('admin.announcements.store'), $this->payload([
                'publication_action' => Announcement::STATUS_SCHEDULED,
                'publish_date' => now()->subDay()->toDateString(),
                'publish_time' => '08:00',
            ]))
            ->assertSessionHasErrors('publish_date');
    }

    private function payload(array $overrides = []): array
    {
        return [
            'title' => 'Informasi Pengujian',
            'content' => 'Isi pengumuman untuk menguji seluruh alur.',
            'target_scope' => 'single',
            'extracurricular_id' => $this->managedActivity->id,
            'priority' => Announcement::PRIORITY_NORMAL,
            'publication_action' => Announcement::STATUS_PUBLISHED,
            ...$overrides,
        ];
    }

    private function announcement(array $overrides = []): Announcement
    {
        return Announcement::query()->create([
            'title' => 'Draft '.fake()->unique()->numerify('####'),
            'content' => 'Isi draft pengumuman.',
            'extracurricular_id' => null,
            'published_by' => $this->admin->id,
            'priority' => Announcement::PRIORITY_NORMAL,
            'publication_status' => Announcement::STATUS_DRAFT,
            'publish_at' => null,
            'ends_at' => null,
            'is_active' => false,
            ...$overrides,
        ]);
    }
}
