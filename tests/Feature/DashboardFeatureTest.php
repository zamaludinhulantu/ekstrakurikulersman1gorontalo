<?php

namespace Tests\Feature;

use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_dashboard_uses_actionable_counts_and_complete_six_month_trend(): void
    {
        $response = $this->actingAs($this->user('admin@gmail.com'))
            ->get(route('admin.dashboard'));

        $response
            ->assertOk()
            ->assertSee('Perlu Tindakan')
            ->assertSee('Distribusi Status')
            ->assertSee('Kegiatan dengan Anggota Terbanyak')
            ->assertSee('Terakhir diperbarui')
            ->assertSee('Kotak masuk notifikasi')
            ->assertSee('Pengaturan notifikasi')
            ->assertDontSee('page-actions-desktop', false)
            ->assertViewHas(
                'totalExtracurriculars',
                Extracurricular::query()->where('is_active', true)->count()
            )
            ->assertViewHas('pendingRegistrations', function (int $count): bool {
                $expected = Registration::query()
                    ->where('status', Registration::STATUS_PENDING)
                    ->whereNull('cancellation_requested_at')
                    ->where(function ($query): void {
                        $query->whereNull('willing_to_take_test')
                            ->orWhere('willing_to_take_test', false);
                    })
                    ->count();

                return $count === $expected;
            })
            ->assertViewHas('registrationTrend', function (array $trend): bool {
                if (count($trend['months'] ?? []) !== 6) {
                    return false;
                }

                foreach ($trend['months'] as $month) {
                    foreach (['label', 'year', 'pending', 'approved', 'rejected', 'cancelled', 'total'] as $key) {
                        if (! array_key_exists($key, $month)) {
                            return false;
                        }
                    }
                }

                return true;
            })
            ->assertViewHas(
                'popularRegistrations',
                fn (array $items): bool => collect($items)->every(
                    fn (array $item): bool => $item['total'] > 0
                )
            );
    }

    public function test_coach_dashboard_only_contains_owned_activity_data(): void
    {
        $coachUser = $this->user('pembina1@gmail.com');
        $ownedIds = $coachUser->coach->extracurriculars()
            ->pluck('extracurriculars.id')
            ->merge(
                Extracurricular::query()
                    ->where('coach_id', $coachUser->coach->id)
                    ->pluck('id')
            )
            ->unique()
            ->all();

        $this->actingAs($coachUser)
            ->get(route('coach.dashboard'))
            ->assertOk()
            ->assertSee('Kegiatan Binaan')
            ->assertSee('Perlu Tindakan')
            ->assertViewHas(
                'recentRegistrations',
                fn ($registrations): bool => $registrations->every(
                    fn (Registration $registration): bool => in_array(
                        $registration->extracurricular_id,
                        $ownedIds,
                        true
                    )
                )
            )
            ->assertViewHas(
                'upcomingSchedules',
                fn ($schedules): bool => $schedules->every(
                    fn ($schedule): bool => in_array($schedule->extracurricular_id, $ownedIds, true)
                )
            );
    }

    public function test_student_dashboard_excludes_cancelled_registrations_from_active_total(): void
    {
        $studentUser = $this->user('siswa1@gmail.com');
        $registration = $studentUser->student->registrations()->firstOrFail();
        $registration->update(['status' => Registration::STATUS_CANCELLED]);

        $expected = $studentUser->student->registrations()
            ->where('status', '!=', Registration::STATUS_CANCELLED)
            ->count();

        $this->actingAs($studentUser)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Pendaftaran Aktif')
            ->assertSee('Agenda Mendatang')
            ->assertViewHas('totalRegistrations', $expected)
            ->assertViewHas(
                'latestRegistration',
                fn (?Registration $latest): bool => $latest === null
                    || $latest->status !== Registration::STATUS_CANCELLED
            );
    }

    public function test_principal_dashboard_keeps_filters_and_read_only_summary_available(): void
    {
        $this->actingAs($this->user('kepsek@gmail.com'))
            ->get(route('principal.dashboard'))
            ->assertOk()
            ->assertSee('Terakhir diperbarui')
            ->assertSee('Ekstrakurikuler')
            ->assertSee('Siswa')
            ->assertSee('Pendaftaran');
    }

    public function test_all_role_dashboards_share_the_same_visual_shell(): void
    {
        $dashboards = [
            'superadmin@gmail.com' => route('super-admin.dashboard'),
            'admin@gmail.com' => route('admin.dashboard'),
            'pembina1@gmail.com' => route('coach.dashboard'),
            'siswa1@gmail.com' => route('student.dashboard'),
            'kepsek@gmail.com' => route('principal.dashboard'),
        ];

        foreach ($dashboards as $email => $dashboardUrl) {
            $this->actingAs($this->user($email))
                ->get($dashboardUrl)
                ->assertOk()
                ->assertSee('dashboard-stat-grid', false)
                ->assertSee('dashboard-updated-at', false)
                ->assertSee('topbar-account-toggle', false)
                ->assertSee('Kotak masuk notifikasi')
                ->assertSee('Pengaturan notifikasi')
                ->assertDontSee('page-actions-desktop', false);
        }
    }

    private function user(string $email): User
    {
        return User::query()->where('email', $email)->firstOrFail();
    }
}
