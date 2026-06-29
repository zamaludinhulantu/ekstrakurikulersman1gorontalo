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

        collect([
            [
                'name' => "Tilawatil Qur'an",
                'coach_id' => null,
                'description' => "Kegiatan pembinaan bacaan Al-Qur'an dengan fokus pada tajwid, adab, dan kualitas tilawah siswa.",
                'requirements' => 'Memiliki minat belajar tilawah dan siap mengikuti pembinaan rutin.',
                'schedule_overview' => 'Jadwal latihan ditetapkan oleh pembina dan pengurus.',
                'achievements_overview' => 'Pembinaan tilawah untuk kegiatan keagamaan dan lomba sekolah.',
            ],
            [
                'name' => "Tartil dan Hifzil Qur'an",
                'coach_id' => null,
                'description' => "Program pembinaan tartil dan hafalan Al-Qur'an untuk meningkatkan kemampuan membaca dan menghafal siswa.",
                'requirements' => 'Memiliki komitmen mengikuti setoran hafalan dan pembinaan tartil.',
                'schedule_overview' => 'Jadwal pembinaan disesuaikan dengan agenda keagamaan sekolah.',
                'achievements_overview' => 'Mendukung penguatan karakter religius dan prestasi bidang tahfiz.',
            ],
            [
                'name' => 'OPSI',
                'coach_id' => null,
                'description' => 'Ekstrakurikuler riset dan karya ilmiah untuk mengembangkan budaya penelitian dan inovasi siswa.',
                'requirements' => 'Tertarik pada penelitian, observasi, dan presentasi ilmiah.',
                'schedule_overview' => 'Pertemuan riset diatur sesuai proyek dan pembimbing.',
                'achievements_overview' => 'Mendorong partisipasi siswa pada lomba penelitian dan inovasi.',
            ],
            [
                'name' => 'Menulis Artikel',
                'coach_id' => null,
                'description' => 'Wadah pengembangan literasi, opini, dan penulisan artikel populer maupun informatif oleh siswa.',
                'requirements' => 'Menyukai membaca, menulis, dan siap mengikuti proses editorial.',
                'schedule_overview' => 'Sesi penulisan dan review artikel dilakukan berkala.',
                'achievements_overview' => 'Mendukung publikasi karya siswa di media sekolah dan lomba literasi.',
            ],
            [
                'name' => 'Pelsis',
                'coach_id' => null,
                'description' => 'Kegiatan organisasi siswa untuk membangun kolaborasi, kepemimpinan, dan pelayanan sekolah.',
                'requirements' => 'Aktif, disiplin, dan siap terlibat dalam program organisasi sekolah.',
                'schedule_overview' => 'Rapat dan program kerja dilaksanakan sesuai agenda kepengurusan.',
                'achievements_overview' => 'Meningkatkan pengalaman organisasi dan manajemen kegiatan siswa.',
            ],
            [
                'name' => 'Rohis',
                'coach_id' => null,
                'description' => 'Kegiatan pembinaan karakter, kajian rutin, dan pengembangan kepemimpinan siswa berbasis nilai keislaman.',
                'requirements' => 'Aktif mengikuti pembinaan dan kegiatan kerohanian sekolah.',
                'schedule_overview' => 'Pertemuan rutin mengikuti agenda pembinaan Rohis.',
                'achievements_overview' => 'Program kajian, pembinaan, dan kegiatan sosial keagamaan siswa.',
            ],
            [
                'name' => 'PBB/Paskib',
                'coach_id' => $paskibCoachId,
                'description' => 'Pelatihan peraturan baris-berbaris, kepemimpinan, disiplin, dan kesiapan petugas upacara sekolah.',
                'requirements' => 'Memiliki kedisiplinan tinggi, fisik prima, dan siap mengikuti latihan rutin.',
                'schedule_overview' => 'Latihan dilakukan berkala sesuai agenda upacara dan lomba.',
                'achievements_overview' => 'Mendukung pembinaan pasukan upacara dan kompetisi baris-berbaris.',
            ],
            [
                'name' => 'PKS',
                'coach_id' => null,
                'description' => 'Patroli Keamanan Sekolah untuk membina kedisiplinan, ketertiban, dan kepedulian siswa terhadap lingkungan sekolah.',
                'requirements' => 'Disiplin, bertanggung jawab, dan siap menjaga ketertiban lingkungan sekolah.',
                'schedule_overview' => 'Kegiatan dilaksanakan sesuai jadwal piket dan pembinaan.',
                'achievements_overview' => 'Membantu penguatan budaya tertib dan aman di sekolah.',
            ],
            [
                'name' => 'SMAG',
                'coach_id' => null,
                'description' => 'Kegiatan pengembangan karakter dan kreativitas siswa dalam berbagai program sekolah unggulan.',
                'requirements' => 'Siap berkolaborasi dalam kegiatan sekolah dan pengembangan diri.',
                'schedule_overview' => 'Pertemuan mengikuti program kerja dan agenda pembinaan.',
                'achievements_overview' => 'Mendukung partisipasi aktif siswa dalam agenda sekolah.',
            ],
            [
                'name' => 'RELS',
                'coach_id' => null,
                'description' => 'Wadah pembinaan kepemimpinan, pelayanan, dan tanggung jawab sosial siswa.',
                'requirements' => 'Memiliki sikap disiplin, komunikatif, dan siap bekerja dalam tim.',
                'schedule_overview' => 'Agenda kegiatan dilaksanakan sesuai program organisasi.',
                'achievements_overview' => 'Mengembangkan soft skill dan pengalaman organisasi siswa.',
            ],
            [
                'name' => 'OSIS / MPK',
                'coach_id' => null,
                'description' => 'Organisasi siswa dan majelis perwakilan kelas untuk menjalankan kepemimpinan, aspirasi, dan program sekolah.',
                'requirements' => 'Memiliki minat organisasi, kepemimpinan, dan tanggung jawab yang baik.',
                'schedule_overview' => 'Rapat dan kegiatan menyesuaikan program kerja OSIS/MPK.',
                'achievements_overview' => 'Mendorong lahirnya kepemimpinan dan kolaborasi siswa yang kuat.',
            ],
            [
                'name' => 'PA/PI Duta',
                'coach_id' => null,
                'description' => 'Pembinaan siswa untuk peran duta sekolah dalam kegiatan promosi, representasi, dan pelayanan acara resmi.',
                'requirements' => 'Percaya diri, komunikatif, dan mampu merepresentasikan sekolah dengan baik.',
                'schedule_overview' => 'Latihan dan briefing dilakukan menjelang agenda representasi sekolah.',
                'achievements_overview' => 'Menyiapkan duta siswa untuk mendukung citra dan kegiatan sekolah.',
            ],
            [
                'name' => 'Fortina',
                'coach_id' => null,
                'description' => 'Ekstrakurikuler pengembangan bakat dan kerja tim siswa dalam program pembinaan sekolah.',
                'requirements' => 'Aktif, disiplin, dan siap berpartisipasi dalam kegiatan kelompok.',
                'schedule_overview' => 'Jadwal kegiatan disusun sesuai agenda pembina dan pengurus.',
                'achievements_overview' => 'Mendorong keterlibatan siswa dalam kegiatan positif sekolah.',
            ],
            [
                'name' => 'Konten Kreator',
                'coach_id' => null,
                'description' => 'Wadah bagi siswa untuk belajar produksi konten digital, dokumentasi, desain, dan publikasi kreatif.',
                'requirements' => 'Memiliki minat pada foto, video, desain, atau media sosial sekolah.',
                'schedule_overview' => 'Produksi konten mengikuti agenda sekolah dan jadwal tim kreatif.',
                'achievements_overview' => 'Mendukung publikasi kegiatan sekolah melalui karya digital siswa.',
            ],
        ])->each(function (array $extracurricular): void {
            Extracurricular::updateOrCreate(
                ['name' => $extracurricular['name']],
                $extracurricular + ['is_active' => true],
            );
        });
    }
}
