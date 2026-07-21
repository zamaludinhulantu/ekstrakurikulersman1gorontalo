<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\Announcement;
use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\Report;
use App\Models\Schedule;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_pages_and_guest_registration_entry_work(): void
    {
        $extracurricular = Extracurricular::query()->where('is_active', true)->firstOrFail();

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee("Tilawatil Qur'an")
            ->assertDontSee('Alur Penggunaan Sistem');

        $this->get(route('public.information'))
            ->assertOk()
            ->assertSee('Alur Penggunaan Sistem')
            ->assertSee('Manfaat Sistem');

        $this->get(route('public.extracurriculars.show', $extracurricular->getKey()))
            ->assertOk();

        $this->get(route('register'))
            ->assertOk()
            ->assertDontSee('name="nis"', false)
            ->assertSee('name="class_name"', false);

        $this->get(route('public.extracurriculars.register', $extracurricular->getKey()))
            ->assertRedirect(route('login'));

        $this->assertSame(
            $extracurricular->id,
            session('pending_extracurricular_id')
        );

        $this->post(route('register.store'), [
            'name' => 'Siswa Mandiri',
            'email' => 'siswa.mandiri@example.com',
            'password' => '11111111',
            'password_confirmation' => '11111111',
            'class_name' => 'X - 5',
            'gender' => 'P',
            'date_of_birth' => '2010-02-20',
            'phone' => '081299999001',
            'address' => 'Alamat registrasi mandiri',
            'parent_name' => 'Ibu Mandiri',
            'parent_phone' => '081299999002',
        ])->assertRedirect(route('student.extracurriculars.show', $extracurricular->getKey()));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'siswa.mandiri@example.com',
            'role' => User::ROLE_STUDENT,
        ]);
        $this->assertDatabaseHas('students', [
            'parent_name' => 'Ibu Mandiri',
            'class_name' => 'X - 5',
            'nis' => null,
        ]);
    }

    public function test_login_logout_and_role_dashboards_work(): void
    {
        $this->post(route('login.attempt'), [
            'email' => 'admin@gmail.com',
            'password' => '11111111',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();

        $dashboardMap = [
            'admin@gmail.com' => route('admin.dashboard'),
            'pembina1@gmail.com' => route('coach.dashboard'),
            'siswa1@gmail.com' => route('student.dashboard'),
            'kepsek@gmail.com' => route('principal.dashboard'),
        ];

        foreach ($dashboardMap as $email => $expectedRoute) {
            $user = $this->userByEmail($email);

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertRedirect($expectedRoute);

            $this->actingAs($user)
                ->get($expectedRoute)
                ->assertOk();
        }

        $this->actingAs($this->userByEmail('admin@gmail.com'))
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_admin_core_pages_and_actions_work(): void
    {
        $admin = $this->userByEmail('admin@gmail.com');
        $pendingRegistration = Registration::query()
            ->where('status', Registration::STATUS_PENDING)
            ->firstOrFail();
        $existingUser = $this->userByEmail('siswa1@gmail.com');
        $existingStudent = $existingUser->student;
        $existingCoach = $this->userByEmail('pembina1@gmail.com')->coach;
        $existingExtracurricular = Extracurricular::query()->firstOrFail();

        $pages = [
            route('admin.dashboard'),
            route('admin.users.index'),
            route('admin.users.create'),
            route('admin.users.show', $existingUser),
            route('admin.users.edit', $existingUser),
            route('admin.students.index'),
            route('admin.students.create'),
            route('admin.students.show', $existingStudent),
            route('admin.students.edit', $existingStudent),
            route('admin.coaches.index'),
            route('admin.coaches.create'),
            route('admin.coaches.show', $existingCoach),
            route('admin.coaches.edit', $existingCoach),
            route('admin.extracurriculars.index'),
            route('admin.extracurriculars.create'),
            route('admin.extracurriculars.show', $existingExtracurricular),
            route('admin.extracurriculars.edit', $existingExtracurricular),
            route('admin.registrations.index'),
            route('admin.participants.index'),
            route('admin.schedules.index'),
            route('admin.attendances.index'),
            route('admin.assessments.index'),
            route('admin.announcements.index'),
        ];

        foreach ($pages as $page) {
            $this->actingAs($admin)->get($page)->assertOk();
        }

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'User Regression',
                'email' => 'user.regression@example.com',
                'role' => User::ROLE_PRINCIPAL,
                'phone' => '081200000100',
                'address' => 'Alamat user regression',
                'password' => '11111111',
                'password_confirmation' => '11111111',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.users.index'));

        $createdUser = $this->userByEmail('user.regression@example.com');

        $this->actingAs($admin)
            ->put(route('admin.users.update', $createdUser), [
                'name' => 'User Regression Update',
                'email' => 'user.regression@example.com',
                'role' => User::ROLE_PRINCIPAL,
                'phone' => '081200000101',
                'address' => 'Alamat user regression update',
                'password' => '',
                'password_confirmation' => '',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.users.index'));

        $createdUser->refresh();
        $this->assertSame('User Regression Update', $createdUser->name);

        $this->actingAs($admin)
            ->post(route('admin.students.store'), [
                'name' => 'Siswa Regression',
                'email' => 'siswa.regression@example.com',
                'phone' => '081200000200',
                'address' => 'Alamat siswa regression',
                'password' => '11111111',
                'password_confirmation' => '11111111',
                'is_active' => '1',
                'nis' => 'NIS-REG-001',
                'class_name' => 'X - 9',
                'gender' => 'L',
                'date_of_birth' => '2008-01-15',
                'parent_name' => 'Orang Tua Regression',
                'parent_phone' => '081200000201',
            ])
            ->assertRedirect(route('admin.students.index'));

        $createdStudent = User::query()->where('email', 'siswa.regression@example.com')->firstOrFail()->student;

        $this->actingAs($admin)
            ->put(route('admin.students.update', $createdStudent), [
                'name' => 'Siswa Regression Update',
                'email' => 'siswa.regression@example.com',
                'phone' => '081200000202',
                'address' => 'Alamat siswa regression update',
                'password' => '',
                'password_confirmation' => '',
                'is_active' => '1',
                'nis' => 'NIS-REG-001',
                'class_name' => 'X - 10',
                'gender' => 'P',
                'date_of_birth' => '2008-01-15',
                'parent_name' => 'Orang Tua Regression Update',
                'parent_phone' => '081200000203',
            ])
            ->assertRedirect(route('admin.students.index'));

        $createdStudent->refresh();
        $this->assertSame('X - 10', $createdStudent->class_name);

        $this->actingAs($admin)
            ->post(route('admin.coaches.store'), [
                'name' => 'Pembina Regression',
                'email' => 'pembina.regression@example.com',
                'phone' => '081200000300',
                'address' => 'Alamat pembina regression',
                'password' => '11111111',
                'password_confirmation' => '11111111',
                'is_active' => '1',
                'nip' => '198801012026010099',
                'bio' => 'Pembina dibuat oleh automated regression test.',
            ])
            ->assertRedirect(route('admin.coaches.index'));

        $createdCoach = User::query()->where('email', 'pembina.regression@example.com')->firstOrFail()->coach;

        $this->actingAs($admin)
            ->put(route('admin.coaches.update', $createdCoach), [
                'name' => 'Pembina Regression Update',
                'email' => 'pembina.regression@example.com',
                'phone' => '081200000301',
                'address' => 'Alamat pembina regression update',
                'password' => '',
                'password_confirmation' => '',
                'is_active' => '1',
                'nip' => '198801012026010099',
                'bio' => 'Pembina diupdate oleh automated regression test.',
            ])
            ->assertRedirect(route('admin.coaches.index'));

        $createdCoach->refresh();
        $this->assertSame('Pembina diupdate oleh automated regression test.', $createdCoach->bio);

        $this->actingAs($admin)
            ->post(route('admin.extracurriculars.store'), [
                'name' => 'Ekstrakurikuler Regression',
                'description' => 'Deskripsi regression test.',
                'requirements' => 'Syarat regression',
                'schedule_overview' => 'Jadwal regression',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.extracurriculars.index'));

        $createdExtracurricular = Extracurricular::query()->where('name', 'Ekstrakurikuler Regression')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.extracurriculars.update', $createdExtracurricular), [
                'name' => 'Ekstrakurikuler Regression Update',
                'description' => 'Deskripsi regression test update.',
                'requirements' => 'Syarat regression update',
                'schedule_overview' => 'Jadwal regression update',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.extracurriculars.index'));

        $createdExtracurricular->refresh();
        $this->assertSame('Ekstrakurikuler Regression Update', $createdExtracurricular->name);

        $this->actingAs($admin)
            ->patch(route('admin.registrations.update-status', $pendingRegistration), [
                'status' => Registration::STATUS_APPROVED,
                'notes' => 'Disetujui oleh regression test',
            ])
            ->assertRedirect();

        $pendingRegistration->refresh();
        $this->assertSame(Registration::STATUS_APPROVED, $pendingRegistration->status);
        $this->assertSame($admin->id, $pendingRegistration->verified_by);

        $this->actingAs($admin)
            ->post(route('admin.announcements.store'), [
                'title' => 'Pengumuman Regression Admin',
                'content' => 'Pengumuman dibuat oleh automated regression test.',
                'extracurricular_id' => $existingExtracurricular->id,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.announcements.index'));

        $announcement = Announcement::query()->where('title', 'Pengumuman Regression Admin')->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('admin.announcements.destroy', $announcement))
            ->assertRedirect(route('admin.announcements.index'));

        $this->actingAs($admin)
            ->delete(route('admin.extracurriculars.destroy', $createdExtracurricular))
            ->assertRedirect(route('admin.extracurriculars.index'));

        $this->actingAs($admin)
            ->delete(route('admin.coaches.destroy', $createdCoach))
            ->assertRedirect(route('admin.coaches.index'));

        $this->actingAs($admin)
            ->delete(route('admin.students.destroy', $createdStudent))
            ->assertRedirect(route('admin.students.index'));

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $createdUser))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
        $this->assertDatabaseMissing('extracurriculars', ['id' => $createdExtracurricular->id]);
        $this->assertDatabaseMissing('coaches', ['id' => $createdCoach->id]);
        $this->assertDatabaseMissing('students', ['id' => $createdStudent->id]);
        $this->assertDatabaseMissing('users', ['id' => $createdUser->id]);
    }

    public function test_student_pages_registration_and_profile_update_work(): void
    {
        $studentUser = $this->userByEmail('siswa3@gmail.com');
        $student = $studentUser->student;
        $extracurricular = Extracurricular::query()
            ->where('is_active', true)
            ->whereDoesntHave('registrations', fn ($query) => $query->where('student_id', $student->id))
            ->firstOrFail();

        $pages = [
            route('student.dashboard'),
            route('student.extracurriculars.index'),
            route('student.extracurriculars.show', $extracurricular),
            route('student.registrations.index'),
            route('student.schedules.index'),
            route('student.attendances.index'),
            route('student.assessments.index'),
            route('profile.edit'),
        ];

        foreach ($pages as $page) {
            $this->actingAs($studentUser)->get($page)->assertOk();
        }

        $this->actingAs($studentUser)
            ->post(route('student.registrations.store', $extracurricular), [
                'motivation_reason' => 'Saya ingin berkembang melalui kegiatan ini.',
                'goal_statement' => 'Saya ingin aktif dan disiplin.',
                'current_skills' => 'Dasar kemampuan awal.',
                'willing_to_take_test' => '1',
                'student_notes' => 'Pendaftaran dari automated regression test.',
            ])
            ->assertRedirect(route('student.extracurriculars.show', $extracurricular));

        $this->assertDatabaseHas('registrations', [
            'student_id' => $student->id,
            'extracurricular_id' => $extracurricular->id,
            'status' => Registration::STATUS_PENDING,
        ]);

        $this->actingAs($studentUser)
            ->put(route('profile.update'), [
                'name' => 'Rizky Ananda Update',
                'email' => 'siswa3@gmail.com',
                'phone' => '081200000003',
                'address' => 'Alamat update regression test',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect();

        $studentUser->refresh();
        $this->assertSame('Rizky Ananda Update', $studentUser->name);
        $this->assertSame('081200000003', $studentUser->phone);
    }

    public function test_public_and_student_activity_filters_keep_osn_and_o2sn_visible(): void
    {
        $studentUser = $this->userByEmail('siswa3@gmail.com');

        $this->get(route('public.activities.category', 'osn'))
            ->assertOk()
            ->assertSee('OSN')
            ->assertSee('Matematika')
            ->assertSee('Informatika')
            ->assertDontSee('0 kegiatan ditemukan');

        $this->get(route('public.activities.category', 'o2sn'))
            ->assertOk()
            ->assertSee('O2SN')
            ->assertSee('Silat')
            ->assertSee('Badminton')
            ->assertDontSee('0 kegiatan ditemukan');

        $this->actingAs($studentUser)
            ->get(route('student.extracurriculars.index', ['category' => 'osn']))
            ->assertOk()
            ->assertSee('OSN')
            ->assertSee('OSN - Matematika')
            ->assertDontSee('O2SN - Silat');

        $this->actingAs($studentUser)
            ->get(route('student.extracurriculars.index', ['category' => 'o2sn']))
            ->assertOk()
            ->assertSee('O2SN')
            ->assertSee('O2SN - Silat')
            ->assertDontSee('OSN - Matematika');
    }

    public function test_coach_pages_schedule_attendance_assessment_and_report_flows_work(): void
    {
        $coachUser = $this->userByEmail('pembina1@gmail.com');
        $coach = $coachUser->coach;
        $extracurricular = $coach->extracurriculars()->firstOrFail();
        $approvedRegistration = Registration::query()
            ->where('extracurricular_id', $extracurricular->id)
            ->where('status', Registration::STATUS_APPROVED)
            ->firstOrFail();
        $schedule = Schedule::query()
            ->where('extracurricular_id', $extracurricular->id)
            ->whereHas('extracurricular.coaches', fn ($query) => $query->whereKey($coach->id))
            ->firstOrFail();

        $pages = [
            route('coach.dashboard'),
            route('coach.extracurriculars.index'),
            route('coach.extracurriculars.participants', $extracurricular),
            route('coach.schedules.index'),
            route('coach.schedules.create'),
            route('coach.attendances.index'),
            route('coach.attendances.index', ['schedule_id' => $schedule->id]),
            route('coach.assessments.index'),
            route('coach.announcements.index'),
        ];

        foreach ($pages as $page) {
            $this->actingAs($coachUser)->get($page)->assertOk();
        }

        $this->actingAs($coachUser)
            ->post(route('coach.schedules.store'), [
                'extracurricular_id' => $extracurricular->id,
                'title' => 'Jadwal Regression Coach',
                'activity_date' => now()->addWeek()->toDateString(),
                'start_time' => '15:00',
                'end_time' => '17:00',
                'location' => 'Lapangan Tengah',
                'description' => 'Jadwal dibuat oleh automated regression test.',
            ])
            ->assertRedirect(route('coach.schedules.index'));

        $newSchedule = Schedule::query()->where('title', 'Jadwal Regression Coach')->firstOrFail();

        $this->actingAs($coachUser)
            ->put(route('coach.schedules.update', $newSchedule), [
                'extracurricular_id' => $extracurricular->id,
                'title' => 'Jadwal Regression Coach Update',
                'activity_date' => now()->addWeeks(2)->toDateString(),
                'start_time' => '16:00',
                'end_time' => '17:30',
                'location' => 'Aula Sekolah',
                'description' => 'Jadwal update oleh automated regression test.',
            ])
            ->assertRedirect(route('coach.schedules.index'));

        $newSchedule->refresh();
        $this->assertSame('Jadwal Regression Coach Update', $newSchedule->title);

        $this->actingAs($coachUser)
            ->post(route('coach.attendances.save', $schedule), [
                'rows' => [
                    [
                        'student_id' => $approvedRegistration->student_id,
                        'status' => 'present',
                        'notes' => 'Dicatat oleh automated regression test',
                    ],
                ],
            ])
            ->assertRedirect(route('coach.attendances.index', ['schedule_id' => $schedule->id]));

        $this->assertDatabaseHas('attendances', [
            'schedule_id' => $schedule->id,
            'student_id' => $approvedRegistration->student_id,
            'status' => 'present',
        ]);

        $this->actingAs($coachUser)
            ->post(route('coach.assessments.store'), [
                'extracurricular_id' => $extracurricular->id,
                'student_id' => $approvedRegistration->student_id,
                'assessment_type' => 'assessment',
                'title' => 'Penilaian Regression Coach',
                'score' => 89,
                'description' => 'Penilaian dibuat oleh automated regression test.',
                'assessment_date' => now()->toDateString(),
            ])
            ->assertRedirect(route('coach.assessments.index'));

        $assessment = Assessment::query()->where('title', 'Penilaian Regression Coach')->firstOrFail();

        $this->actingAs($coachUser)
            ->get(route('coach.assessments.edit', $assessment))
            ->assertOk();

        $this->actingAs($coachUser)
            ->put(route('coach.assessments.update', $assessment), [
                'extracurricular_id' => $extracurricular->id,
                'student_id' => $approvedRegistration->student_id,
                'assessment_type' => 'achievement',
                'title' => 'Penilaian Regression Coach Update',
                'score' => 91,
                'description' => 'Penilaian diupdate oleh automated regression test.',
                'assessment_date' => now()->toDateString(),
            ])
            ->assertRedirect(route('coach.assessments.index'));

        $assessment->refresh();
        $this->assertSame('achievement', $assessment->assessment_type);
        $this->assertSame('Penilaian Regression Coach Update', $assessment->title);

        $this->actingAs($coachUser)
            ->post(route('coach.announcements.store'), [
                'title' => 'Pengumuman Regression Coach',
                'content' => 'Pengumuman pembina oleh automated regression test.',
                'extracurricular_id' => $extracurricular->id,
                'is_active' => '1',
            ])
            ->assertRedirect(route('coach.announcements.index'));

        $coachAnnouncement = Announcement::query()->where('title', 'Pengumuman Regression Coach')->firstOrFail();

        $this->actingAs($coachUser)
            ->delete(route('coach.announcements.destroy', $coachAnnouncement))
            ->assertRedirect(route('coach.announcements.index'));

        $this->actingAs($coachUser)
            ->delete(route('coach.assessments.destroy', $assessment))
            ->assertRedirect(route('coach.assessments.index'));

        $this->actingAs($coachUser)
            ->delete(route('coach.schedules.destroy', $newSchedule))
            ->assertRedirect(route('coach.schedules.index'));

        $this->assertDatabaseMissing('announcements', ['id' => $coachAnnouncement->id]);
        $this->assertDatabaseMissing('assessments', ['id' => $assessment->id]);
        $this->assertDatabaseMissing('schedules', ['id' => $newSchedule->id]);
    }

    public function test_principal_pages_and_exports_work(): void
    {
        $principal = $this->userByEmail('kepsek@gmail.com');

        $this->actingAs($principal)
            ->get(route('principal.dashboard'))
            ->assertOk();

        $this->actingAs($principal)
            ->get(route('principal.attendances.index'))
            ->assertOk();

        $this->actingAs($principal)
            ->get(route('principal.attendances.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_talent_test_flow_and_student_visibility_work(): void
    {
        $coachUser = $this->userByEmail('pembina1@gmail.com');
        $coach = $coachUser->coach;
        $extracurricular = $coach->extracurriculars()->firstOrFail();
        $registration = Registration::query()
            ->where('extracurricular_id', $extracurricular->id)
            ->whereIn('status', [Registration::STATUS_PENDING, Registration::STATUS_APPROVED])
            ->firstOrFail();

        $this->actingAs($coachUser)
            ->post(route('coach.talent-tests.store'), [
                'extracurricular_id' => $extracurricular->id,
                'title' => 'Tes Bakat Regression',
                'activity_date' => now()->addDays(5)->toDateString(),
                'start_time' => '15:00',
                'end_time' => '16:00',
                'location' => 'Lapangan Timur',
                'description' => 'Tes bakat dibuat oleh regression test.',
                'equipment' => 'Sepatu olahraga',
                'instructions' => 'Datang tepat waktu',
                'participant_registration_ids' => [$registration->id],
            ])
            ->assertRedirect(route('coach.talent-tests.index'));

        $talentTest = Schedule::query()->where('title', 'Tes Bakat Regression')->firstOrFail();

        $this->actingAs($coachUser)
            ->get(route('coach.talent-tests.manage', $talentTest))
            ->assertOk();

        $participant = $talentTest->talentTestParticipants()->firstOrFail();
        $aspectIds = $extracurricular->talentTestAspects()->pluck('id')->all();
        $scores = [];
        foreach ($aspectIds as $aspectId) {
            $scores[$aspectId] = 85;
        }

        $this->actingAs($coachUser)
            ->post(route('coach.talent-tests.results.save', $talentTest), [
                'participants' => [[
                    'participant_id' => $participant->id,
                    'attendance_status' => 'present',
                    'attendance_notes' => 'Hadir saat regression test.',
                    'ability_category' => 'Menengah',
                    'training_group' => 'Kelompok A',
                    'recommended_role' => 'Anggota inti',
                    'recommendation' => 'Layak ikut pembinaan lanjutan.',
                    'coach_notes' => 'Stabil.',
                    'internal_notes' => 'Catatan internal.',
                    'scores' => $scores,
                ]],
            ])
            ->assertRedirect();

        $result = \App\Models\TalentTestResult::query()
            ->where('schedule_id', $talentTest->id)
            ->where('student_id', $participant->student_id)
            ->firstOrFail();

        $this->assertSame('draft', $result->status);

        $this->actingAs($coachUser)
            ->post(route('coach.talent-tests.results.save', $talentTest), [
                'publish' => '1',
                'participants' => [[
                    'participant_id' => $participant->id,
                    'attendance_status' => 'present',
                    'ability_category' => 'Menengah',
                    'training_group' => 'Kelompok A',
                    'recommended_role' => 'Anggota inti',
                    'recommendation' => 'Layak ikut pembinaan lanjutan.',
                    'coach_notes' => 'Stabil.',
                    'scores' => $scores,
                ]],
            ])
            ->assertRedirect();

        $result->refresh();
        $this->assertSame('published', $result->status);

        $studentUser = $registration->student->user;
        $this->actingAs($studentUser)
            ->get(route('student.talent-tests.index'))
            ->assertOk()
            ->assertSee('Tes Bakat Regression')
            ->assertSee('Kelompok A');

        $otherStudent = $this->userByEmail('siswa3@gmail.com');
        $this->actingAs($otherStudent)
            ->get(route('registrations.profile-preview', $registration))
            ->assertForbidden();
    }

    private function userByEmail(string $email): User
    {
        return User::query()->where('email', $email)->firstOrFail();
    }
}
