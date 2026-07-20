<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicLandingController extends Controller
{
    public function index(): View
    {
        $activityCounts = Extracurricular::activeCategoryCounts();
        $categorySummaries = $this->baseCategorySummaries($activityCounts);

        return view('public.landing', [
            'categorySummaries' => $this->decorateCategorySummaries($categorySummaries),
            'statistics' => [
                'totalActivities' => $activityCounts['total'],
                'openActivities' => $activityCounts['total'],
                'categories' => $categorySummaries->count(),
                'onlineRegistration' => '24/7',
            ],
            'recentAnnouncements' => Announcement::with(['publisher', 'extracurricular'])
                ->visibleToStudents()
                ->latest()
                ->limit(3)
                ->get(),
        ]);
    }

    public function activities(): View
    {
        $categorySummaries = $this->decorateCategorySummaries(
            $this->baseCategorySummaries(Extracurricular::activeCategoryCounts())
        );

        return view('public.activities-categories', [
            'categorySummaries' => $categorySummaries,
            'totalActivities' => $categorySummaries->sum('count'),
        ]);
    }

    public function catalog(Request $request): View
    {
        return $this->renderCatalogPage($request, null, includeCategoryFilter: true);
    }

    public function categoryCatalog(Request $request, string $slug): View
    {
        $category = $this->categoryBySlug($slug);
        abort_unless($category !== null, 404);

        return $this->renderCatalogPage($request, $category, includeCategoryFilter: false);
    }

    public function show(Extracurricular $extracurricular): View
    {
        $extracurricular->load([
            'coach.user',
            'coaches.user',
            'schedules' => fn ($query) => $query->orderBy('activity_date')->orderBy('start_time'),
            'achievements' => fn ($query) => $query->limit(6),
        ]);

        $extracurricular = $this->decorateExtracurricular($extracurricular);

        return view('public.extracurricular-detail', [
            'extracurricular' => $extracurricular,
            'relatedAnnouncements' => Announcement::with('publisher')
                ->visibleToStudents()
                ->where('extracurricular_id', $extracurricular->id)
                ->latest()
                ->limit(3)
                ->get(),
            'backToActivitiesUrl' => $this->backToActivityUrl($extracurricular),
        ]);
    }

    public function information(): View
    {
        return view('public.information');
    }

    public function announcements(): View
    {
        $announcements = Announcement::with(['publisher', 'extracurricular'])
            ->visibleToStudents()
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
                return redirect()->route('student.extracurriculars.register', $extracurricular);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Pendaftaran kegiatan hanya dapat dilakukan menggunakan akun siswa.');
        }

        abort_unless($extracurricular->is_active, 404);

        $request->session()->put('pending_extracurricular_id', $extracurricular->id);

        return redirect()->route('login')
            ->with('info', 'Login sebagai siswa untuk melanjutkan pendaftaran kegiatan '.$extracurricular->name.'.');
    }

    private function renderCatalogPage(Request $request, ?array $fixedCategory, bool $includeCategoryFilter): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:all,open,closed'],
            'sort' => ['nullable', 'in:relevant,name,latest,open'],
            'category' => ['nullable', 'string', 'max:120'],
        ]);

        $search = trim((string) ($filters['search'] ?? ''));
        $availableCategoryKeys = collect($this->baseCategorySummaries(Extracurricular::activeCategoryCounts()))->pluck('key')->all();
        $requestedCategory = (string) ($filters['category'] ?? 'all');
        $category = $fixedCategory['key'] ?? (in_array($requestedCategory, $availableCategoryKeys, true) ? $requestedCategory : 'all');
        $status = $filters['status'] ?? 'all';
        $sort = $filters['sort'] ?? 'relevant';

        $query = $this->catalogQuery(activeOnly: false)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($category !== 'all', function ($query) use ($category): void {
                $this->applyCategoryFilter($query, $category);
            })
            ->when($status !== 'all', function ($query) use ($status): void {
                $query->where('is_active', $status === 'open');
            });

        $this->applyCatalogSort($query, $sort, $search, $category);

        $extracurriculars = $query
            ->paginate(12)
            ->withQueryString();

        $collection = $this->decorateExtracurriculars($extracurriculars->getCollection());

        $user = $request->user();
        if ($user?->hasRole(User::ROLE_STUDENT) && $user->student) {
            $registrations = Registration::query()
                ->where('student_id', $user->student->id)
                ->whereIn('extracurricular_id', $collection->pluck('id'))
                ->latest('registration_date')
                ->latest('id')
                ->get()
                ->keyBy('extracurricular_id');

            $collection = $collection->map(function (Extracurricular $activity) use ($registrations): Extracurricular {
                $activity->setAttribute('current_registration', $registrations->get($activity->id));

                return $activity;
            });
        }

        $extracurriculars->setCollection($collection);

        return view('public.catalog', [
            'extracurriculars' => $extracurriculars,
            'search' => $search,
            'category' => $category,
            'status' => $status,
            'sort' => $sort,
            'fixedCategory' => $fixedCategory,
            'includeCategoryFilter' => $includeCategoryFilter,
            'categorySummaries' => $this->decorateCategorySummaries($this->baseCategorySummaries(Extracurricular::activeCategoryCounts())),
        ]);
    }

    private function decorateExtracurriculars(Collection $extracurriculars): Collection
    {
        return $extracurriculars->map(
            fn (Extracurricular $extracurricular): Extracurricular => $this->decorateExtracurricular($extracurricular)
        );
    }

    private function decorateCategorySummaries(Collection $summaries): Collection
    {
        return $summaries->map(function (array $summary): array {
            $summary['slug'] = $summary['slug'] ?? str((string) ($summary['label'] ?? ''))->slug()->toString();
            $summary['route'] = route('public.activities.category', $summary['slug']);

            return $summary;
        });
    }

    private function decorateExtracurricular(Extracurricular $extracurricular): Extracurricular
    {
        $extracurricular->setAttribute('preview_image', $this->resolvePreviewImage($extracurricular));

        return $extracurricular;
    }

    private function catalogQuery(bool $activeOnly)
    {
        return Extracurricular::with([
            'coach.user',
            'coaches.user',
            'schedules' => fn ($query) => $query->select('id', 'extracurricular_id', 'title', 'activity_date', 'start_time', 'location')
                ->orderBy('activity_date')
                ->orderBy('start_time')
                ->limit(1),
        ])
            ->withCount([
                'registrations as participants_count' => fn ($query) => $query->where('status', 'approved'),
            ])
            ->when($activeOnly, fn ($query) => $query->where('is_active', true))
            ->where(function ($query): void {
                $query->where('type', '!=', Extracurricular::TYPE_OLYMPIAD)
                    ->orWhereNull('branch_options');
            });
    }

    private function applyCategoryFilter($query, string $category): void
    {
        $ids = Extracurricular::idsForCategory($category);

        if ($ids === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('id', $ids);
    }

    private function applyCatalogSort($query, string $sort, string $search, string $category): void
    {
        match ($sort) {
            'name' => $query->orderBy('name'),
            'latest' => $query->orderByDesc('id'),
            'open' => $query->orderByDesc('is_active')->orderBy('name'),
            default => $search !== ''
                ? $query->orderByRaw(
                    "case when name = ? then 0 when name like ? then 1 when name like ? then 2 when description like ? then 3 else 4 end",
                    [$search, $search.'%', '%'.$search.'%', '%'.$search.'%']
                )->orderBy('name')
                : $query->orderByDesc('is_active')->orderBy('name'),
        };
    }

    private function baseCategorySummaries(array $activityCounts): Collection
    {
        return collect(Extracurricular::categoryDefinitions())
            ->map(function (array $definition) use ($activityCounts): array {
                return [
                    'label' => $definition['label'],
                    'key' => $definition['key'],
                    'slug' => $definition['slug'],
                    'description' => $definition['description'],
                    'catalogTitle' => $definition['catalog_title'],
                    'catalogSubtitle' => $definition['catalog_subtitle'],
                    'count' => $activityCounts[$definition['key']] ?? 0,
                    'icon' => $definition['icon'],
                    'image' => $definition['image'],
                    'tone' => $definition['tone'],
                ];
            })
            ->values();
    }

    private function categoryBySlug(string $slug): ?array
    {
        return $this->baseCategorySummaries(Extracurricular::activeCategoryCounts())
            ->firstWhere('slug', $slug);
    }

    private function backToActivityUrl(Extracurricular $extracurricular): string
    {
        return route('public.activities.category', $extracurricular->category_slug);
    }

    private function resolvePreviewImage(Extracurricular $extracurricular): string
    {
        if ($extracurricular->image_path) {
            return asset($extracurricular->image_path);
        }

        return Extracurricular::makePreviewImage($extracurricular->name);
    }
}
