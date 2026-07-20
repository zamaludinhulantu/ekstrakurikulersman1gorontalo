<?php

namespace Database\Seeders;

use App\Models\Coach;
use App\Models\Extracurricular;
use Illuminate\Database\Seeder;

class ExtracurricularCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $paskibCoachId = Coach::query()
            ->whereHas('user', fn ($query) => $query->where('email', 'pembina2@gmail.com'))
            ->value('id');

        $groupedPrograms = [
            [
                'parent' => 'OSN',
                'type' => Extracurricular::TYPE_OLYMPIAD,
                'schedule_overview' => 'Jadwal pembinaan disusun mengikuti agenda seleksi dan pendampingan sekolah.',
                'requirements' => 'Memiliki minat akademik kuat, siap mengikuti pembinaan intensif, dan berkomitmen pada seleksi bertahap.',
                'branches' => [
                    'Matematika',
                    'Fisika',
                    'Biologi',
                    'Kimia',
                    'IPA Terpadu',
                    'Ekonomi',
                    'Geografi',
                    'Kebumian',
                    'Astronomi',
                    'Informatika',
                ],
            ],
            [
                'parent' => 'O2SN',
                'type' => Extracurricular::TYPE_OLYMPIAD,
                'schedule_overview' => 'Pembinaan cabang olahraga dijadwalkan sesuai agenda latihan dan tahapan seleksi.',
                'requirements' => 'Memiliki kesiapan fisik, disiplin latihan, dan siap mengikuti seleksi cabang olahraga sesuai ketentuan sekolah.',
                'branches' => [
                    'Silat',
                    'Karate & Taekwondo',
                    'Renang',
                    'Badminton',
                    'Atletik',
                    'Panjat Tebing',
                    'Tenis Meja',
                    'Takraw',
                    'Volly Ball',
                    'Basket Ball',
                    'Futsal / Sepak Bola',
                ],
            ],
            [
                'parent' => 'FLS3N',
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'schedule_overview' => 'Jadwal pembinaan seni disusun mengikuti agenda latihan, kurasi karya, dan persiapan lomba.',
                'requirements' => 'Memiliki minat di bidang seni, siap berlatih rutin, dan mengikuti pembinaan sesuai cabang lomba.',
                'branches' => [
                    'Vokalia PA/PI',
                    'Cipta Lagu',
                    'Baca Puisi',
                    'Cipta Puisi',
                    'Design Poster PA/PI',
                    'Komik Digital',
                    'Monolog',
                    'Kriya PA/PI',
                    'Gitar Solo',
                    'Tari Kreasi',
                    'Film Pendek',
                    'Musik Tradisional',
                    'Fotography',
                    'Jurnalistik',
                    'Menulis Cerpen',
                ],
            ],
            [
                'parent' => 'DEBAT',
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'schedule_overview' => 'Pembinaan debat mengikuti agenda latihan argumentasi, diskusi isu, dan simulasi lomba.',
                'requirements' => 'Siap berlatih argumentasi, public speaking, dan berpikir kritis sesuai bidang debat yang dipilih.',
                'branches' => [
                    'Bahasa Inggris',
                    'Bahasa Indonesia',
                    'Hukum',
                    'Ekonomi',
                ],
            ],
            [
                'parent' => 'KEG. MUSIUM',
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'schedule_overview' => 'Pembinaan kegiatan museum disusun mengikuti agenda sejarah, seni, dan eksplorasi budaya sekolah.',
                'requirements' => 'Memiliki minat pada sejarah, budaya, atau karya kreatif dan siap mengikuti pembinaan rutin.',
                'branches' => [
                    'Tutur Sejarah',
                    'Melukis',
                    'Cipta Lagu Tentang Musium',
                    'Tarian Tidi',
                    'Paiya Lo Hungulo Poli',
                ],
            ],
        ];

        $baseActivities = [
            [
                'type' => Extracurricular::TYPE_OLYMPIAD,
                'name' => 'OSN',
                'coach_id' => null,
                'description' => 'Olimpiade Sains Nasional dengan cabang Matematika, IPA Terpadu, Geografi, Ekonomi, Kebumian, Astronomi, dan Informatika.',
                'requirements' => 'Memiliki minat akademik kuat, siap mengikuti pembinaan intensif, dan berkomitmen pada seleksi bertahap.',
                'schedule_overview' => 'Jadwal pembinaan disusun mengikuti agenda seleksi dan pendampingan sekolah.',
                'branch_options' => [
                    'Matematika',
                    'IPA Terpadu',
                    'Geografi',
                    'Ekonomi',
                    'Kebumian',
                    'Astronomi',
                    'Informatika',
                ],
                'is_active' => false,
            ],
            [
                'type' => Extracurricular::TYPE_OLYMPIAD,
                'name' => 'O2SN',
                'coach_id' => null,
                'description' => 'Olimpiade Olahraga Siswa Nasional dengan cabang Silat, Karate dan Taekwondo, Renang, Badminton, Atletik, Panjat Tebing, Tenis Meja, Takraw, Volly Ball, Basket Ball, serta Futsal atau Sepak Bola.',
                'requirements' => 'Memiliki kesiapan fisik, disiplin latihan, dan siap mengikuti seleksi cabang olahraga sesuai ketentuan sekolah.',
                'schedule_overview' => 'Pembinaan cabang olahraga dijadwalkan sesuai agenda latihan dan tahapan seleksi.',
                'branch_options' => [
                    'Silat',
                    'Karate & Taekwondo',
                    'Renang',
                    'Badminton',
                    'Atletik',
                    'Panjat Tebing',
                    'Tenis Meja',
                    'Takraw',
                    'Volly Ball',
                    'Basket Ball',
                    'Futsal / Sepak Bola',
                ],
                'is_active' => false,
            ],
            [
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => "Tilawatil Qur'an",
                'coach_id' => null,
                'description' => "Kegiatan pembinaan bacaan Al-Qur'an dengan fokus pada tajwid, adab, dan kualitas tilawah siswa.",
                'requirements' => 'Memiliki minat belajar tilawah dan siap mengikuti pembinaan rutin.',
                'schedule_overview' => 'Jadwal latihan ditetapkan oleh pembina dan pengurus.',
            ],
            [
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => "Tartil dan Hifzil Qur'an",
                'coach_id' => null,
                'description' => "Program pembinaan tartil dan hafalan Al-Qur'an untuk meningkatkan kemampuan membaca dan menghafal siswa.",
                'requirements' => 'Memiliki komitmen mengikuti setoran hafalan dan pembinaan tartil.',
                'schedule_overview' => 'Jadwal pembinaan disesuaikan dengan agenda keagamaan sekolah.',
            ],
            [
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => 'OPSI',
                'coach_id' => null,
                'description' => 'Ekstrakurikuler riset dan karya ilmiah untuk mengembangkan budaya penelitian dan inovasi siswa.',
                'requirements' => 'Tertarik pada penelitian, observasi, dan presentasi ilmiah.',
                'schedule_overview' => 'Pertemuan riset diatur sesuai proyek dan pembimbing.',
            ],
            [
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => 'Menulis Artikel',
                'coach_id' => null,
                'description' => 'Wadah pengembangan literasi, opini, dan penulisan artikel populer maupun informatif oleh siswa.',
                'requirements' => 'Menyukai membaca, menulis, dan siap mengikuti proses editorial.',
                'schedule_overview' => 'Sesi penulisan dan review artikel dilakukan berkala.',
            ],
            [
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => 'Pelsis',
                'coach_id' => null,
                'description' => 'Kegiatan organisasi siswa untuk membangun kolaborasi, kepemimpinan, dan pelayanan sekolah.',
                'requirements' => 'Aktif, disiplin, dan siap terlibat dalam program organisasi sekolah.',
                'schedule_overview' => 'Rapat dan program kerja dilaksanakan sesuai agenda kepengurusan.',
            ],
            [
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => 'Rohis',
                'coach_id' => null,
                'description' => 'Kegiatan pembinaan karakter, kajian rutin, dan pengembangan kepemimpinan siswa berbasis nilai keislaman.',
                'requirements' => 'Aktif mengikuti pembinaan dan kegiatan kerohanian sekolah.',
                'schedule_overview' => 'Pertemuan rutin mengikuti agenda pembinaan Rohis.',
            ],
            [
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => 'PBB / Paskib',
                'coach_id' => $paskibCoachId,
                'description' => 'Pelatihan peraturan baris-berbaris, kepemimpinan, disiplin, dan kesiapan petugas upacara sekolah.',
                'requirements' => 'Memiliki kedisiplinan tinggi, fisik prima, dan siap mengikuti latihan rutin.',
                'schedule_overview' => 'Latihan dilakukan berkala sesuai agenda upacara dan lomba.',
            ],
            [
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => 'PKS',
                'coach_id' => null,
                'description' => 'Patroli Keamanan Sekolah untuk membina kedisiplinan, ketertiban, dan kepedulian siswa terhadap lingkungan sekolah.',
                'requirements' => 'Disiplin, bertanggung jawab, dan siap menjaga ketertiban lingkungan sekolah.',
                'schedule_overview' => 'Kegiatan dilaksanakan sesuai jadwal piket dan pembinaan.',
            ],
            [
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => 'SMAG',
                'coach_id' => null,
                'description' => 'Kegiatan pengembangan karakter dan kreativitas siswa dalam berbagai program sekolah unggulan.',
                'requirements' => 'Siap berkolaborasi dalam kegiatan sekolah dan pengembangan diri.',
                'schedule_overview' => 'Pertemuan mengikuti program kerja dan agenda pembinaan.',
            ],
            [
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => 'RELS',
                'coach_id' => null,
                'description' => 'Wadah pembinaan kepemimpinan, pelayanan, dan tanggung jawab sosial siswa.',
                'requirements' => 'Memiliki sikap disiplin, komunikatif, dan siap bekerja dalam tim.',
                'schedule_overview' => 'Agenda kegiatan dilaksanakan sesuai program organisasi.',
            ],
            [
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => 'OSIS / MPK',
                'coach_id' => null,
                'description' => 'Organisasi siswa dan majelis perwakilan kelas untuk menjalankan kepemimpinan, aspirasi, dan program sekolah.',
                'requirements' => 'Memiliki minat organisasi, kepemimpinan, dan tanggung jawab yang baik.',
                'schedule_overview' => 'Rapat dan kegiatan menyesuaikan program kerja OSIS/MPK.',
            ],
            [
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => 'PA/PI Duta',
                'coach_id' => null,
                'description' => 'Pembinaan siswa untuk peran duta sekolah dalam kegiatan promosi, representasi, dan pelayanan acara resmi.',
                'requirements' => 'Percaya diri, komunikatif, dan mampu merepresentasikan sekolah dengan baik.',
                'schedule_overview' => 'Latihan dan briefing dilakukan menjelang agenda representasi sekolah.',
            ],
            [
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => 'Fortina',
                'coach_id' => null,
                'description' => 'Ekstrakurikuler pengembangan bakat dan kerja tim siswa dalam program pembinaan sekolah.',
                'requirements' => 'Aktif, disiplin, dan siap berpartisipasi dalam kegiatan kelompok.',
                'schedule_overview' => 'Jadwal kegiatan disusun sesuai agenda pembina dan pengurus.',
            ],
            [
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => 'Konten Kreator',
                'coach_id' => null,
                'description' => 'Wadah bagi siswa untuk belajar produksi konten digital, dokumentasi, desain, dan publikasi kreatif.',
                'requirements' => 'Memiliki minat pada foto, video, desain, atau media sosial sekolah.',
                'schedule_overview' => 'Produksi konten mengikuti agenda sekolah dan jadwal tim kreatif.',
            ],
            [
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => 'Pramuka',
                'coach_id' => null,
                'description' => 'Kegiatan kepramukaan untuk membina kemandirian, kepemimpinan, disiplin, dan kerja sama siswa.',
                'requirements' => 'Siap mengikuti latihan rutin, kegiatan lapangan, dan pembinaan karakter kepramukaan.',
                'schedule_overview' => 'Latihan dilaksanakan sesuai agenda gugus depan dan program pembina.',
            ],
            [
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => 'PIK KR',
                'coach_id' => null,
                'description' => 'Pusat Informasi dan Konseling Remaja untuk membina kepedulian, edukasi, dan komunikasi sebaya siswa.',
                'requirements' => 'Memiliki minat pada edukasi sebaya, komunikasi, dan kegiatan pembinaan remaja.',
                'schedule_overview' => 'Pertemuan dan pembinaan mengikuti agenda program PIK KR sekolah.',
            ],
            [
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => 'PMR',
                'coach_id' => null,
                'description' => 'Palang Merah Remaja untuk membina kepedulian sosial, kesiapsiagaan, dan keterampilan dasar kesehatan siswa.',
                'requirements' => 'Siap mengikuti latihan dasar kesehatan, kegiatan sosial, dan pembinaan kedisiplinan.',
                'schedule_overview' => 'Latihan dan kegiatan sosial dilaksanakan mengikuti agenda pembina dan sekolah.',
            ],
            [
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => 'Menulis Artikel / Essai',
                'coach_id' => null,
                'description' => 'Wadah pengembangan literasi, opini, artikel, dan essai siswa untuk meningkatkan kualitas menulis.',
                'requirements' => 'Menyukai membaca, menulis, dan siap mengikuti proses editorial serta pembinaan literasi.',
                'schedule_overview' => 'Sesi penulisan, review, dan diskusi karya dilakukan secara berkala.',
            ],
            [
                'type' => Extracurricular::TYPE_EXTRACURRICULAR,
                'name' => 'PA/PI DUT',
                'coach_id' => null,
                'description' => 'Pembinaan siswa untuk peran duta sekolah dalam kegiatan promosi, representasi, dan pelayanan acara resmi.',
                'requirements' => 'Percaya diri, komunikatif, dan mampu merepresentasikan sekolah dengan baik.',
                'schedule_overview' => 'Latihan dan briefing dilakukan menjelang agenda representasi sekolah.',
            ],
        ];

        collect($baseActivities)->each(function (array $extracurricular): void {
            $legacyNames = match ($extracurricular['name']) {
                'Menulis Artikel / Essai' => ['Menulis Artikel'],
                'PA/PI DUT' => ['PA/PI Duta'],
                'PBB / Paskib' => ['PBB/Paskib'],
                default => [],
            };

            $existing = Extracurricular::query()
                ->where('name', $extracurricular['name'])
                ->when($legacyNames !== [], fn ($query) => $query->orWhereIn('name', $legacyNames))
                ->first();

            if ($existing) {
                $existing->update($extracurricular + ['is_active' => true]);

                return;
            }

            Extracurricular::create($extracurricular + ['is_active' => true]);
        });

        collect($groupedPrograms)->each(function (array $program): void {
            collect($program['branches'])->each(function (string $branch) use ($program): void {
                $name = $program['parent'].' - '.$branch;

                Extracurricular::updateOrCreate(
                    ['name' => $name],
                    [
                        'type' => $program['type'],
                        'coach_id' => null,
                        'description' => $program['parent'].' untuk bidang '.$branch.'.',
                        'requirements' => $program['requirements'],
                        'schedule_overview' => $program['schedule_overview'],
                        'branch_options' => null,
                        'is_active' => true,
                    ],
                );
            });
        });
    }
}
