<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ExtracurricularController extends Controller
{
    public function index(Request $request): View
    {
        $allowedCategories = array_keys(Extracurricular::categoryDefinitions());
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:all,active,registered,unregistered'],
        ]);

        $search = trim((string) ($filters['search'] ?? ''));
        $requestedCategory = (string) ($filters['category'] ?? 'semua');
        $category = in_array($requestedCategory, $allowedCategories, true) ? $requestedCategory : 'semua';
        $status = $filters['status'] ?? 'all';
        $student = auth()->user()->student;

        $registrations = $student
            ? Registration::where('student_id', $student->id)->pluck('status', 'extracurricular_id')
            : collect();

        $registeredIds = $registrations->keys()->all();

        $extracurriculars = Extracurricular::with(['coach.user', 'coaches.user'])
            ->withCount([
                'registrations as participants_count' => fn ($query) => $query->where('status', Registration::STATUS_APPROVED),
            ])
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search): void {
                $searchValue = $search;
                $query->where(function ($subQuery) use ($searchValue): void {
                    $subQuery->where('name', 'like', "%{$searchValue}%")
                        ->orWhere('description', 'like', "%{$searchValue}%");
                });
            })
            ->when($category !== 'semua', function ($query) use ($category): void {
                $ids = Extracurricular::query()
                    ->get(['id', 'name', 'type'])
                    ->filter(fn (Extracurricular $item) => $item->category_key === $category)
                    ->pluck('id')
                    ->all();

                if ($ids === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->whereIn('id', $ids);
            })
            ->when($status === 'registered', function ($query) use ($registeredIds): void {
                if ($registeredIds === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->whereIn('id', $registeredIds);
            })
            ->when($status === 'unregistered', function ($query) use ($registeredIds): void {
                if ($registeredIds !== []) {
                    $query->whereNotIn('id', $registeredIds);
                }
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $extracurriculars->setCollection($this->decorateExtracurriculars($extracurriculars->getCollection()));

        return view('student.extracurriculars.index', [
            'extracurriculars' => $extracurriculars,
            'search' => $search,
            'category' => $category,
            'status' => $status,
            'registrationStatuses' => $registrations,
            'filterCategories' => collect(Extracurricular::categoryDefinitions())
                ->map(fn (array $definition) => ['key' => $definition['key'], 'label' => $definition['label']])
                ->prepend(['key' => 'semua', 'label' => 'Semua'])
                ->values(),
        ]);
    }

    public function show(Extracurricular $extracurricular): View
    {
        $student = auth()->user()->student;
        $registration = null;

        if ($student) {
            $registration = Registration::where('student_id', $student->id)
                ->where('extracurricular_id', $extracurricular->id)
                ->first();
        }

        $extracurricular->loadCount([
            'registrations as participants_count' => fn ($query) => $query->where('status', Registration::STATUS_APPROVED),
        ])->load([
            'coach.user',
            'coaches.user',
            'schedules' => fn ($query) => $query->orderByDesc('activity_date')->limit(8),
            'achievements' => fn ($query) => $query->limit(5),
        ]);

        $extracurricular = $this->decorateExtracurricular($extracurricular);

        return view('student.extracurriculars.show', [
            'extracurricular' => $extracurricular,
            'registration' => $registration,
        ]);
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
