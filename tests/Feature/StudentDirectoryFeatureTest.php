<?php

namespace Tests\Feature;

use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentDirectoryFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_directory_includes_all_students_and_supports_validated_filters(): void
    {
        $student = $this->createStudent(
            name: 'SISWA DENGAN NAMA SANGAT PANJANG UNTUK PENGUJIAN DIREKTORI',
            email: 'siswa.directory.long@example.com',
            nis: null,
            className: 'X - 8',
            active: true,
        );
        Registration::query()->create([
            'student_id' => $student->id,
            'extracurricular_id' => Extracurricular::query()->firstOrFail()->id,
            'registration_date' => now()->toDateString(),
            'status' => Registration::STATUS_APPROVED,
        ]);
        $studentWithoutMembership = $this->createStudent(
            name: 'Siswa Belum Mengikuti Kegiatan',
            email: 'siswa.not.accepted@example.com',
            nis: 'NOT-ACCEPTED',
        );

        $response = $this->actingAs($this->user('admin@gmail.com'))
            ->get(route('admin.students.index', [
                'search' => 'directory.long',
                'profile_status' => 'incomplete',
                'sort' => 'name',
                'direction' => 'asc',
                'per_page' => 10,
            ]));

        $response
            ->assertOk()
            ->assertSee($student->user->name)
            ->assertDontSee($studentWithoutMembership->user->name)
            ->assertSee('NIS belum diisi')
            ->assertSee('Profil Belum Lengkap')
            ->assertSee('student-sort-link', false)
            ->assertSee('Menampilkan 1-1')
            ->assertSee('dari 1 siswa')
            ->assertViewHas('students', fn ($students): bool => $students->total() === 1)
            ->assertViewHas('perPage', 10);

        $this->actingAs($this->user('admin@gmail.com'))
            ->get(route('admin.students.index', ['search' => 'not.accepted']))
            ->assertOk()
            ->assertSee($studentWithoutMembership->user->name)
            ->assertSee('Belum mengikuti kegiatan')
            ->assertViewHas('students', fn ($students): bool => $students->total() === 1);
    }

    public function test_admin_cannot_delete_student_history_but_can_delete_unused_account(): void
    {
        $studentWithHistory = $this->createStudent(
            name: 'Siswa Dengan Riwayat',
            email: 'siswa.history@example.com',
            nis: 'HISTORY-1',
        );
        Registration::query()->create([
            'student_id' => $studentWithHistory->id,
            'extracurricular_id' => Extracurricular::query()->firstOrFail()->id,
            'registration_date' => now()->toDateString(),
            'status' => Registration::STATUS_APPROVED,
        ]);

        $this->actingAs($this->user('admin@gmail.com'))
            ->delete(route('admin.students.destroy', $studentWithHistory))
            ->assertRedirect(route('admin.students.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('students', ['id' => $studentWithHistory->id]);
        $this->assertDatabaseHas('users', ['id' => $studentWithHistory->user_id]);

        $unusedStudent = $this->createStudent(
            name: 'Siswa Tanpa Riwayat',
            email: 'siswa.unused@example.com',
            nis: 'UNUSED-1',
        );
        $unusedUserId = $unusedStudent->user_id;

        $this->actingAs($this->user('admin@gmail.com'))
            ->delete(route('admin.students.destroy', $unusedStudent))
            ->assertRedirect(route('admin.students.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('students', ['id' => $unusedStudent->id]);
        $this->assertDatabaseMissing('users', ['id' => $unusedUserId]);
    }

    public function test_coach_participant_filters_remain_scoped_to_owned_activity(): void
    {
        $coachUser = $this->user('pembina1@gmail.com');
        $ownedActivity = $coachUser->coach->extracurriculars()->firstOrFail();
        $outsideActivity = Extracurricular::query()
            ->whereDoesntHave('coaches', fn ($query) => $query->whereKey($coachUser->coach->id))
            ->firstOrFail();

        $ownedStudent = $this->createStudent(
            name: 'Peserta Binaan Dicari',
            email: 'peserta.owned@example.com',
            nis: 'OWNED-1',
        );
        $outsideStudent = $this->createStudent(
            name: 'Peserta Luar Rahasia',
            email: 'peserta.outside@example.com',
            nis: 'OUTSIDE-1',
        );

        foreach ([[$ownedStudent, $ownedActivity], [$outsideStudent, $outsideActivity]] as [$student, $activity]) {
            Registration::query()->create([
                'student_id' => $student->id,
                'extracurricular_id' => $activity->id,
                'registration_date' => now()->toDateString(),
                'status' => Registration::STATUS_APPROVED,
            ]);
        }

        $this->actingAs($coachUser)
            ->get(route('coach.extracurriculars.participants', [
                $ownedActivity,
                'search' => 'Peserta',
                'sort' => 'name',
                'direction' => 'asc',
                'per_page' => 10,
            ]))
            ->assertOk()
            ->assertSee('Peserta Binaan Dicari')
            ->assertDontSee('Peserta Luar Rahasia')
            ->assertSee('Total Peserta')
            ->assertSee('Buka aksi peserta');

        $this->actingAs($coachUser)
            ->get(route('coach.extracurriculars.participants', $outsideActivity))
            ->assertForbidden();
    }

    public function test_multiple_activities_are_compacted_and_participant_export_uses_the_same_search(): void
    {
        $student = $this->createStudent(
            name: 'Siswa Banyak Kegiatan Audit',
            email: 'siswa.multiple.activities@example.com',
            nis: 'MULTI-1',
            className: 'X - 2',
        );

        foreach (Extracurricular::query()->where('is_active', true)->limit(3)->get() as $activity) {
            Registration::query()->create([
                'student_id' => $student->id,
                'extracurricular_id' => $activity->id,
                'registration_date' => now()->toDateString(),
                'status' => Registration::STATUS_APPROVED,
            ]);
        }

        $admin = $this->user('admin@gmail.com');
        $this->actingAs($admin)
            ->get(route('admin.students.index', ['search' => 'Banyak Kegiatan Audit']))
            ->assertOk()
            ->assertSee('Siswa Banyak Kegiatan Audit')
            ->assertSee('+1 lainnya')
            ->assertSee('Kontak');

        $this->actingAs($admin)
            ->get(route('admin.participants.index', [
                'search' => 'multiple.activities',
                'class_name' => 'X - 2',
                'sort' => 'name',
                'direction' => 'asc',
                'per_page' => 10,
            ]))
            ->assertOk()
            ->assertSee('Siswa Banyak Kegiatan Audit')
            ->assertSee('3 keanggotaan sesuai filter');

        $export = $this->actingAs($admin)
            ->get(route('admin.reports.export', [
                'type' => 'participants',
                'search' => 'multiple.activities',
                'class_name' => 'X - 2',
                'format' => 'csv',
            ]));

        $export->assertOk();
        $this->assertStringContainsString('Siswa Banyak Kegiatan Audit', $export->streamedContent());
    }

    public function test_admin_can_add_new_class_option_when_creating_student(): void
    {
        $admin = $this->user('admin@gmail.com');

        $this->actingAs($admin)
            ->post(route('admin.students.store'), [
                'name' => 'Siswa Kelas Baru',
                'email' => 'siswa.kelas.baru@example.com',
                'password' => '11111111',
                'password_confirmation' => '11111111',
                'is_active' => '1',
                'nis' => 'KELAS-BARU-1',
                'class_name' => '',
                'custom_class_name' => 'X - 13',
                'gender' => 'P',
            ])
            ->assertRedirect(route('admin.students.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'nis' => 'KELAS-BARU-1',
            'class_name' => 'X - 13',
        ]);

        $storedOptions = SystemSetting::getValue('student_class_options', []);
        if (is_string($storedOptions)) {
            $storedOptions = json_decode($storedOptions, true) ?: [];
        }

        $this->assertContains('X - 13', $storedOptions);
        $this->assertArrayHasKey('X - 13', Student::registrationClassOptions());
    }

    private function createStudent(
        string $name,
        string $email,
        ?string $nis,
        string $className = 'X - 1',
        bool $active = true,
    ): Student {
        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => '11111111',
            'role' => User::ROLE_STUDENT,
            'is_active' => $active,
        ]);

        return Student::query()->create([
            'user_id' => $user->id,
            'nis' => $nis,
            'class_name' => $className,
            'gender' => 'L',
        ]);
    }

    private function user(string $email): User
    {
        return User::query()->where('email', $email)->firstOrFail();
    }
}
