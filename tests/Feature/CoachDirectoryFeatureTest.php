<?php

namespace Tests\Feature;

use App\Models\Coach;
use App\Models\Extracurricular;
use App\Models\Schedule;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoachDirectoryFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_directory_supports_filters_sorting_and_lists_multiple_assignments(): void
    {
        $coach = $this->createCoach(
            name: 'PEMBINA DIREKTORI DENGAN NAMA PANJANG',
            email: 'coach.directory.long@example.com',
            nip: 'AUTO-COACH-AUDIT-039',
        );

        $activities = Extracurricular::query()->limit(3)->get();
        $coach->extracurriculars()->sync($activities->pluck('id'));

        $this->actingAs($this->user('admin@gmail.com'))
            ->get(route('admin.coaches.index', [
                'search' => 'directory.long',
                'status' => 'active',
                'assignment' => 'assigned',
                'profile_status' => 'complete',
                'sort' => 'activities_count',
                'direction' => 'desc',
                'per_page' => 10,
            ]))
            ->assertOk()
            ->assertSee('PEMBINA DIREKTORI DENGAN NAMA PANJANG')
            ->assertSee('AUTO-COACH-AUDIT-039')
            ->assertSee($activities[0]->name)
            ->assertSee($activities[1]->name)
            ->assertSee($activities[2]->name)
            ->assertDontSee('+1 lainnya')
            ->assertSee('Kegiatan Binaan')
            ->assertSee('Menampilkan 1-1')
            ->assertSee('dari 1 pembina')
            ->assertViewHas('coaches', fn ($coaches): bool => $coaches->total() === 1);
    }

    public function test_admin_can_filter_unassigned_inactive_coach(): void
    {
        $coach = $this->createCoach(
            name: 'Pembina Belum Ditugaskan',
            email: 'coach.unassigned@example.com',
            nip: '000012345678901234',
            active: false,
        );

        $this->actingAs($this->user('admin@gmail.com'))
            ->get(route('admin.coaches.index', [
                'status' => 'inactive',
                'assignment' => 'unassigned',
            ]))
            ->assertOk()
            ->assertSee($coach->user->name)
            ->assertSee('000012345678901234')
            ->assertSee('Belum ditugaskan')
            ->assertSee('Tidak Aktif');
    }

    public function test_admin_cannot_delete_coach_with_history_but_can_delete_unused_account(): void
    {
        $coachWithHistory = $this->createCoach(
            name: 'Pembina Dengan Riwayat',
            email: 'coach.history@example.com',
            nip: 'COACH-HISTORY',
        );
        Schedule::query()->create([
            'extracurricular_id' => Extracurricular::query()->firstOrFail()->id,
            'coach_id' => $coachWithHistory->id,
            'title' => 'Jadwal Riwayat Pembina',
            'activity_date' => now()->addDay()->toDateString(),
            'start_time' => '15:00',
            'end_time' => '16:00',
            'location' => 'Lapangan',
        ]);

        $this->actingAs($this->user('admin@gmail.com'))
            ->delete(route('admin.coaches.destroy', $coachWithHistory))
            ->assertRedirect(route('admin.coaches.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('coaches', ['id' => $coachWithHistory->id]);
        $this->assertDatabaseHas('users', ['id' => $coachWithHistory->user_id]);

        $unusedCoach = $this->createCoach(
            name: 'Pembina Tanpa Riwayat',
            email: 'coach.unused@example.com',
            nip: 'COACH-UNUSED',
        );
        $unusedUserId = $unusedCoach->user_id;

        $this->actingAs($this->user('admin@gmail.com'))
            ->delete(route('admin.coaches.destroy', $unusedCoach))
            ->assertRedirect(route('admin.coaches.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('coaches', ['id' => $unusedCoach->id]);
        $this->assertDatabaseMissing('users', ['id' => $unusedUserId]);
    }

    public function test_coach_role_cannot_open_admin_coach_directory_or_change_another_coach(): void
    {
        $coachUser = $this->user('pembina1@gmail.com');
        $target = Coach::query()->where('user_id', '!=', $coachUser->id)->firstOrFail();

        $this->actingAs($coachUser)
            ->get(route('admin.coaches.index'))
            ->assertForbidden();

        $this->actingAs($coachUser)
            ->get(route('admin.coaches.edit', $target))
            ->assertForbidden();

        $this->actingAs($coachUser)
            ->delete(route('admin.coaches.destroy', $target))
            ->assertForbidden();
    }

    private function createCoach(
        string $name,
        string $email,
        string $nip,
        bool $active = true,
    ): Coach {
        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => '11111111',
            'role' => User::ROLE_COACH,
            'is_active' => $active,
        ]);

        return Coach::query()->create([
            'user_id' => $user->id,
            'nip' => $nip,
        ]);
    }

    private function user(string $email): User
    {
        return User::query()->where('email', $email)->firstOrFail();
    }
}
