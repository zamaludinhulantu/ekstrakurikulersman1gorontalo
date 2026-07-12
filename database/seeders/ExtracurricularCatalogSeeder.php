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
            ],
            [
                'name' => "Tartil dan Hifzil Qur'an",
                'coach_id' => null,
                'description' => "Program pembinaan tartil dan hafalan Al-Qur'an untuk meningkatkan kemampuan membaca dan menghafal siswa.",
                'requirements' => 'Memiliki komitmen mengikuti setoran hafalan dan pembinaan tartil.',
                'schedule_overview' => 'Jadwal pembinaan disesuaikan dengan agenda keagamaan sekolah.',
            ],
            [
                'name' => 'OPSI',
                'coach_id' => null,
                'description' => 'Ekstrakurikuler riset dan karya ilmiah untuk mengembangkan budaya penelitian dan inovasi siswa.',
                'requirements' => 'Tertarik pada penelitian, observasi, dan presentasi ilmiah.',
                'schedule_overview' => 'Pertemuan riset diatur sesuai proyek dan pembimbing.',
            ],
            [
                'name' => 'Menulis Artikel',
                'coach_id' => null,
                'description' => 'Wadah pengembangan literasi, opini, dan penulisan artikel populer maupun informatif oleh siswa.',
                'requirements' => 'Menyukai membaca, menulis, dan siap mengikuti proses editorial.',
                'schedule_overview' => 'Sesi penulisan dan review artikel dilakukan berkala.',
            ],
            [
                'name' => 'Pelsis',
                'coach_id' => null,
                'description' => 'Kegiatan organisasi siswa untuk membangun kolaborasi, kepemimpinan, dan pelayanan sekolah.',
                'requirements' => 'Aktif, disiplin, dan siap terlibat dalam program organisasi sekolah.',
                'schedule_overview' => 'Rapat dan program kerja dilaksanakan sesuai agenda kepengurusan.',
            ],
            [
                'name' => 'Rohis',
                'coach_id' => null,
                'description' => 'Kegiatan pembinaan karakter, kajian rutin, dan pengembangan kepemimpinan siswa berbasis nilai keislaman.',
                'requirements' => 'Aktif mengikuti pembinaan dan kegiatan kerohanian sekolah.',
                'schedule_overview' => 'Pertemuan rutin mengikuti agenda pembinaan Rohis.',
            ],
            [
                'name' => 'PBB/Paskib',
                'coach_id' => $paskibCoachId,
                'description' => 'Pelatihan peraturan baris-berbaris, kepemimpinan, disiplin, dan kesiapan petugas upacara sekolah.',
                'requirements' => 'Memiliki kedisiplinan tinggi, fisik prima, dan siap mengikuti latihan rutin.',
                'schedule_overview' => 'Latihan dilakukan berkala sesuai agenda upacara dan lomba.',
            ],
            [
                'name' => 'PKS',
                'coach_id' => null,
                'description' => 'Patroli Keamanan Sekolah untuk membina kedisiplinan, ketertiban, dan kepedulian siswa terhadap lingkungan sekolah.',
                'requirements' => 'Disiplin, bertanggung jawab, dan siap menjaga ketertiban lingkungan sekolah.',
                'schedule_overview' => 'Kegiatan dilaksanakan sesuai jadwal piket dan pembinaan.',
            ],
            [
                'name' => 'SMAG',
                'coach_id' => null,
                'description' => 'Kegiatan pengembangan karakter dan kreativitas siswa dalam berbagai program sekolah unggulan.',
                'requirements' => 'Siap berkolaborasi dalam kegiatan sekolah dan pengembangan diri.',
                'schedule_overview' => 'Pertemuan mengikuti program kerja dan agenda pembinaan.',
            ],
            [
                'name' => 'RELS',
                'coach_id' => null,
                'description' => 'Wadah pembinaan kepemimpinan, pelayanan, dan tanggung jawab sosial siswa.',
                'requirements' => 'Memiliki sikap disiplin, komunikatif, dan siap bekerja dalam tim.',
                'schedule_overview' => 'Agenda kegiatan dilaksanakan sesuai program organisasi.',
            ],
            [
                'name' => 'OSIS / MPK',
                'coach_id' => null,
                'description' => 'Organisasi siswa dan majelis perwakilan kelas untuk menjalankan kepemimpinan, aspirasi, dan program sekolah.',
                'requirements' => 'Memiliki minat organisasi, kepemimpinan, dan tanggung jawab yang baik.',
                'schedule_overview' => 'Rapat dan kegiatan menyesuaikan program kerja OSIS/MPK.',
            ],
            [
                'name' => 'PA/PI Duta',
                'coach_id' => null,
                'description' => 'Pembinaan siswa untuk peran duta sekolah dalam kegiatan promosi, representasi, dan pelayanan acara resmi.',
                'requirements' => 'Percaya diri, komunikatif, dan mampu merepresentasikan sekolah dengan baik.',
                'schedule_overview' => 'Latihan dan briefing dilakukan menjelang agenda representasi sekolah.',
            ],
            [
                'name' => 'Fortina',
                'coach_id' => null,
                'description' => 'Ekstrakurikuler pengembangan bakat dan kerja tim siswa dalam program pembinaan sekolah.',
                'requirements' => 'Aktif, disiplin, dan siap berpartisipasi dalam kegiatan kelompok.',
                'schedule_overview' => 'Jadwal kegiatan disusun sesuai agenda pembina dan pengurus.',
            ],
            [
                'name' => 'Konten Kreator',
                'coach_id' => null,
                'description' => 'Wadah bagi siswa untuk belajar produksi konten digital, dokumentasi, desain, dan publikasi kreatif.',
                'requirements' => 'Memiliki minat pada foto, video, desain, atau media sosial sekolah.',
                'schedule_overview' => 'Produksi konten mengikuti agenda sekolah dan jadwal tim kreatif.',
            ],
        ])->each(function (array $extracurricular): void {
            Extracurricular::updateOrCreate(
                ['name' => $extracurricular['name']],
                $extracurricular + ['is_active' => true],
            );
        });
    }
}
