<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\Coach;
use App\Models\Extracurricular;
use App\Models\ExtracurricularAchievement;
use App\Models\Registration;
use App\Models\Report;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin Kesiswaan',
            'email' => 'admin@gmail.com',
            'password' => '11111111',
            'role' => User::ROLE_ADMIN,
            'phone' => '0811111111',
            'address' => 'Kantor Kesiswaan',
            'is_active' => true,
        ]);

        $principal = User::create([
            'name' => 'Kepala Sekolah',
            'email' => 'kepsek@gmail.com',
            'password' => '11111111',
            'role' => User::ROLE_PRINCIPAL,
            'phone' => '0822222222',
            'address' => 'Ruang Kepala Sekolah',
            'is_active' => true,
        ]);

        $coachUser1 = User::create([
            'name' => 'Pembina Pramuka',
            'email' => 'pembina1@gmail.com',
            'password' => '11111111',
            'role' => User::ROLE_COACH,
            'phone' => '0833333333',
            'address' => 'SMAN 1 Gorontalo',
            'is_active' => true,
        ]);

        $coachUser2 = User::create([
            'name' => 'Pembina Paskibra',
            'email' => 'pembina2@gmail.com',
            'password' => '11111111',
            'role' => User::ROLE_COACH,
            'phone' => '0844444444',
            'address' => 'SMAN 1 Gorontalo',
            'is_active' => true,
        ]);

        $coach1 = Coach::create([
            'user_id' => $coachUser1->id,
            'nip' => '197801010001',
            'expertise' => 'Kepanduan',
            'bio' => 'Membina kegiatan Pramuka sejak 2015.',
        ]);

        $coach2 = Coach::create([
            'user_id' => $coachUser2->id,
            'nip' => '198001010002',
            'expertise' => 'Kedisiplinan dan baris-berbaris',
            'bio' => 'Membina kegiatan Paskibra tingkat kota.',
        ]);

        $studentUser1 = User::create([
            'name' => 'Ahmad Yusuf',
            'email' => 'siswa1@gmail.com',
            'password' => '11111111',
            'role' => User::ROLE_STUDENT,
            'phone' => '0855555555',
            'address' => 'Kelurahan Limba U1',
            'is_active' => true,
        ]);

        $studentUser2 = User::create([
            'name' => 'Siti Rahma',
            'email' => 'siswa2@gmail.com',
            'password' => '11111111',
            'role' => User::ROLE_STUDENT,
            'phone' => '0866666666',
            'address' => 'Kelurahan Dulalowo',
            'is_active' => true,
        ]);

        $student1 = Student::create([
            'user_id' => $studentUser1->id,
            'nis' => '2026001',
            'class_name' => 'X IPA 1',
            'gender' => 'L',
            'date_of_birth' => '2010-01-10',
            'address' => 'Kelurahan Limba U1',
            'parent_name' => 'Bapak Yusuf',
            'parent_phone' => '0877777777',
        ]);

        $student2 = Student::create([
            'user_id' => $studentUser2->id,
            'nis' => '2026002',
            'class_name' => 'X IPA 2',
            'gender' => 'P',
            'date_of_birth' => '2010-05-25',
            'address' => 'Kelurahan Dulalowo',
            'parent_name' => 'Ibu Rahmawati',
            'parent_phone' => '0888888888',
        ]);

        $studentUser3 = User::create([
            'name' => 'Rizky Ananda',
            'email' => 'siswa3@gmail.com',
            'password' => '11111111',
            'role' => User::ROLE_STUDENT,
            'phone' => '0899999999',
            'address' => 'Kelurahan Heledulaa',
            'is_active' => true,
        ]);

        Student::create([
            'user_id' => $studentUser3->id,
            'nis' => '2026003',
            'class_name' => 'X IPA 3',
            'gender' => 'L',
            'date_of_birth' => '2010-09-14',
            'address' => 'Kelurahan Heledulaa',
            'parent_name' => 'Bapak Ananda',
            'parent_phone' => '081234567890',
        ]);

        $pramuka = Extracurricular::create([
            'coach_id' => $coach1->id,
            'name' => 'Pramuka',
            'description' => 'Kegiatan pembinaan karakter dan kepemimpinan melalui kepramukaan.',
            'requirements' => 'Disiplin, bersedia mengikuti latihan rutin.',
            'schedule_overview' => 'Setiap Jumat sore.',
            'is_active' => true,
        ]);

        $paskibra = Extracurricular::create([
            'coach_id' => $coach2->id,
            'name' => 'Paskibra',
            'description' => 'Pelatihan baris-berbaris dan pengibaran bendera.',
            'requirements' => 'Sehat jasmani, disiplin tinggi.',
            'schedule_overview' => 'Setiap Rabu dan Sabtu.',
            'is_active' => true,
        ]);

        $pmr = Extracurricular::create([
            'coach_id' => $coach1->id,
            'name' => 'PMR',
            'description' => 'Kegiatan pertolongan pertama dan kesehatan remaja.',
            'requirements' => 'Memiliki minat di bidang kesehatan.',
            'schedule_overview' => 'Setiap Kamis.',
            'is_active' => true,
        ]);
        $this->call(ExtracurricularCatalogSeeder::class);

        Registration::create([
            'student_id' => $student1->id,
            'extracurricular_id' => $pramuka->id,
            'registration_date' => now()->subDays(12)->toDateString(),
            'status' => Registration::STATUS_APPROVED,
            'notes' => 'Disetujui oleh admin',
            'verified_by' => $admin->id,
            'verified_at' => now()->subDays(11),
        ]);

        Registration::create([
            'student_id' => $student1->id,
            'extracurricular_id' => $paskibra->id,
            'registration_date' => now()->subDays(4)->toDateString(),
            'status' => Registration::STATUS_PENDING,
        ]);

        Registration::create([
            'student_id' => $student2->id,
            'extracurricular_id' => $paskibra->id,
            'registration_date' => now()->subDays(8)->toDateString(),
            'status' => Registration::STATUS_APPROVED,
            'verified_by' => $admin->id,
            'verified_at' => now()->subDays(7),
        ]);

        Registration::create([
            'student_id' => $student2->id,
            'extracurricular_id' => $pramuka->id,
            'registration_date' => now()->subDays(5)->toDateString(),
            'status' => Registration::STATUS_REJECTED,
            'notes' => 'Kuota penuh',
            'verified_by' => $admin->id,
            'verified_at' => now()->subDays(4),
        ]);

        $schedule1 = Schedule::create([
            'extracurricular_id' => $pramuka->id,
            'coach_id' => $coach1->id,
            'title' => 'Latihan Materi Simpul',
            'activity_date' => now()->subDays(3)->toDateString(),
            'start_time' => '15:00:00',
            'end_time' => '17:00:00',
            'location' => 'Lapangan Sekolah',
            'description' => 'Latihan teknik simpul dan tali-temali.',
        ]);

        $schedule2 = Schedule::create([
            'extracurricular_id' => $paskibra->id,
            'coach_id' => $coach2->id,
            'title' => 'Latihan Formasi',
            'activity_date' => now()->subDays(2)->toDateString(),
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'location' => 'Lapangan Upacara',
            'description' => 'Latihan formasi pasukan.',
        ]);

        Schedule::create([
            'extracurricular_id' => $pramuka->id,
            'coach_id' => $coach1->id,
            'title' => 'Persiapan Kemah',
            'activity_date' => now()->addDays(3)->toDateString(),
            'start_time' => '15:00:00',
            'end_time' => '17:00:00',
            'location' => 'Ruang Aula',
            'description' => 'Briefing perlengkapan kemah.',
        ]);

        Attendance::create([
            'schedule_id' => $schedule1->id,
            'extracurricular_id' => $pramuka->id,
            'student_id' => $student1->id,
            'recorded_by' => $coachUser1->id,
            'status' => 'present',
            'notes' => 'Aktif mengikuti latihan.',
            'recorded_at' => now()->subDays(3),
        ]);

        Attendance::create([
            'schedule_id' => $schedule2->id,
            'extracurricular_id' => $paskibra->id,
            'student_id' => $student2->id,
            'recorded_by' => $coachUser2->id,
            'status' => 'sick',
            'notes' => 'Izin karena sakit.',
            'recorded_at' => now()->subDays(2),
        ]);

        Assessment::create([
            'student_id' => $student1->id,
            'extracurricular_id' => $pramuka->id,
            'coach_id' => $coach1->id,
            'assessment_type' => 'assessment',
            'title' => 'Kedisiplinan Latihan',
            'score' => 88.50,
            'description' => 'Kehadiran baik dan aktif.',
            'assessment_date' => now()->subDays(2)->toDateString(),
        ]);

        Assessment::create([
            'student_id' => $student2->id,
            'extracurricular_id' => $paskibra->id,
            'coach_id' => $coach2->id,
            'assessment_type' => 'achievement',
            'title' => 'Juara Formasi Terbaik',
            'score' => 92.00,
            'description' => 'Meraih penghargaan pada lomba internal.',
            'assessment_date' => now()->subDay()->toDateString(),
        ]);

        ExtracurricularAchievement::create([
            'extracurricular_id' => $pramuka->id,
            'title' => 'Juara 2 Lomba Pramuka Tingkat Kota 2025',
            'description' => 'Diraih pada kompetisi kepramukaan tingkat kota.',
            'achievement_date' => now()->subMonths(2)->toDateString(),
        ]);

        ExtracurricularAchievement::create([
            'extracurricular_id' => $paskibra->id,
            'title' => 'Anggota terpilih Paskibraka Kota',
            'description' => 'Peserta binaan lolos seleksi paskibraka tingkat kota.',
            'achievement_date' => now()->subMonths(1)->toDateString(),
        ]);

        ExtracurricularAchievement::create([
            'extracurricular_id' => $pmr->id,
            'title' => 'Partisipasi aktif donor darah pelajar',
            'description' => 'Tim PMR aktif dalam kegiatan sosial donor darah.',
            'achievement_date' => now()->subWeeks(3)->toDateString(),
        ]);

        Report::create([
            'title' => 'Laporan Kegiatan Pramuka Bulanan',
            'report_type' => 'activity',
            'extracurricular_id' => $pramuka->id,
            'generated_by' => $coachUser1->id,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'content' => 'Kegiatan berjalan sesuai jadwal dan peserta aktif.',
        ]);

        Report::create([
            'title' => 'Laporan Rekap Presensi',
            'report_type' => 'attendance',
            'extracurricular_id' => null,
            'generated_by' => $admin->id,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->toDateString(),
            'content' => 'Sebagian besar peserta hadir tepat waktu.',
        ]);

        Report::create([
            'title' => 'Ringkasan Prestasi Peserta',
            'report_type' => 'summary',
            'extracurricular_id' => null,
            'generated_by' => $principal->id,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->toDateString(),
            'content' => 'Terdapat peningkatan performa siswa pada ekstrakurikuler aktif.',
        ]);
    }
}
