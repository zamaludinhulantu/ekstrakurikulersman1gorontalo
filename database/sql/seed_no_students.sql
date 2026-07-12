-- Import this after running `php artisan migrate --force`.
-- It prepares base accounts and extracurricular data, and intentionally leaves
-- all student-related data empty.
--
-- Login accounts created by this file:
-- admin   : admin@gmail.com / admin12345
-- kepsek  : kepsek@gmail.com / kepsek12345
-- pembina : pembina1@gmail.com / pembina12345
-- pembina : pembina2@gmail.com / pembina12345

SET FOREIGN_KEY_CHECKS=0;

DELETE FROM attendances;
DELETE FROM assessments;
DELETE FROM extracurricular_achievements;
DELETE FROM registrations;
DELETE FROM reports;
DELETE FROM announcements;
DELETE FROM extracurricular_coach;
DELETE FROM schedules;
DELETE FROM extracurriculars;
DELETE FROM students;
DELETE FROM coaches;
DELETE FROM sessions;
DELETE FROM cache;
DELETE FROM jobs;
DELETE FROM job_batches;
DELETE FROM failed_jobs;
DELETE FROM users;

ALTER TABLE attendances AUTO_INCREMENT = 1;
ALTER TABLE assessments AUTO_INCREMENT = 1;
ALTER TABLE extracurricular_achievements AUTO_INCREMENT = 1;
ALTER TABLE registrations AUTO_INCREMENT = 1;
ALTER TABLE reports AUTO_INCREMENT = 1;
ALTER TABLE announcements AUTO_INCREMENT = 1;
ALTER TABLE extracurricular_coach AUTO_INCREMENT = 1;
ALTER TABLE schedules AUTO_INCREMENT = 1;
ALTER TABLE extracurriculars AUTO_INCREMENT = 1;
ALTER TABLE students AUTO_INCREMENT = 1;
ALTER TABLE coaches AUTO_INCREMENT = 1;
ALTER TABLE jobs AUTO_INCREMENT = 1;
ALTER TABLE job_batches AUTO_INCREMENT = 1;
ALTER TABLE failed_jobs AUTO_INCREMENT = 1;
ALTER TABLE users AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS=1;

INSERT INTO users (
    id,
    name,
    email,
    email_verified_at,
    password,
    remember_token,
    role,
    phone,
    address,
    is_active,
    created_at,
    updated_at
) VALUES
    (
        1,
        'Admin Kesiswaan',
        'admin@gmail.com',
        NOW(),
        '$2y$10$nsRwYL4A.PiV77ud1uYM8OfI3c1XEZwXcmA7Hl/TzSUOreVh00DL.',
        NULL,
        'admin',
        '0811111111',
        'Kantor Kesiswaan',
        1,
        NOW(),
        NOW()
    ),
    (
        2,
        'Kepala Sekolah',
        'kepsek@gmail.com',
        NOW(),
        '$2y$10$1tm.Cpvfg9sRoC7Z.EJsFu6Mr95b3rFgpZDy9KkGnuRw.E8zoBu7a',
        NULL,
        'principal',
        '0822222222',
        'Ruang Kepala Sekolah',
        1,
        NOW(),
        NOW()
    ),
    (
        3,
        'Pembina Pramuka',
        'pembina1@gmail.com',
        NOW(),
        '$2y$10$LXII8KZRieotnYHJlCR.MubcROMgR0BTyArWsIw3mG.6STmis93lO',
        NULL,
        'coach',
        '0833333333',
        'SMAN 1 Gorontalo',
        1,
        NOW(),
        NOW()
    ),
    (
        4,
        'Pembina Paskibra',
        'pembina2@gmail.com',
        NOW(),
        '$2y$10$LXII8KZRieotnYHJlCR.MubcROMgR0BTyArWsIw3mG.6STmis93lO',
        NULL,
        'coach',
        '0844444444',
        'SMAN 1 Gorontalo',
        1,
        NOW(),
        NOW()
    );

INSERT INTO coaches (
    id,
    user_id,
    nip,
    expertise,
    bio,
    created_at,
    updated_at
) VALUES
    (
        1,
        3,
        '197801010001',
        'Kepanduan',
        'Membina kegiatan Pramuka sejak 2015.',
        NOW(),
        NOW()
    ),
    (
        2,
        4,
        '198001010002',
        'Kedisiplinan dan baris-berbaris',
        'Membina kegiatan Paskibra tingkat kota.',
        NOW(),
        NOW()
    );

INSERT INTO extracurriculars (
    id,
    coach_id,
    name,
    description,
    requirements,
    schedule_overview,
    image_path,
    is_active,
    created_at,
    updated_at
) VALUES
    (1, 1, 'Pramuka', 'Kegiatan pembinaan karakter dan kepemimpinan melalui kepramukaan.', 'Disiplin, bersedia mengikuti latihan rutin.', 'Setiap Jumat sore.', NULL, 1, NOW(), NOW()),
    (2, 2, 'Paskibra', 'Pelatihan baris-berbaris dan pengibaran bendera.', 'Sehat jasmani, disiplin tinggi.', 'Setiap Rabu dan Sabtu.', NULL, 1, NOW(), NOW()),
    (3, 1, 'PMR', 'Kegiatan pertolongan pertama dan kesehatan remaja.', 'Memiliki minat di bidang kesehatan.', 'Setiap Kamis.', NULL, 1, NOW(), NOW()),
    (4, NULL, 'Tilawatil Qur''an', 'Kegiatan pembinaan bacaan Al-Qur''an dengan fokus pada tajwid, adab, dan kualitas tilawah siswa.', 'Memiliki minat belajar tilawah dan siap mengikuti pembinaan rutin.', 'Jadwal latihan ditetapkan oleh pembina dan pengurus.', NULL, 1, NOW(), NOW()),
    (5, NULL, 'Tartil dan Hifzil Qur''an', 'Program pembinaan tartil dan hafalan Al-Qur''an untuk meningkatkan kemampuan membaca dan menghafal siswa.', 'Memiliki komitmen mengikuti setoran hafalan dan pembinaan tartil.', 'Jadwal pembinaan disesuaikan dengan agenda keagamaan sekolah.', NULL, 1, NOW(), NOW()),
    (6, NULL, 'OPSI', 'Ekstrakurikuler riset dan karya ilmiah untuk mengembangkan budaya penelitian dan inovasi siswa.', 'Tertarik pada penelitian, observasi, dan presentasi ilmiah.', 'Pertemuan riset diatur sesuai proyek dan pembimbing.', NULL, 1, NOW(), NOW()),
    (7, NULL, 'Menulis Artikel', 'Wadah pengembangan literasi, opini, dan penulisan artikel populer maupun informatif oleh siswa.', 'Menyukai membaca, menulis, dan siap mengikuti proses editorial.', 'Sesi penulisan dan review artikel dilakukan berkala.', NULL, 1, NOW(), NOW()),
    (8, NULL, 'Pelsis', 'Kegiatan organisasi siswa untuk membangun kolaborasi, kepemimpinan, dan pelayanan sekolah.', 'Aktif, disiplin, dan siap terlibat dalam program organisasi sekolah.', 'Rapat dan program kerja dilaksanakan sesuai agenda kepengurusan.', NULL, 1, NOW(), NOW()),
    (9, NULL, 'Rohis', 'Kegiatan pembinaan karakter, kajian rutin, dan pengembangan kepemimpinan siswa berbasis nilai keislaman.', 'Aktif mengikuti pembinaan dan kegiatan kerohanian sekolah.', 'Pertemuan rutin mengikuti agenda pembinaan Rohis.', NULL, 1, NOW(), NOW()),
    (10, 2, 'PBB/Paskib', 'Pelatihan peraturan baris-berbaris, kepemimpinan, disiplin, dan kesiapan petugas upacara sekolah.', 'Memiliki kedisiplinan tinggi, fisik prima, dan siap mengikuti latihan rutin.', 'Latihan dilakukan berkala sesuai agenda upacara dan lomba.', NULL, 1, NOW(), NOW()),
    (11, NULL, 'PKS', 'Patroli Keamanan Sekolah untuk membina kedisiplinan, ketertiban, dan kepedulian siswa terhadap lingkungan sekolah.', 'Disiplin, bertanggung jawab, dan siap menjaga ketertiban lingkungan sekolah.', 'Kegiatan dilaksanakan sesuai jadwal piket dan pembinaan.', NULL, 1, NOW(), NOW()),
    (12, NULL, 'SMAG', 'Kegiatan pengembangan karakter dan kreativitas siswa dalam berbagai program sekolah unggulan.', 'Siap berkolaborasi dalam kegiatan sekolah dan pengembangan diri.', 'Pertemuan mengikuti program kerja dan agenda pembinaan.', NULL, 1, NOW(), NOW()),
    (13, NULL, 'RELS', 'Wadah pembinaan kepemimpinan, pelayanan, dan tanggung jawab sosial siswa.', 'Memiliki sikap disiplin, komunikatif, dan siap bekerja dalam tim.', 'Agenda kegiatan dilaksanakan sesuai program organisasi.', NULL, 1, NOW(), NOW()),
    (14, NULL, 'OSIS / MPK', 'Organisasi siswa dan majelis perwakilan kelas untuk menjalankan kepemimpinan, aspirasi, dan program sekolah.', 'Memiliki minat organisasi, kepemimpinan, dan tanggung jawab yang baik.', 'Rapat dan kegiatan menyesuaikan program kerja OSIS/MPK.', NULL, 1, NOW(), NOW()),
    (15, NULL, 'PA/PI Duta', 'Pembinaan siswa untuk peran duta sekolah dalam kegiatan promosi, representasi, dan pelayanan acara resmi.', 'Percaya diri, komunikatif, dan mampu merepresentasikan sekolah dengan baik.', 'Latihan dan briefing dilakukan menjelang agenda representasi sekolah.', NULL, 1, NOW(), NOW()),
    (16, NULL, 'Fortina', 'Ekstrakurikuler pengembangan bakat dan kerja tim siswa dalam program pembinaan sekolah.', 'Aktif, disiplin, dan siap berpartisipasi dalam kegiatan kelompok.', 'Jadwal kegiatan disusun sesuai agenda pembina dan pengurus.', NULL, 1, NOW(), NOW()),
    (17, NULL, 'Konten Kreator', 'Wadah bagi siswa untuk belajar produksi konten digital, dokumentasi, desain, dan publikasi kreatif.', 'Memiliki minat pada foto, video, desain, atau media sosial sekolah.', 'Produksi konten mengikuti agenda sekolah dan jadwal tim kreatif.', NULL, 1, NOW(), NOW());

INSERT INTO extracurricular_achievements (
    id,
    extracurricular_id,
    title,
    description,
    achievement_date,
    created_at,
    updated_at
) VALUES
    (1, 1, 'Juara 2 Lomba Pramuka Tingkat Kota 2025', 'Diraih pada kompetisi kepramukaan tingkat kota.', DATE_SUB(CURDATE(), INTERVAL 60 DAY), NOW(), NOW()),
    (2, 2, 'Anggota terpilih Paskibraka Kota', 'Peserta binaan lolos seleksi paskibraka tingkat kota.', DATE_SUB(CURDATE(), INTERVAL 30 DAY), NOW(), NOW()),
    (3, 3, 'Partisipasi aktif donor darah pelajar', 'Tim PMR aktif dalam kegiatan sosial donor darah.', DATE_SUB(CURDATE(), INTERVAL 21 DAY), NOW(), NOW());

INSERT INTO extracurricular_coach (
    id,
    extracurricular_id,
    coach_id,
    created_at,
    updated_at
) VALUES
    (1, 1, 1, NOW(), NOW()),
    (2, 2, 2, NOW(), NOW()),
    (3, 3, 1, NOW(), NOW()),
    (4, 10, 2, NOW(), NOW());

INSERT INTO schedules (
    id,
    extracurricular_id,
    coach_id,
    title,
    activity_date,
    start_time,
    end_time,
    location,
    description,
    created_at,
    updated_at
) VALUES
    (1, 1, 1, 'Latihan Materi Simpul', CURDATE(), '15:00:00', '17:00:00', 'Lapangan Sekolah', 'Latihan teknik simpul dan tali-temali.', NOW(), NOW()),
    (2, 2, 2, 'Latihan Formasi', CURDATE(), '16:00:00', '17:30:00', 'Lapangan Upacara', 'Latihan formasi pasukan.', NOW(), NOW()),
    (3, 1, 1, 'Persiapan Kemah', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '15:00:00', '17:00:00', 'Ruang Aula', 'Briefing perlengkapan kemah.', NOW(), NOW()),
    (4, 3, 1, 'Pelatihan Pertolongan Pertama', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '15:30:00', '17:00:00', 'UKS Sekolah', 'Simulasi penanganan cedera ringan.', NOW(), NOW()),
    (5, 10, 2, 'Latihan PBB Rutin', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:30:00', '17:00:00', 'Lapangan Utama', 'Penguatan formasi dan kedisiplinan baris-berbaris.', NOW(), NOW());

-- Student-related tables remain empty by design.
