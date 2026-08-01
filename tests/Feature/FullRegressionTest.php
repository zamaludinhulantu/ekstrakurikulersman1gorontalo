<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\Report;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Models\User;
use App\Mail\SystemTestMail;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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
        ])->assertRedirect(route('verification.notice', ['email' => 'siswa.mandiri@example.com']));

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('value="Siswa Mandiri"', false)
            ->assertSee('value="siswa.mandiri@example.com"', false)
            ->assertSee('Alamat registrasi mandiri');

        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'email' => 'siswa.mandiri@example.com',
            'role' => User::ROLE_STUDENT,
        ]);
        $this->assertDatabaseHas('students', [
            'parent_name' => 'Ibu Mandiri',
            'class_name' => 'X - 5',
            'nis' => null,
        ]);

        $this->post(route('register.store'), [
            'name' => 'Siswa Tanggal Tidak Valid',
            'email' => 'siswa.invalid@example.com',
            'password' => '11111111',
            'password_confirmation' => '11111111',
            'class_name' => 'X - 6',
            'gender' => 'L',
            'date_of_birth' => '2026-07-27',
        ])->assertSessionHasErrors('date_of_birth');
    }

    public function test_login_logout_and_role_dashboards_work(): void
    {
        $this->post(route('login.attempt'), [
            'email' => 'admin@gmail.com',
            'password' => '11111111',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();

        $dashboardMap = [
            'superadmin@gmail.com' => route('super-admin.dashboard'),
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

    public function test_super_admin_can_manage_users_and_operational_admin_stays_working(): void
    {
        Mail::fake();

        $admin = $this->userByEmail('admin@gmail.com');
        $superAdmin = $this->userByEmail('superadmin@gmail.com');
        $pendingRegistration = Registration::query()
            ->where('status', Registration::STATUS_PENDING)
            ->firstOrFail();
        $existingUser = $this->userByEmail('siswa1@gmail.com');
        $existingStudent = $existingUser->student;
        $existingCoach = $this->userByEmail('pembina1@gmail.com')->coach;
        $existingExtracurricular = Extracurricular::query()->firstOrFail();

        $superAdminPages = [
            route('super-admin.dashboard'),
            route('super-admin.users.index'),
            route('super-admin.users.create'),
            route('super-admin.users.show', $existingUser),
            route('super-admin.users.edit', $existingUser),
            route('super-admin.system.index'),
            route('super-admin.maintenance.index'),
            route('super-admin.audit-logs.index'),
        ];

        foreach ($superAdminPages as $page) {
            $this->actingAs($superAdmin)->get($page)->assertOk();
        }

        $adminPages = [
            route('admin.dashboard'),
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

        foreach ($adminPages as $page) {
            $this->actingAs($admin)->get($page)->assertOk();
        }

        $this->actingAs($admin)
            ->get(route('super-admin.users.index'))
            ->assertForbidden();

        $this->actingAs($superAdmin)
            ->post(route('super-admin.users.store'), [
                'name' => 'User Regression',
                'email' => 'user.regression@example.com',
                'role' => User::ROLE_PRINCIPAL,
                'phone' => '081200000100',
                'address' => 'Alamat user regression',
                'password' => '11111111',
                'password_confirmation' => '11111111',
                'is_active' => '1',
            ])
            ->assertRedirect(route('super-admin.users.index'));

        $createdUser = $this->userByEmail('user.regression@example.com');

        $this->actingAs($superAdmin)
            ->put(route('super-admin.users.update', $createdUser), [
                'name' => 'User Regression Update',
                'email' => 'user.regression@example.com',
                'role' => User::ROLE_PRINCIPAL,
                'phone' => '081200000101',
                'address' => 'Alamat user regression update',
                'password' => '',
                'password_confirmation' => '',
                'is_active' => '1',
            ])
            ->assertRedirect(route('super-admin.users.index'));

        $createdUser->refresh();
        $this->assertSame('User Regression Update', $createdUser->name);

        $onlySuperAdmin = $this->userByEmail('superadmin@gmail.com');

        $this->actingAs($superAdmin)
            ->put(route('super-admin.users.update', $onlySuperAdmin), [
                'name' => 'Super Admin',
                'email' => 'superadmin@gmail.com',
                'role' => User::ROLE_ADMIN,
                'phone' => '0800000000',
                'address' => 'Ruang Server Sekolah',
                'password' => '',
                'password_confirmation' => '',
                'is_active' => '1',
            ])
            ->assertSessionHas('error', 'Super admin terakhir tidak dapat diubah rolenya atau dinonaktifkan.');

        $this->actingAs($superAdmin)
            ->delete(route('super-admin.users.destroy', $onlySuperAdmin))
            ->assertSessionHas('error', 'Anda tidak dapat menghapus akun sendiri.');

        $this->actingAs($superAdmin)
            ->put(route('super-admin.system.email.update'), [
                'mail_mailer' => 'smtp',
                'mail_smtp_host' => 'smtp.example.com',
                'mail_smtp_port' => 587,
                'mail_smtp_username' => 'noreply@example.com',
                'mail_smtp_password' => 'secret123',
                'mail_smtp_encryption' => 'tls',
                'mail_from_address' => 'noreply@example.com',
                'mail_from_name' => 'Ekstrakurikuler SMAN 1',
            ])
            ->assertSessionHas('success', 'Konfigurasi email berhasil diperbarui.');

        $this->assertSame('smtp.example.com', SystemSetting::getValue('mail_smtp_host'));
        $this->assertSame('secret123', SystemSetting::getValue('mail_smtp_password'));

        $this->actingAs($superAdmin)
            ->post(route('super-admin.system.email.test'), [
                'test_email' => 'uji@example.com',
            ])
            ->assertSessionHas('success', 'Email uji berhasil dikirim.');

        Mail::assertSent(SystemTestMail::class);

        $this->actingAs($superAdmin)
            ->put(route('super-admin.maintenance.update'), [
                'maintenance_enabled' => '1',
                'maintenance_message' => 'Sedang maintenance pengujian.',
            ])
            ->assertSessionHas('success', 'Mode maintenance berhasil diaktifkan.');

        auth()->logout();

        $this->get(route('landing'))
            ->assertStatus(503)
            ->assertSee('Sedang maintenance pengujian.');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertStatus(503);

        $this->actingAs($superAdmin)
            ->get(route('super-admin.dashboard'))
            ->assertOk();

        $this->actingAs($superAdmin)
            ->put(route('super-admin.maintenance.update'), [
                'maintenance_message' => 'Maintenance selesai.',
            ])
            ->assertSessionHas('success', 'Mode maintenance berhasil dinonaktifkan.');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'system.email.updated',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'system.email.test_sent',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'system.maintenance.enabled',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'system.maintenance.disabled',
        ]);
        $this->assertGreaterThanOrEqual(3, AuditLog::query()->count());

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
                'decision' => 'approve',
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
                'is_active' => '0',
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

        $this->actingAs($superAdmin)
            ->delete(route('super-admin.users.destroy', $createdUser))
            ->assertRedirect(route('super-admin.users.index'));

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

    public function test_student_attendance_history_uses_talent_test_participant_status(): void
    {
        $studentUser = $this->userByEmail('siswa3@gmail.com');
        $student = $studentUser->student;
        $coach = $this->userByEmail('pembina1@gmail.com')->coach;
        $extracurricular = $coach->extracurriculars()->firstOrFail();

        $registration = Registration::query()->firstOrCreate(
            [
                'student_id' => $student->id,
                'extracurricular_id' => $extracurricular->id,
            ],
            [
                'registration_date' => now()->toDateString(),
                'status' => Registration::STATUS_APPROVED,
            ]
        );

        if ($registration->status !== Registration::STATUS_APPROVED) {
            $registration->update(['status' => Registration::STATUS_APPROVED]);
        }

        $talentTest = Schedule::query()->create([
            'extracurricular_id' => $extracurricular->id,
            'coach_id' => $coach->id,
            'schedule_type' => 'talent_test',
            'title' => 'Tes Bakat Presensi Siswa',
            'activity_date' => now()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '09:00',
            'location' => 'Ruang Presensi',
            'status' => 'scheduled',
        ]);

        $talentTest->talentTestParticipants()->create([
            'registration_id' => $registration->id,
            'student_id' => $student->id,
            'assigned_by' => $coach->user_id,
            'attendance_status' => 'present',
            'attendance_notes' => 'Hadir tepat waktu.',
        ]);

        $this->actingAs($studentUser)
            ->get(route('student.attendances.index'))
            ->assertOk()
            ->assertSee('Tes Bakat Presensi Siswa')
            ->assertSee('Hadir')
            ->assertSee('Hadir tepat waktu.');
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
        $secondApprovedRegistration = Registration::query()
            ->where('extracurricular_id', $extracurricular->id)
            ->whereKeyNot($approvedRegistration->id)
            ->first();

        if (! $secondApprovedRegistration) {
            $candidateStudent = Student::query()
                ->whereDoesntHave('registrations', function ($query) use ($extracurricular): void {
                    $query->where('extracurricular_id', $extracurricular->id);
                })
                ->firstOrFail();

            $secondApprovedRegistration = Registration::query()->create([
                'student_id' => $candidateStudent->id,
                'extracurricular_id' => $extracurricular->id,
                'registration_date' => now()->toDateString(),
                'status' => Registration::STATUS_APPROVED,
                'notes' => 'Disetujui untuk regression test peserta jadwal.',
            ]);
        } elseif ($secondApprovedRegistration->status !== Registration::STATUS_APPROVED) {
            $secondApprovedRegistration->update([
                'status' => Registration::STATUS_APPROVED,
                'notes' => 'Disetujui untuk regression test peserta jadwal.',
            ]);
        }

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
                'participant_registration_ids' => [$approvedRegistration->id],
            ])
            ->assertRedirect(route('coach.schedules.index'));

        $newSchedule = Schedule::query()->where('title', 'Jadwal Regression Coach')->firstOrFail();
        $this->assertDatabaseHas('schedule_participants', [
            'schedule_id' => $newSchedule->id,
            'registration_id' => $approvedRegistration->id,
            'student_id' => $approvedRegistration->student_id,
        ]);
        $this->assertDatabaseMissing('schedule_participants', [
            'schedule_id' => $newSchedule->id,
            'student_id' => $secondApprovedRegistration->student_id,
        ]);

        $this->actingAs($coachUser)
            ->put(route('coach.schedules.update', $newSchedule), [
                'extracurricular_id' => $extracurricular->id,
                'title' => 'Jadwal Regression Coach Update',
                'activity_date' => now()->addWeeks(2)->toDateString(),
                'start_time' => '16:00',
                'end_time' => '17:30',
                'location' => 'Aula Sekolah',
                'description' => 'Jadwal update oleh automated regression test.',
                'participant_registration_ids' => [$approvedRegistration->id, $secondApprovedRegistration->id],
            ])
            ->assertRedirect(route('coach.schedules.index'));

        $newSchedule->refresh();
        $this->assertSame('Jadwal Regression Coach Update', $newSchedule->title);
        $this->assertDatabaseHas('schedule_participants', [
            'schedule_id' => $newSchedule->id,
            'student_id' => $secondApprovedRegistration->student_id,
        ]);

        $attendancePage = $this->actingAs($coachUser)
            ->get(route('coach.attendances.index', ['schedule_id' => $newSchedule->id]));

        $attendancePage
            ->assertOk()
            ->assertSee($approvedRegistration->student->user->name)
            ->assertSee($secondApprovedRegistration->student->user->name);

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
                'is_active' => '0',
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
        $studentUser = $registration->student->user;

        $this->actingAs($studentUser)
            ->get(route('student.schedules.index'))
            ->assertOk()
            ->assertSee('Tes Bakat Regression')
            ->assertSee('Tes Bakat');

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
                    'decision_status' => 'accepted',
                    'decision_notes' => 'Lulus tes bakat dan diterima.',
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
        $this->assertSame('accepted', $result->decision_status);
        $registration->refresh();
        $this->assertSame(Registration::STATUS_APPROVED, $registration->status);

        $studentUser = $registration->student->user;
        $this->actingAs($studentUser)
            ->get(route('student.talent-tests.index'))
            ->assertOk()
            ->assertSee('Tes Bakat Regression')
            ->assertSee('Diterima ke Ekskul');

        $otherStudent = $this->userByEmail('siswa3@gmail.com');
        $this->actingAs($otherStudent)
            ->get(route('registrations.profile-preview', $registration))
            ->assertForbidden();
    }

    public function test_talent_test_publish_allows_partial_scoring(): void
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
                'title' => 'Tes Bakat Partial Score',
                'activity_date' => now()->addDays(4)->toDateString(),
                'start_time' => '13:00',
                'end_time' => '14:00',
                'location' => 'Ruang Uji',
                'description' => 'Tes untuk partial scoring.',
                'equipment' => 'Alat tulis',
                'instructions' => 'Ikuti arahan pembina',
                'participant_registration_ids' => [$registration->id],
            ])
            ->assertRedirect(route('coach.talent-tests.index'));

        $talentTest = Schedule::query()->where('title', 'Tes Bakat Partial Score')->firstOrFail();
        $participant = $talentTest->talentTestParticipants()->firstOrFail();
        $aspectIds = $extracurricular->talentTestAspects()->pluck('id')->values();

        $this->actingAs($coachUser)
            ->post(route('coach.talent-tests.results.save', $talentTest), [
                'publish' => '1',
                'participants' => [[
                    'participant_id' => $participant->id,
                    'attendance_status' => 'present',
                    'ability_category' => 'Menengah',
                    'decision_status' => 'accepted',
                    'training_group' => 'Kelompok B',
                    'recommendation' => 'Cukup baik.',
                    'scores' => [
                        $aspectIds[0] => 88,
                    ],
                ]],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('talent_test_results', [
            'schedule_id' => $talentTest->id,
            'student_id' => $participant->student_id,
            'status' => 'published',
            'ability_category' => 'Menengah',
            'decision_status' => 'accepted',
        ]);
    }

    public function test_talent_test_publish_without_aspects_still_allows_complete_core_result(): void
    {
        $coachUser = $this->userByEmail('pembina1@gmail.com');
        $coach = $coachUser->coach;
        $extracurricular = $coach->extracurriculars()->firstOrFail();
        $extracurricular->talentTestAspects()->delete();

        $registration = Registration::query()
            ->where('extracurricular_id', $extracurricular->id)
            ->whereIn('status', [Registration::STATUS_PENDING, Registration::STATUS_APPROVED])
            ->firstOrFail();

        $this->actingAs($coachUser)
            ->post(route('coach.talent-tests.store'), [
                'extracurricular_id' => $extracurricular->id,
                'title' => 'Tes Bakat Tanpa Aspek',
                'activity_date' => now()->addDays(5)->toDateString(),
                'start_time' => '10:00',
                'end_time' => '11:00',
                'location' => 'Aula Uji',
                'description' => 'Tes tanpa aspek terpisah.',
                'equipment' => 'Tidak ada',
                'instructions' => 'Ikuti instruksi pembina',
                'participant_registration_ids' => [$registration->id],
            ])
            ->assertRedirect(route('coach.talent-tests.index'));

        $talentTest = Schedule::query()->where('title', 'Tes Bakat Tanpa Aspek')->firstOrFail();
        $participant = $talentTest->talentTestParticipants()->firstOrFail();

        $this->actingAs($coachUser)
            ->post(route('coach.talent-tests.results.save', $talentTest), [
                'publish' => '1',
                'participants' => [[
                    'participant_id' => $participant->id,
                    'attendance_status' => 'present',
                    'overall_score' => 91.5,
                    'ability_category' => 'Menengah',
                    'decision_status' => 'accepted',
                    'decision_notes' => 'Lulus tanpa aspek tambahan.',
                    'coach_notes' => 'Data inti lengkap.',
                    'scores' => [],
                ]],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('talent_test_results', [
            'schedule_id' => $talentTest->id,
            'student_id' => $participant->student_id,
            'status' => 'published',
            'overall_score' => 91.50,
            'ability_category' => 'Menengah',
            'decision_status' => 'accepted',
        ]);
    }

    public function test_coach_can_apply_bulk_decision_to_selected_talent_test_participants(): void
    {
        $coachUser = $this->userByEmail('pembina1@gmail.com');
        $coach = $coachUser->coach;
        $extracurricular = $coach->extracurriculars()->firstOrFail();
        $registrations = Registration::query()
            ->where('extracurricular_id', $extracurricular->id)
            ->whereIn('status', [Registration::STATUS_PENDING, Registration::STATUS_APPROVED])
            ->take(2)
            ->get();

        if ($registrations->count() < 2) {
            $candidateStudent = Student::query()
                ->whereDoesntHave('registrations', function ($query) use ($extracurricular): void {
                    $query->where('extracurricular_id', $extracurricular->id);
                })
                ->firstOrFail();

            $registrations->push(Registration::query()->create([
                'student_id' => $candidateStudent->id,
                'extracurricular_id' => $extracurricular->id,
                'registration_date' => now()->toDateString(),
                'status' => Registration::STATUS_PENDING,
                'willing_to_take_test' => true,
            ]));
        }

        $this->actingAs($coachUser)
            ->post(route('coach.talent-tests.store'), [
                'extracurricular_id' => $extracurricular->id,
                'title' => 'Tes Bakat Bulk Decision',
                'activity_date' => now()->addDays(4)->toDateString(),
                'start_time' => '10:00',
                'end_time' => '11:00',
                'location' => 'Ruang Seleksi',
                'participant_registration_ids' => $registrations->pluck('id')->all(),
            ])
            ->assertRedirect(route('coach.talent-tests.index'));

        $talentTest = Schedule::query()->where('title', 'Tes Bakat Bulk Decision')->firstOrFail();
        $participants = $talentTest->talentTestParticipants()->orderBy('id')->get();
        $aspectId = $extracurricular->talentTestAspects()->value('id');

        $payloadParticipants = $participants->map(function ($participant) use ($aspectId): array {
            return [
                'participant_id' => $participant->id,
                'attendance_status' => 'present',
                'ability_category' => 'Menengah',
                'coach_notes' => 'Siap diputuskan massal.',
                'scores' => [
                    $aspectId => 82,
                ],
            ];
        })->all();

        $this->actingAs($coachUser)
            ->post(route('coach.talent-tests.results.save', $talentTest), [
                'apply_bulk_decision' => '1',
                'bulk_overall_score' => 84.5,
                'bulk_ability_category' => 'Menengah',
                'bulk_decision_status' => 'accepted',
                'bulk_decision_notes' => 'Diterima melalui aksi massal.',
                'selected_participant_ids' => $participants->pluck('id')->all(),
                'participants' => $payloadParticipants,
            ])
            ->assertRedirect();

        foreach ($participants as $participant) {
            $this->assertDatabaseHas('talent_test_results', [
                'schedule_id' => $talentTest->id,
                'student_id' => $participant->student_id,
                'status' => 'draft',
                'overall_score' => 84.50,
                'ability_category' => 'Menengah',
                'decision_status' => 'accepted',
            ]);
        }
    }

    public function test_registration_schedule_test_creates_talent_test_without_directly_approving(): void
    {
        $admin = $this->userByEmail('admin@gmail.com');
        $registration = Registration::query()
            ->where('status', Registration::STATUS_PENDING)
            ->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.registrations.update-status', $registration), [
                'decision' => 'schedule_test',
                'notes' => 'Perlu tes bakat terlebih dahulu.',
                'schedule_title' => 'Tes Bakat Seleksi Admin',
                'schedule_date' => now()->addDays(3)->toDateString(),
                'schedule_start_time' => '08:00',
                'schedule_end_time' => '09:30',
                'schedule_location' => 'Aula Utama',
                'schedule_description' => 'Bawa perlengkapan sesuai kebutuhan.',
            ])
            ->assertRedirect();

        $registration->refresh();

        $this->assertSame(Registration::STATUS_PENDING, $registration->status);
        $this->assertTrue($registration->willing_to_take_test);
        $this->assertSame(Registration::DISPLAY_STATUS_SCHEDULED_TEST, $registration->displayStatus());
        $this->assertFalse($registration->canStudentEdit());

        $schedule = Schedule::query()
            ->where('title', 'Tes Bakat Seleksi Admin')
            ->firstOrFail();

        $this->assertSame('talent_test', $schedule->schedule_type);
        $this->assertDatabaseHas('talent_test_participants', [
            'schedule_id' => $schedule->id,
            'registration_id' => $registration->id,
            'student_id' => $registration->student_id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'registration.verified',
            'subject_type' => Registration::class,
            'subject_id' => $registration->id,
        ]);

        $studentUser = $registration->student->user;
        $this->actingAs($studentUser)
            ->get(route('student.registrations.edit', $registration))
            ->assertRedirect(route('student.registrations.index'))
            ->assertSessionHas('error');
    }

    public function test_registration_schedule_test_can_reuse_existing_talent_test_schedule(): void
    {
        $admin = $this->userByEmail('admin@gmail.com');
        $registration = Registration::query()
            ->where('status', Registration::STATUS_PENDING)
            ->firstOrFail();

        $existingSchedule = Schedule::query()->create([
            'extracurricular_id' => $registration->extracurricular_id,
            'coach_id' => $registration->extracurricular->coaches()->value('coaches.id') ?? $registration->extracurricular->coach_id,
            'schedule_type' => 'talent_test',
            'title' => 'Tes Bakat Bersama',
            'activity_date' => now()->addDays(4)->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'location' => 'Ruang Serbaguna',
            'description' => 'Sesi reuse jadwal.',
            'status' => 'scheduled',
        ]);

        $initialScheduleCount = Schedule::query()
            ->where('schedule_type', 'talent_test')
            ->count();

        $this->actingAs($admin)
            ->patch(route('admin.registrations.update-status', $registration), [
                'decision' => 'schedule_test',
                'notes' => 'Masuk ke jadwal tes bersama.',
                'existing_schedule_id' => $existingSchedule->id,
            ])
            ->assertRedirect();

        $registration->refresh();

        $this->assertSame(Registration::STATUS_PENDING, $registration->status);
        $this->assertTrue($registration->willing_to_take_test);
        $this->assertSame($initialScheduleCount, Schedule::query()->where('schedule_type', 'talent_test')->count());
        $this->assertDatabaseHas('talent_test_participants', [
            'schedule_id' => $existingSchedule->id,
            'registration_id' => $registration->id,
            'student_id' => $registration->student_id,
        ]);
    }

    public function test_coach_can_schedule_test_from_registration_verification(): void
    {
        $coachUser = $this->userByEmail('pembina1@gmail.com');
        $coach = $coachUser->coach;
        $extracurricular = $coach->extracurriculars()->firstOrFail();
        $student = Student::query()
            ->whereDoesntHave('registrations', fn ($query) => $query->where('extracurricular_id', $extracurricular->id))
            ->firstOrFail();
        $registration = Registration::query()->create([
            'student_id' => $student->id,
            'extracurricular_id' => $extracurricular->id,
            'registration_date' => now()->toDateString(),
            'status' => Registration::STATUS_PENDING,
            'selected_branch' => 'Seleksi pembina',
            'primary_talent' => 'Koordinasi gerak',
        ]);

        $this->actingAs($coachUser)
            ->patch(route('coach.registrations.update-status', $registration), [
                'decision' => 'schedule_test',
                'notes' => 'Tes bakat oleh pembina.',
                'schedule_title' => 'Tes Bakat Pembina',
                'schedule_date' => now()->addDays(2)->toDateString(),
                'schedule_start_time' => '13:00',
                'schedule_end_time' => '14:00',
                'schedule_location' => 'Studio Latihan',
            ])
            ->assertRedirect();

        $registration->refresh();
        $schedule = Schedule::query()->where('title', 'Tes Bakat Pembina')->firstOrFail();

        $this->assertSame(Registration::STATUS_PENDING, $registration->status);
        $this->assertTrue($registration->willing_to_take_test);
        $this->assertSame((int) $coach->id, (int) $schedule->coach_id);
        $this->assertDatabaseHas('talent_test_participants', [
            'schedule_id' => $schedule->id,
            'registration_id' => $registration->id,
            'student_id' => $registration->student_id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $coachUser->id,
            'action' => 'registration.verified',
            'subject_type' => Registration::class,
            'subject_id' => $registration->id,
        ]);
    }

    public function test_super_admin_can_delete_schedule_but_admin_cannot(): void
    {
        $superAdmin = $this->userByEmail('superadmin@gmail.com');
        $admin = $this->userByEmail('admin@gmail.com');
        $coach = $this->userByEmail('pembina1@gmail.com')->coach;
        $extracurricular = $coach->extracurriculars()->firstOrFail();

        $schedule = Schedule::query()->create([
            'extracurricular_id' => $extracurricular->id,
            'coach_id' => $coach->id,
            'schedule_type' => 'activity',
            'title' => 'Latihan Super Admin Hapus',
            'activity_date' => now()->addDays(5)->toDateString(),
            'start_time' => '15:00',
            'end_time' => '17:00',
            'location' => 'Lapangan Utama',
            'status' => 'scheduled',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.schedules.destroy', $schedule))
            ->assertForbidden();

        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('admin.schedules.destroy', $schedule))
            ->assertRedirect(route('admin.schedules.index'))
            ->assertSessionHas('success', 'Jadwal latihan berhasil dihapus.');

        $this->assertDatabaseMissing('schedules', [
            'id' => $schedule->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'schedule.deleted',
            'subject_type' => Schedule::class,
            'subject_id' => $schedule->id,
        ]);
    }

    public function test_coach_can_delete_talent_test(): void
    {
        $coachUser = $this->userByEmail('pembina1@gmail.com');
        $coach = $coachUser->coach;
        $extracurricular = $coach->extracurriculars()->firstOrFail();

        $talentTest = Schedule::query()->create([
            'extracurricular_id' => $extracurricular->id,
            'coach_id' => $coach->id,
            'schedule_type' => 'talent_test',
            'title' => 'Tes Bakat Hapus',
            'activity_date' => now()->addDays(3)->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'location' => 'Aula',
            'status' => 'scheduled',
        ]);

        $this->actingAs($coachUser)
            ->delete(route('coach.talent-tests.destroy', $talentTest))
            ->assertRedirect(route('coach.talent-tests.index'))
            ->assertSessionHas('success', 'Tes bakat berhasil dihapus.');

        $this->assertDatabaseMissing('schedules', [
            'id' => $talentTest->id,
        ]);
    }

    public function test_student_cannot_add_fourth_active_registration_but_old_data_stays_intact(): void
    {
        $studentUser = $this->userByEmail('siswa1@gmail.com');
        $student = $studentUser->student;
        $coach = $this->userByEmail('pembina1@gmail.com')->coach;

        Registration::query()->where('student_id', $student->id)->delete();

        $extracurriculars = collect(range(1, 4))->map(function (int $index) use ($coach): Extracurricular {
            $extracurricular = Extracurricular::query()->create([
                'coach_id' => $coach->id,
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => 'Ekskul Limit '.$index,
                'description' => 'Ekskul untuk uji limit.',
                'requirements' => null,
                'schedule_overview' => 'Setiap Senin',
                'branch_options' => [],
                'is_active' => true,
            ]);
            $extracurricular->coaches()->syncWithoutDetaching([$coach->id]);

            return $extracurricular;
        });

        foreach ($extracurriculars->take(3) as $extracurricular) {
            Registration::query()->create([
                'student_id' => $student->id,
                'extracurricular_id' => $extracurricular->id,
                'registration_date' => now()->toDateString(),
                'status' => Registration::STATUS_APPROVED,
            ]);
        }

        $targetExtracurricular = $extracurriculars->last();

        $this->actingAs($studentUser)
            ->post(route('student.registrations.store', $targetExtracurricular), [
                'motivation_reason' => 'Ingin ikut juga.',
            ])
            ->assertSessionHas('error', 'Anda sudah terdaftar pada 3 ekstrakurikuler. Jika ingin mendaftar ekstrakurikuler lain, batalkan salah satu pendaftaran terlebih dahulu.');

        $this->assertDatabaseMissing('registrations', [
            'student_id' => $student->id,
            'extracurricular_id' => $targetExtracurricular->id,
        ]);
        $this->assertSame(3, $student->fresh()->activeRegistrationCount());
    }

    public function test_student_can_cancel_registration_and_cleanup_related_participants(): void
    {
        $studentUser = $this->userByEmail('siswa1@gmail.com');
        $student = $studentUser->student;
        $registration = Registration::query()
            ->where('student_id', $student->id)
            ->whereIn('status', [Registration::STATUS_PENDING, Registration::STATUS_APPROVED])
            ->first();

        if (! $registration) {
            $coach = $this->userByEmail('pembina1@gmail.com')->coach;
            $extracurricular = $coach->extracurriculars()->firstOrFail();
            $registration = Registration::query()->create([
                'student_id' => $student->id,
                'extracurricular_id' => $extracurricular->id,
                'registration_date' => now()->toDateString(),
                'status' => Registration::STATUS_APPROVED,
            ]);
        }

        $activitySchedule = Schedule::query()->create([
            'extracurricular_id' => $registration->extracurricular_id,
            'coach_id' => $registration->extracurricular->coaches()->value('coaches.id') ?? $registration->extracurricular->coach_id,
            'schedule_type' => 'activity',
            'title' => 'Jadwal Untuk Uji Batal',
            'activity_date' => now()->addDays(5)->toDateString(),
            'start_time' => '15:00',
            'end_time' => '16:00',
            'location' => 'Lapangan Uji',
            'status' => 'scheduled',
        ]);

        $activitySchedule->scheduleParticipants()->create([
            'registration_id' => $registration->id,
            'student_id' => $registration->student_id,
            'assigned_by' => $this->userByEmail('pembina1@gmail.com')->id,
        ]);

        $talentTest = Schedule::query()->create([
            'extracurricular_id' => $registration->extracurricular_id,
            'coach_id' => $registration->extracurricular->coaches()->value('coaches.id') ?? $registration->extracurricular->coach_id,
            'schedule_type' => 'talent_test',
            'title' => 'Tes Untuk Uji Batal',
            'activity_date' => now()->addDays(6)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'location' => 'Ruang Uji',
            'status' => 'scheduled',
        ]);

        $talentTest->talentTestParticipants()->create([
            'registration_id' => $registration->id,
            'student_id' => $registration->student_id,
            'assigned_by' => $studentUser->id,
            'attendance_status' => 'pending',
        ]);

        $beforeCount = $student->fresh()->activeRegistrationCount();

        $this->actingAs($studentUser)
            ->delete(route('student.registrations.destroy', $registration))
            ->assertRedirect(route('student.registrations.index'))
            ->assertSessionHas('success', 'Permintaan pembatalan berhasil dikirim dan menunggu konfirmasi Admin atau Pembina.');

        $registration->refresh();

        $this->assertNotSame(Registration::STATUS_CANCELLED, $registration->status);
        $this->assertNotNull($registration->cancellation_requested_at);
        $this->assertDatabaseHas('schedule_participants', [
            'schedule_id' => $activitySchedule->id,
            'registration_id' => $registration->id,
            'student_id' => $registration->student_id,
        ]);
        $this->assertDatabaseHas('talent_test_participants', [
            'schedule_id' => $talentTest->id,
            'registration_id' => $registration->id,
            'student_id' => $registration->student_id,
        ]);
        $this->assertSame($beforeCount, $student->fresh()->activeRegistrationCount());

        $this->actingAs($this->userByEmail('admin@gmail.com'))
            ->patch(route('admin.registrations.review-cancellation', $registration), [
                'decision' => 'approve',
            ])
            ->assertSessionHas('success', 'Pembatalan keikutsertaan berhasil disetujui.');

        $registration->refresh();

        $this->assertSame(Registration::STATUS_CANCELLED, $registration->status);
        $this->assertNull($registration->cancellation_requested_at);
        $this->assertStringContainsString('Permintaan pembatalan disetujui Admin', (string) $registration->notes);
        $this->assertFalse($registration->willing_to_take_test);
        $this->assertDatabaseMissing('schedule_participants', [
            'schedule_id' => $activitySchedule->id,
            'registration_id' => $registration->id,
            'student_id' => $registration->student_id,
        ]);
        $this->assertDatabaseMissing('talent_test_participants', [
            'schedule_id' => $talentTest->id,
            'registration_id' => $registration->id,
            'student_id' => $registration->student_id,
        ]);
        $this->assertSame(max(0, $beforeCount - 1), $student->fresh()->activeRegistrationCount());
    }

    public function test_coach_can_remove_approved_participant_from_extracurricular(): void
    {
        $coachUser = $this->userByEmail('pembina1@gmail.com');
        $coach = $coachUser->coach;
        $extracurricular = $coach->extracurriculars()->firstOrFail();
        $registration = Registration::query()
            ->where('extracurricular_id', $extracurricular->id)
            ->where('status', Registration::STATUS_APPROVED)
            ->first();

        if (! $registration) {
            $student = Student::query()
                ->whereDoesntHave('registrations', fn ($query) => $query->where('extracurricular_id', $extracurricular->id))
                ->firstOrFail();

            $registration = Registration::query()->create([
                'student_id' => $student->id,
                'extracurricular_id' => $extracurricular->id,
                'registration_date' => now()->toDateString(),
                'status' => Registration::STATUS_APPROVED,
            ]);
        }

        $schedule = Schedule::query()->create([
            'extracurricular_id' => $extracurricular->id,
            'coach_id' => $coach->id,
            'schedule_type' => 'activity',
            'title' => 'Jadwal Untuk Uji Keluarkan',
            'activity_date' => now()->addDays(3)->toDateString(),
            'start_time' => '08:00',
            'end_time' => '09:00',
            'location' => 'Aula Uji',
            'status' => 'scheduled',
        ]);

        $schedule->scheduleParticipants()->create([
            'registration_id' => $registration->id,
            'student_id' => $registration->student_id,
            'assigned_by' => $coachUser->id,
        ]);

        $this->actingAs($coachUser)
            ->delete(route('coach.extracurriculars.participants.destroy', [$extracurricular, $registration]))
            ->assertRedirect(route('coach.extracurriculars.participants', $extracurricular))
            ->assertSessionHas('success', 'Peserta berhasil dikeluarkan dari ekstrakurikuler.');

        $registration->refresh();

        $this->assertSame(Registration::STATUS_CANCELLED, $registration->status);
        $this->assertSame($coachUser->id, $registration->verified_by);
        $this->assertStringContainsString('Dikeluarkan oleh pembina', (string) $registration->notes);
        $this->assertDatabaseMissing('schedule_participants', [
            'schedule_id' => $schedule->id,
            'registration_id' => $registration->id,
            'student_id' => $registration->student_id,
        ]);
    }

    public function test_legacy_registration_overflow_only_shows_warning_without_changing_old_data(): void
    {
        $studentUser = $this->userByEmail('siswa2@gmail.com');
        $student = $studentUser->student;
        $admin = $this->userByEmail('admin@gmail.com');
        $coachUser = $this->userByEmail('pembina1@gmail.com');
        $coach = $coachUser->coach;

        Registration::query()->where('student_id', $student->id)->delete();

        $extracurriculars = collect(range(1, 4))->map(function (int $index) use ($coach): Extracurricular {
            $extracurricular = Extracurricular::query()->create([
                'coach_id' => $coach->id,
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => 'Ekskul Overflow '.$index,
                'description' => 'Ekskul untuk uji warning legacy.',
                'requirements' => null,
                'schedule_overview' => 'Setiap Selasa',
                'branch_options' => [],
                'is_active' => true,
            ]);
            $extracurricular->coaches()->syncWithoutDetaching([$coach->id]);

            return $extracurricular;
        });

        foreach ($extracurriculars as $extracurricular) {
            Registration::query()->create([
                'student_id' => $student->id,
                'extracurricular_id' => $extracurricular->id,
                'registration_date' => now()->toDateString(),
                'status' => Registration::STATUS_APPROVED,
            ]);
        }

        $warningText = 'Data pendaftaran siswa ini melebihi batas maksimal 3 ekstrakurikuler. Data lama tetap disimpan, tetapi pendaftaran baru tidak dapat ditambahkan.';

        $this->actingAs($studentUser)
            ->get(route('student.registrations.index'))
            ->assertOk()
            ->assertSee($warningText);

        $this->actingAs($admin)
            ->get(route('admin.registrations.index'))
            ->assertOk()
            ->assertSee('Pendaftaran baru harus ditahan.');

        $this->actingAs($coachUser)
            ->get(route('coach.registrations.index'))
            ->assertOk()
            ->assertSee('Pendaftaran baru harus ditahan.');

        $this->assertSame(4, $student->fresh()->activeRegistrationCount());
    }

    private function userByEmail(string $email): User
    {
        return User::query()->where('email', $email)->firstOrFail();
    }
}
