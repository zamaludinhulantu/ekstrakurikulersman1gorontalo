<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Coach;
use App\Models\Extracurricular;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicLandingController extends Controller
{
    public function index(): View
    {
        $extracurriculars = Extracurricular::with([
            'coach.user',
            'coaches.user',
            'schedules' => fn ($query) => $query->orderBy('activity_date')->orderBy('start_time'),
        ])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $usesDummyExtracurriculars = $extracurriculars->isEmpty();

        if ($usesDummyExtracurriculars) {
            $extracurriculars = $this->dummyExtracurriculars();
        }

        $extracurriculars = $this->decorateExtracurriculars($extracurriculars);

        return view('public.landing', [
            'extracurriculars' => $extracurriculars,
            'featuredExtracurricular' => $extracurriculars->first(),
            'usesDummyExtracurriculars' => $usesDummyExtracurriculars,
        ]);
    }

    public function show(Extracurricular $extracurricular): View
    {
        abort_unless($extracurricular->is_active, 404);

        $extracurricular->load([
            'coach.user',
            'coaches.user',
            'schedules' => fn ($query) => $query->orderBy('activity_date')->orderBy('start_time'),
            'achievements' => fn ($query) => $query->limit(6),
        ]);

        $extracurricular = $this->decorateExtracurricular($extracurricular);

        return view('public.extracurricular-detail', [
            'extracurricular' => $extracurricular,
        ]);
    }

    public function information(): View
    {
        return view('public.information');
    }

    public function announcements(): View
    {
        $announcements = Announcement::with(['publisher', 'extracurricular'])
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('public.announcements', [
            'announcements' => $announcements,
        ]);
    }

    public function beginRegistration(Request $request, Extracurricular $extracurricular): RedirectResponse
    {
        abort_unless($extracurricular->is_active, 404);

        if (auth()->check()) {
            if (auth()->user()->hasRole(User::ROLE_STUDENT)) {
                return redirect()->route('student.extracurriculars.show', $extracurricular);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Pendaftaran ekstrakurikuler hanya dapat dilakukan menggunakan akun siswa.');
        }

        $request->session()->put('pending_extracurricular_id', $extracurricular->id);

        return redirect()->route('login')
            ->with('info', 'Login sebagai siswa untuk melanjutkan pendaftaran ekstrakurikuler '.$extracurricular->name.'.');
    }

    private function dummyExtracurriculars(): Collection
    {
        return collect([
            [
                'name' => 'Basket',
                'description' => 'Latihan teknik dasar, strategi tim, dan pembinaan fisik untuk kompetisi antarsekolah.',
                'requirements' => 'Memiliki semangat latihan rutin dan menjaga sportivitas.',
                'schedule_overview' => 'Setiap Selasa dan Kamis, pukul 15.30 - 17.30.',
                'achievements_overview' => 'Finalis turnamen basket pelajar tingkat kota.',
                'coach_name' => 'Pembina Basket',
                'schedule_title' => 'Latihan Passing dan Shooting',
                'schedule_date' => now()->addDays(2)->toDateString(),
                'schedule_start' => '15:30:00',
                'schedule_end' => '17:30:00',
                'schedule_location' => 'Lapangan Basket Sekolah',
            ],
            [
                'name' => 'Futsal',
                'description' => 'Pembinaan teknik bermain futsal, kerja sama tim, dan persiapan turnamen sekolah.',
                'requirements' => 'Sehat jasmani dan siap mengikuti seleksi dasar.',
                'schedule_overview' => 'Setiap Senin dan Jumat, pukul 16.00 - 17.30.',
                'achievements_overview' => 'Juara 3 Liga Futsal Pelajar wilayah kota.',
                'coach_name' => 'Pembina Futsal',
                'schedule_title' => 'Latihan Formasi dan Finishing',
                'schedule_date' => now()->addDays(3)->toDateString(),
                'schedule_start' => '16:00:00',
                'schedule_end' => '17:30:00',
                'schedule_location' => 'Lapangan Serbaguna',
            ],
            [
                'name' => 'Rohis',
                'description' => 'Kegiatan pembinaan karakter, kajian rutin, dan pengembangan kepemimpinan siswa.',
                'requirements' => 'Aktif mengikuti pembinaan dan kegiatan keagamaan sekolah.',
                'schedule_overview' => 'Setiap Rabu, pukul 15.30 - 17.00.',
                'achievements_overview' => 'Program kajian rutin dan bakti sosial siswa.',
                'coach_name' => 'Pembina Rohis',
                'schedule_title' => 'Kajian dan Diskusi Pekanan',
                'schedule_date' => now()->addDays(5)->toDateString(),
                'schedule_start' => '15:30:00',
                'schedule_end' => '17:00:00',
                'schedule_location' => 'Mushola Sekolah',
            ],
        ])->map(function (array $item): Extracurricular {
            $coach = new Coach;
            $coach->setRelation('user', new User([
                'name' => $item['coach_name'],
            ]));

            $schedule = new Schedule([
                'title' => $item['schedule_title'],
                'activity_date' => $item['schedule_date'],
                'start_time' => $item['schedule_start'],
                'end_time' => $item['schedule_end'],
                'location' => $item['schedule_location'],
            ]);

            $extracurricular = new Extracurricular([
                'name' => $item['name'],
                'description' => $item['description'],
                'requirements' => $item['requirements'],
                'schedule_overview' => $item['schedule_overview'],
                'achievements_overview' => $item['achievements_overview'],
                'is_active' => true,
            ]);

            $extracurricular->setRelation('coach', $coach);
            $extracurricular->setRelation('coaches', collect([$coach]));
            $extracurricular->setRelation('schedules', collect([$schedule]));

            return $extracurricular;
        });
    }

    private function decorateExtracurriculars(Collection $extracurriculars): Collection
    {
        return $extracurriculars->map(
            fn (Extracurricular $extracurricular): Extracurricular => $this->decorateExtracurricular($extracurricular)
        );
    }

    private function decorateExtracurricular(Extracurricular $extracurricular): Extracurricular
    {
        $extracurricular->setAttribute('preview_image', $this->resolvePreviewImage($extracurricular));

        return $extracurricular;
    }

    private function resolvePreviewImage(Extracurricular $extracurricular): string
    {
        if ($extracurricular->image_path) {
            return asset($extracurricular->image_path);
        }

        $name = $extracurricular->name;
        $normalized = Str::of($name)->lower()->trim()->toString();

        $imageMap = [
            'pramuka' => asset('images/extracurriculars/pramuka.jpg'),
            'paskibra' => asset('images/extracurriculars/paskibra.webp'),
            'pmr' => asset('images/extracurriculars/pmr.jpg'),
            'basket' => asset('images/extracurriculars/basket.jpg'),
            'basketball' => asset('images/extracurriculars/basket.jpg'),
            'futsal' => asset('images/extracurriculars/futsal.jpg'),
            'rohis' => asset('images/extracurriculars/rohis.jpg'),
            "tilawatil qur'an" => asset('images/extracurriculars/quran-student.jpg'),
            "tartil dan hifzil qur'an" => asset('images/extracurriculars/quran-student.jpg'),
            'opsi' => asset('images/extracurriculars/student-discussion.jpg'),
            'menulis artikel' => asset('images/extracurriculars/student-discussion.jpg'),
            'pelsis' => asset('images/extracurriculars/student-discussion.jpg'),
            'smag' => asset('images/extracurriculars/student-discussion.jpg'),
            'rels' => asset('images/extracurriculars/student-discussion.jpg'),
            'osis / mpk' => asset('images/extracurriculars/student-discussion.jpg'),
            'pa/pi duta' => asset('images/extracurriculars/student-discussion.jpg'),
            'fortina' => asset('images/extracurriculars/student-discussion.jpg'),
            'konten kreator' => asset('images/extracurriculars/student-camera.jpg'),
            'pbb/paskib' => asset('images/extracurriculars/student-parade.jpg'),
            'pks' => asset('images/extracurriculars/student-parade.jpg'),
        ];

        if (array_key_exists($normalized, $imageMap)) {
            return $imageMap[$normalized];
        }

        return $this->makePreviewImage($name);
    }

    private function makePreviewImage(string $name): string
    {
        $palette = collect([
            ['#0f766e', '#14b8a6', '#99f6e4'],
            ['#1d4ed8', '#38bdf8', '#dbeafe'],
            ['#7c3aed', '#c084fc', '#f3e8ff'],
            ['#be123c', '#fb7185', '#ffe4e6'],
            ['#b45309', '#f59e0b', '#fef3c7'],
        ]);

        $colors = $palette[abs(crc32($name)) % $palette->count()];
        $initial = Str::upper(Str::substr($name, 0, 1));
        $label = e(Str::upper($name));

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 520">
    <defs>
        <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="{$colors[0]}"/>
            <stop offset="55%" stop-color="{$colors[1]}"/>
            <stop offset="100%" stop-color="{$colors[2]}"/>
        </linearGradient>
    </defs>
    <rect width="800" height="520" rx="36" fill="url(#bg)"/>
    <circle cx="664" cy="96" r="96" fill="rgba(255,255,255,0.18)"/>
    <circle cx="110" cy="440" r="120" fill="rgba(255,255,255,0.14)"/>
    <circle cx="610" cy="370" r="150" fill="rgba(15,23,42,0.12)"/>
    <text x="76" y="156" font-family="Segoe UI, Arial, sans-serif" font-size="148" font-weight="800" fill="rgba(255,255,255,0.92)">{$initial}</text>
    <text x="78" y="446" font-family="Segoe UI, Arial, sans-serif" font-size="48" font-weight="700" fill="#ffffff">{$label}</text>
</svg>
SVG;

        return 'data:image/svg+xml;utf8,'.rawurlencode($svg);
    }
}
