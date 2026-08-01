<?php

namespace Tests\Feature;

use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class RegistrationIndexFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_list_paginates_each_registration_and_handles_incomplete_student_identity(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $student = $this->createStudent('Siswa Audit Multi', null, null);
        $activities = Extracurricular::query()->orderBy('id')->limit(3)->get();

        foreach ($activities->take(2) as $index => $activity) {
            Registration::create([
                'student_id' => $student->id,
                'extracurricular_id' => $activity->id,
                'registration_date' => now()->subDays($index)->toDateString(),
                'status' => Registration::STATUS_PENDING,
            ]);
        }
        Registration::create([
            'student_id' => $student->id,
            'extracurricular_id' => $activities->last()->id,
            'registration_date' => now()->toDateString(),
            'status' => Registration::STATUS_CANCELLED,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.registrations.index', [
            'search' => 'Siswa Audit Multi',
            'per_page' => 10,
        ]));

        $response
            ->assertOk()
            ->assertSee('Satu baris mewakili satu pendaftaran kegiatan.')
            ->assertSee('NIS belum diisi')
            ->assertSee('Kelas belum diisi')
            ->assertSee('Menampilkan 1-2 dari 2 pendaftaran');

        $response->assertViewHas('registrations', function ($paginator): bool {
            return $paginator->total() === 2
                && $paginator->getCollection()->every(
                    fn ($item): bool => $item instanceof Registration
                );
        })->assertViewHas(
            'statusMap',
            fn (array $statusMap): bool => ! array_key_exists(Registration::STATUS_CANCELLED, $statusMap)
        );
    }

    public function test_admin_filters_registration_by_activity_class_status_and_date_range(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $student = $this->createStudent('Siswa Filter Audit', 'AUDIT-001', 'X - 8');
        $activity = Extracurricular::query()->orderBy('id')->firstOrFail();
        $otherActivity = Extracurricular::query()->whereKeyNot($activity->id)->orderBy('id')->firstOrFail();

        $matching = Registration::create([
            'student_id' => $student->id,
            'extracurricular_id' => $activity->id,
            'registration_date' => '2026-07-15',
            'status' => Registration::STATUS_APPROVED,
        ]);
        Registration::create([
            'student_id' => $student->id,
            'extracurricular_id' => $otherActivity->id,
            'registration_date' => '2026-06-10',
            'status' => Registration::STATUS_REJECTED,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.registrations.index', [
            'search' => 'AUDIT-001',
            'extracurricular_id' => $activity->id,
            'class_name' => 'X - 8',
            'status' => Registration::STATUS_APPROVED,
            'date_from' => '2026-07-01',
            'date_to' => '2026-07-31',
            'per_page' => 10,
        ]));

        $response
            ->assertOk()
            ->assertSee($activity->name)
            ->assertViewHas('registrations', fn ($paginator): bool => $paginator->total() === 1
                && $paginator->first()->is($matching));
    }

    public function test_admin_registration_pagination_uses_compact_page_numbers(): void
    {
        $paginator = new LengthAwarePaginator(
            items: range(1, 20),
            total: 258,
            perPage: 20,
            currentPage: 1,
            options: ['path' => '/admin/registrations'],
        );

        $html = $paginator
            ->onEachSide(0)
            ->links('vendor.pagination.bootstrap-5-compact')
            ->render();

        $this->assertStringContainsString('aria-label="Navigasi halaman daftar pendaftar"', $html);
        $this->assertStringContainsString('aria-label="Buka halaman 2"', $html);
        $this->assertStringNotContainsString('aria-label="Buka halaman 5"', $html);
        $this->assertStringNotContainsString('Showing', $html);
    }

    public function test_coach_only_sees_and_manages_registrations_from_owned_activities(): void
    {
        $coachUser = User::query()->where('email', 'pembina1@gmail.com')->firstOrFail();
        $ownedIds = $coachUser->coach->extracurriculars()->pluck('extracurriculars.id');
        $ownedActivity = Extracurricular::query()->whereIn('id', $ownedIds)->firstOrFail();
        $outsideActivity = Extracurricular::query()->whereNotIn('id', $ownedIds)->firstOrFail();
        $student = $this->createStudent('Siswa Batas Pembina', 'AUDIT-COACH-1', 'X - 7');

        $ownedRegistration = Registration::create([
            'student_id' => $student->id,
            'extracurricular_id' => $ownedActivity->id,
            'registration_date' => now()->toDateString(),
            'status' => Registration::STATUS_PENDING,
            'willing_to_take_test' => true,
        ]);
        $outsideRegistration = Registration::create([
            'student_id' => $student->id,
            'extracurricular_id' => $outsideActivity->id,
            'registration_date' => now()->toDateString(),
            'status' => Registration::STATUS_PENDING,
        ]);
        $cancelledStudent = $this->createStudent('Siswa Batal Tidak Ditampilkan', 'AUDIT-COACH-1-CANCELLED', 'X - 7');
        Registration::create([
            'student_id' => $cancelledStudent->id,
            'extracurricular_id' => $ownedActivity->id,
            'registration_date' => now()->toDateString(),
            'status' => Registration::STATUS_CANCELLED,
        ]);

        $this->actingAs($coachUser)
            ->get(route('coach.registrations.index', ['search' => 'AUDIT-COACH-1']))
            ->assertOk()
            ->assertSee($ownedActivity->name)
            ->assertSee('Menunggu Tes')
            ->assertSee('data-action="'.route('coach.registrations.update-status', $ownedRegistration).'"', false)
            ->assertDontSee('Siswa Batal Tidak Ditampilkan')
            ->assertDontSee($outsideActivity->name)
            ->assertViewHas('registrations', fn ($paginator): bool => $paginator->total() === 1
                && $paginator->first()->is($ownedRegistration));

        $this->actingAs($coachUser)
            ->get(route('coach.registrations.index', [
                'search' => 'AUDIT-COACH-1',
                'extracurricular_id' => $outsideActivity->id,
            ]))
            ->assertOk()
            ->assertSee('Belum ada pendaftaran pada kegiatan binaan yang sesuai dengan filter ini.')
            ->assertViewHas('registrations', fn ($paginator): bool => $paginator->total() === 0);

        $this->actingAs($coachUser)
            ->get(route('coach.registrations.show', $outsideRegistration))
            ->assertForbidden();

        $this->actingAs($coachUser)
            ->patch(route('coach.registrations.update-status', $outsideRegistration), [
                'decision' => 'approve',
            ])
            ->assertForbidden();

        $outsideRegistration->update(['cancellation_requested_at' => now()]);
        $this->actingAs($coachUser)
            ->patch(route('coach.registrations.review-cancellation', $outsideRegistration), [
                'decision' => 'approve',
            ])
            ->assertForbidden();
    }

    public function test_approved_registration_cancellation_waits_for_coach_review_and_can_be_rejected(): void
    {
        $coachUser = User::query()->where('email', 'pembina1@gmail.com')->firstOrFail();
        $activity = $coachUser->coach->extracurriculars()->firstOrFail();
        $student = $this->createStudent('Siswa Meminta Batal', 'AUDIT-CANCEL-1', 'X - 5');
        $registration = Registration::create([
            'student_id' => $student->id,
            'extracurricular_id' => $activity->id,
            'registration_date' => now()->toDateString(),
            'status' => Registration::STATUS_APPROVED,
        ]);

        $this->actingAs($student->user)
            ->delete(route('student.registrations.destroy', $registration))
            ->assertSessionHas('success', 'Permintaan pembatalan berhasil dikirim dan menunggu konfirmasi Admin atau Pembina.');

        $registration->refresh();
        $this->assertSame(Registration::STATUS_APPROVED, $registration->status);
        $this->assertNotNull($registration->cancellation_requested_at);

        $this->actingAs($coachUser)
            ->get(route('coach.registrations.index', ['search' => 'AUDIT-CANCEL-1']))
            ->assertOk()
            ->assertSee('Menunggu Konfirmasi Batal')
            ->assertSee('Setujui pembatalan')
            ->assertSee('Tolak permintaan batal');

        $this->actingAs($coachUser)
            ->patch(route('coach.registrations.review-cancellation', $registration), [
                'decision' => 'reject',
            ])
            ->assertSessionHas('success', 'Permintaan pembatalan berhasil ditolak.');

        $registration->refresh();
        $this->assertSame(Registration::STATUS_APPROVED, $registration->status);
        $this->assertNull($registration->cancellation_requested_at);
    }

    public function test_pending_registration_without_test_can_still_be_cancelled_directly(): void
    {
        $activity = Extracurricular::query()->firstOrFail();
        $student = $this->createStudent('Siswa Batal Langsung', 'AUDIT-CANCEL-2', 'X - 4');
        $registration = Registration::create([
            'student_id' => $student->id,
            'extracurricular_id' => $activity->id,
            'registration_date' => now()->toDateString(),
            'status' => Registration::STATUS_PENDING,
            'willing_to_take_test' => false,
        ]);

        $this->actingAs($student->user)
            ->delete(route('student.registrations.destroy', $registration))
            ->assertSessionHas('success', 'Keikutsertaan ekstrakurikuler berhasil dibatalkan dan telah dihapus dari daftar pendaftaran Anda.');

        $registration->refresh();
        $this->assertSame(Registration::STATUS_CANCELLED, $registration->status);
        $this->assertNull($registration->cancellation_requested_at);
    }

    public function test_admin_export_uses_one_row_per_filtered_registration(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $student = $this->createStudent('Siswa Ekspor Audit', 'AUDIT-EXPORT-1', 'X - 6');
        $activities = Extracurricular::query()->orderBy('id')->limit(2)->get();

        foreach ($activities as $activity) {
            Registration::create([
                'student_id' => $student->id,
                'extracurricular_id' => $activity->id,
                'registration_date' => '2026-07-20',
                'status' => Registration::STATUS_APPROVED,
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.registrations.export', [
            'search' => 'AUDIT-EXPORT-1',
            'format' => 'xls',
        ]));

        $response
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('Total pendaftaran: 2', $content);
        $this->assertStringContainsString($activities[0]->name, $content);
        $this->assertStringContainsString($activities[1]->name, $content);
    }

    private function createStudent(string $name, ?string $nis, ?string $className): Student
    {
        $user = User::factory()->create([
            'name' => $name,
            'role' => User::ROLE_STUDENT,
            'is_active' => true,
        ]);

        return Student::create([
            'user_id' => $user->id,
            'nis' => $nis,
            'class_name' => $className,
            'gender' => 'L',
            'date_of_birth' => '2010-01-01',
        ]);
    }
}
