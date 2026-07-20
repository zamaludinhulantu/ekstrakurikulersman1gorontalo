<?php

namespace Database\Seeders;

use App\Models\Coach;
use App\Models\Extracurricular;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CoachActivityAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $coachAssignments = [
            [
                'name' => 'Sry Yulanda Mile, M.Pd',
                'activities' => ['OSN - Matematika'],
                'expertise' => 'Pembinaan OSN Matematika',
            ],
            [
                'name' => 'Linda Suronoto, S.Pd',
                'activities' => ['OSN - Fisika', 'OPSI'],
                'expertise' => 'Pembinaan sains dan riset',
            ],
            [
                'name' => 'Siti Nurhayati, S.Pd',
                'activities' => ['OSN - Biologi'],
                'expertise' => 'Pembinaan OSN Biologi',
            ],
            [
                'name' => 'Yuni Prasetiani, S.Pd',
                'activities' => ['OSN - Kimia'],
                'expertise' => 'Pembinaan OSN Kimia',
            ],
            [
                'name' => 'Siti Chayaninggrum, S.Pd',
                'activities' => ['OSN - Ekonomi'],
                'expertise' => 'Pembinaan OSN Ekonomi',
            ],
            [
                'name' => 'Miniarty Hunou, S.Pd',
                'activities' => ['OSN - Geografi'],
                'expertise' => 'Pembinaan OSN Geografi',
            ],
            [
                'name' => 'Widya Nur, S.Pd',
                'activities' => ['OSN - Kebumian'],
                'expertise' => 'Pembinaan OSN Kebumian',
            ],
            [
                'name' => 'Dewi Sinta Ismail, M.Pd',
                'activities' => ['OSN - Astronomi'],
                'expertise' => 'Pembinaan OSN Astronomi',
            ],
            [
                'name' => 'Ramlah Parman, S.Pd',
                'activities' => ['OSN - Informatika', 'FLS3N - Komik Digital'],
                'expertise' => 'Pembinaan teknologi dan media kreatif',
            ],
            [
                'name' => 'Jefri Ibrahim, S.Pd',
                'activities' => ['FLS3N - Vokalia PA/PI', 'KEG. MUSIUM - Cipta Lagu Tentang Musium'],
                'expertise' => 'Pembinaan seni vokal dan musik',
            ],
            [
                'name' => 'Kurniati Ladjambu, M.Pd',
                'activities' => ['FLS3N - Baca Puisi'],
                'expertise' => 'Pembinaan sastra dan puisi',
            ],
            [
                'name' => 'Jein Pailati, M.Pd',
                'activities' => ['FLS3N - Cipta Puisi', 'Menulis Artikel / Essai'],
                'expertise' => 'Pembinaan literasi dan sastra',
            ],
            [
                'name' => 'Abdul Rahman Hiola, M.Pd',
                'activities' => ['FLS3N - Design Poster PA/PI', 'Konten Kreator', 'Fortina'],
                'expertise' => 'Pembinaan desain, media, dan publikasi',
            ],
            [
                'name' => 'Febriyanti Dumbu, S.Pd',
                'activities' => ['FLS3N - Monolog'],
                'expertise' => 'Pembinaan seni peran',
            ],
            [
                'name' => 'Febriyanti Abas, S.Pd',
                'activities' => ['FLS3N - Kriya PA/PI'],
                'expertise' => 'Pembinaan kriya dan karya seni',
            ],
            [
                'name' => 'Suryono Paris, S.Pd',
                'activities' => [
                    'FLS3N - Gitar Solo',
                    'FLS3N - Musik Tradisional',
                    'KEG. MUSIUM - Melukis',
                    'OSIS / MPK',
                ],
                'expertise' => 'Pembinaan seni musik, budaya, dan organisasi',
            ],
            [
                'name' => 'Sri Novanti Mukdin, S.Pd',
                'activities' => ['FLS3N - Tari Kreasi'],
                'expertise' => 'Pembinaan tari',
            ],
            [
                'name' => 'Rachmat Hamzah, S.Kom',
                'activities' => ['FLS3N - Film Pendek', 'O2SN - Silat'],
                'expertise' => 'Pembinaan media digital dan olahraga',
            ],
            [
                'name' => 'Firdaus Habie, M.Pd',
                'activities' => ['FLS3N - Jurnalistik', 'SMAG'],
                'expertise' => 'Pembinaan jurnalistik dan organisasi',
            ],
            [
                'name' => 'Lian Puluhulawa, S.Pd',
                'activities' => ['FLS3N - Menulis Cerpen', 'DEBAT - Bahasa Indonesia'],
                'expertise' => 'Pembinaan literasi dan debat',
            ],
            [
                'name' => 'Cahyani K. Hasan, S.Pd',
                'activities' => ['DEBAT - Bahasa Inggris'],
                'expertise' => 'Pembinaan debat bahasa Inggris',
            ],
            [
                'name' => 'As Jusy, S.Pd',
                'activities' => ['DEBAT - Hukum'],
                'expertise' => 'Pembinaan debat hukum',
            ],
            [
                'name' => 'Desy Arisandy Katili, S.Pd',
                'activities' => ['DEBAT - Ekonomi'],
                'expertise' => 'Pembinaan debat ekonomi',
            ],
            [
                'name' => 'Taufik Gubali, M.Pd',
                'activities' => ['O2SN - Karate & Taekwondo', 'PBB / Paskib'],
                'expertise' => 'Pembinaan kedisiplinan dan olahraga bela diri',
            ],
            [
                'name' => 'Saprin Isima, S.Pd',
                'activities' => ['O2SN - Renang'],
                'expertise' => 'Pembinaan renang',
            ],
            [
                'name' => 'Ashari Bahsowan, S.Pd',
                'activities' => ['O2SN - Badminton', 'O2SN - Takraw', 'PKS'],
                'expertise' => 'Pembinaan olahraga dan kedisiplinan',
            ],
            [
                'name' => 'Rahamat A. R. Musa, S.Pd',
                'activities' => ['O2SN - Atletik'],
                'expertise' => 'Pembinaan atletik',
            ],
            [
                'name' => 'Dadang Lakoro, S.Pd',
                'activities' => ['O2SN - Panjat Tebing', 'O2SN - Futsal / Sepak Bola'],
                'expertise' => 'Pembinaan olahraga kompetitif',
            ],
            [
                'name' => 'Muhlis Frio Abas, S.Pd',
                'activities' => ['O2SN - Tenis Meja', 'O2SN - Volly Ball'],
                'expertise' => 'Pembinaan permainan bola dan meja',
            ],
            [
                'name' => 'Maimuna Abas, S.Pd',
                'activities' => ['KEG. MUSIUM - Tutur Sejarah'],
                'expertise' => 'Pembinaan sejarah dan budaya',
            ],
            [
                'name' => 'Yeni Kairupan, S.Pd',
                'activities' => ['KEG. MUSIUM - Tarian Tidi'],
                'expertise' => 'Pembinaan seni budaya',
            ],
            [
                'name' => 'Vera Siska Auladi, S.Pd',
                'activities' => ['KEG. MUSIUM - Paiya Lo Hungulo Poli'],
                'expertise' => 'Pembinaan budaya daerah',
            ],
            [
                'name' => 'Serfila, S.Pd',
                'activities' => ["Tilawatil Qur'an"],
                'expertise' => 'Pembinaan tilawah',
            ],
            [
                'name' => 'Nangsi Pakaya, S.Pd',
                'activities' => ["Tartil dan Hifzil Qur'an"],
                'expertise' => 'Pembinaan tartil dan hafalan',
            ],
            [
                'name' => 'Rahim Penia, S.Pd',
                'activities' => ['Rohis'],
                'expertise' => 'Pembinaan kerohanian',
            ],
            [
                'name' => 'Dr. Jusuf Ginting, M.Pd',
                'activities' => ['Pelsis'],
                'expertise' => 'Pembinaan kepemimpinan siswa',
            ],
            [
                'name' => 'Hastin L. Asi, S.Pd',
                'activities' => ['Pramuka'],
                'expertise' => 'Pembinaan kepramukaan',
            ],
            [
                'name' => 'Trimastanti Husain, M.Pd',
                'activities' => ['PIK KR', 'PA/PI DUT'],
                'expertise' => 'Pembinaan remaja dan representasi sekolah',
            ],
            [
                'name' => 'Heningsih, S.Kom',
                'activities' => ['PMR'],
                'expertise' => 'Pembinaan kesehatan dan kepedulian sosial',
            ],
        ];

        foreach ($coachAssignments as $index => $assignment) {
            $coach = $this->firstOrCreateCoach($assignment, $index + 1);

            foreach ($assignment['activities'] as $activityName) {
                $extracurricular = Extracurricular::query()->where('name', $activityName)->first();

                if (! $extracurricular) {
                    continue;
                }

                $extracurricular->coaches()->syncWithoutDetaching([$coach->id]);

                if (! $extracurricular->coach_id) {
                    $extracurricular->update(['coach_id' => $coach->id]);
                }
            }
        }
    }

    private function firstOrCreateCoach(array $assignment, int $sequence): Coach
    {
        $email = $this->placeholderEmail($assignment['name']);
        $nip = sprintf('AUTO-COACH-%03d', $sequence);

        $user = User::query()->firstOrNew(['email' => $email]);
        $user->name = $assignment['name'];
        $user->role = User::ROLE_COACH;
        $user->phone = $user->phone ?: null;
        $user->address = $user->address ?: 'SMAN 1 Gorontalo';
        $user->is_active = false;

        if (! $user->exists) {
            $user->password = Str::random(24);
        }

        $user->save();

        $coach = Coach::query()
            ->where('user_id', $user->id)
            ->orWhere('nip', $nip)
            ->first();

        if (! $coach) {
            $coach = new Coach();
            $coach->user_id = $user->id;
            $coach->nip = $nip;
        }

        $coach->expertise = $assignment['expertise'] ?? null;
        $coach->bio = 'Data pembina diinput dari daftar kegiatan sekolah dan dapat dilengkapi admin kemudian.';
        $coach->save();

        return $coach;
    }

    private function placeholderEmail(string $name): string
    {
        $slug = Str::slug(str($name)->before(',')->toString(), '.');

        return 'coach.'.$slug.'@sman1gorontalo.local';
    }
}
