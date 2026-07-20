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
            'rohis' => asset('images/extracurriculars/rohis.jpg'),
            "tilawatil qur'an" => asset('images/extracurriculars/quran-student.jpg'),
            "tartil dan hifzil qur'an" => asset('images/extracurriculars/quran-student.jpg'),
        ];

        if (array_key_exists($normalized, $imageMap)) {
            return $imageMap[$normalized];
        }

        return Extracurricular::makePreviewImage($name);
    }
}
