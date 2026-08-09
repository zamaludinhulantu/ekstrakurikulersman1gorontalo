<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Article;
use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicLandingController extends Controller
{
    public function index(): View
    {
        $landingPayload = Cache::remember(
            'public.landing.payload.v2',
            now()->addMinutes(10),
            function (): array {
                $activityCounts = Extracurricular::activeCategoryCounts();
                $categorySummaries = $this->baseCategorySummaries($activityCounts);

                return [
                    'categorySummaries' => $this->decorateCategorySummaries($categorySummaries),
                    'statistics' => [
                        'totalActivities' => $activityCounts['total'],
                        'categories' => $categorySummaries->count(),
                    ],
                    'recentAnnouncements' => Announcement::query()
                        ->select(['id', 'title', 'content', 'priority', 'extracurricular_id', 'published_by', 'publish_at', 'created_at'])
                        ->with([
                            'publisher:id,name',
                            'extracurricular:id,name',
                        ])
                        ->visibleToStudents()
                        ->latest('publish_at')
                        ->latest('id')
                        ->limit(3)
                        ->get(),
                    'recentArticles' => Article::query()
                        ->select([
                            'id',
                            'title',
                            'slug',
                            'excerpt',
                            'content',
                            'content_category',
                            'extracurricular_id',
                            'published_by',
                            'image_path',
                            'image_alt_text',
                            'publish_at',
                            'created_at',
                            'is_featured',
                        ])
                        ->with([
                            'publisher:id,name',
                            'extracurricular:id,name',
                        ])
                        ->visibleToPublic()
                        ->orderByDesc('is_featured')
                        ->latest('publish_at')
                        ->limit(4)
                        ->get(),
                ];
            }
        );

        return view('public.landing', [
            'categorySummaries' => $landingPayload['categorySummaries'],
            'statistics' => $landingPayload['statistics'],
            'recentAnnouncements' => $landingPayload['recentAnnouncements'],
            'recentArticles' => $landingPayload['recentArticles'],
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
        $today = now()->toDateString();

        $extracurricular->load([
            'coach.user:id,name',
            'coaches.user:id,name',
            'schedules' => fn ($query) => $query
                ->select([
                    'id',
                    'extracurricular_id',
                    'schedule_type',
                    'title',
                    'activity_date',
                    'start_time',
                    'end_time',
                    'location',
                    'status',
                    'cancelled_at',
                ])
                ->orderByRaw('case when activity_date >= ? then 0 else 1 end', [$today])
                ->orderBy('activity_date')
                ->orderBy('start_time'),
            'achievements' => fn ($query) => $query
                ->select(['id', 'extracurricular_id', 'title', 'description', 'achievement_date'])
                ->limit(6),
        ])->loadCount([
            'registrations as participants_count' => fn ($query) => $query->where('status', Registration::STATUS_APPROVED),
        ]);

        $user = request()->user();
        $currentRegistration = null;

        if ($user?->hasRole(User::ROLE_STUDENT) && $user->student) {
            $currentRegistration = Registration::query()
                ->with([
                    'talentTestResults:id,registration_id,status,published_at',
                    'talentTestParticipants:id,registration_id,schedule_id',
                    'talentTestParticipants.schedule:id,activity_date',
                ])
                ->where('student_id', $user->student->id)
                ->where('extracurricular_id', $extracurricular->id)
                ->latest('registration_date')
                ->latest('id')
                ->first();
        }

        $extracurricular = $this->decorateExtracurricular($extracurricular);

        return view('public.extracurricular-detail', [
            'extracurricular' => $extracurricular,
            'detailPage' => $this->buildDetailPageData($extracurricular, $currentRegistration, $user),
            'relatedAnnouncements' => Announcement::query()
                ->select(['id', 'title', 'content', 'published_by', 'publish_at', 'created_at'])
                ->with('publisher:id,name')
                ->visibleToStudents()
                ->where('extracurricular_id', $extracurricular->id)
                ->latest('publish_at')
                ->latest('id')
                ->limit(3)
                ->get(),
            'backToActivitiesUrl' => $this->backToActivityUrl($extracurricular),
        ]);
    }

    public function announcements(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'extracurricular_id' => ['nullable', 'integer', 'exists:extracurriculars,id'],
            'priority' => ['nullable', 'string', 'in:normal,important,urgent'],
        ]);
        $search = trim((string) ($filters['search'] ?? ''));
        $extracurricularId = isset($filters['extracurricular_id']) ? (int) $filters['extracurricular_id'] : null;
        $priority = (string) ($filters['priority'] ?? '');

        $announcements = Announcement::query()
            ->select(['id', 'title', 'content', 'priority', 'extracurricular_id', 'published_by', 'publish_at', 'created_at'])
            ->with(['publisher:id,name', 'extracurricular:id,name'])
            ->visibleToStudents()
            ->when($search, function ($query, string $value): void {
                $query->where(function ($searchQuery) use ($value): void {
                    $searchQuery->where('title', 'like', "%{$value}%")
                        ->orWhere('content', 'like', "%{$value}%");
                });
            })
            ->when($extracurricularId, fn ($query, int $id) => $query->where('extracurricular_id', $id))
            ->when($priority, fn ($query, string $value) => $query->where('priority', $value))
            ->latest('publish_at')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('public.announcements', [
            'announcements' => $announcements,
            'extracurriculars' => Extracurricular::query()
                ->where('is_active', true)
                ->whereHas('announcements', fn ($query) => $query->visibleToStudents())
                ->orderBy('name')
                ->get(['id', 'name']),
            'filters' => [
                'search' => $search,
                'extracurricular_id' => $extracurricularId,
                'priority' => $priority,
            ],
        ]);
    }

    public function articles(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'content_category' => ['nullable', 'string', 'max:50'],
            'extracurricular_id' => ['nullable', 'integer'],
            'published_from' => ['nullable', 'date'],
            'published_until' => ['nullable', 'date'],
        ]);

        $search = trim((string) ($filters['search'] ?? ''));
        $contentCategory = (string) ($filters['content_category'] ?? '');
        $extracurricularId = isset($filters['extracurricular_id']) ? (int) $filters['extracurricular_id'] : null;
        $publishedFrom = $filters['published_from'] ?? '';
        $publishedUntil = $filters['published_until'] ?? '';

        if ($contentCategory !== '' && ! array_key_exists($contentCategory, Article::contentCategories())) {
            $contentCategory = '';
        }

        $baseQuery = Article::query()
            ->select([
                'id',
                'title',
                'slug',
                'excerpt',
                'content',
                'content_category',
                'extracurricular_id',
                'published_by',
                'image_path',
                'image_alt_text',
                'publish_at',
                'created_at',
                'is_featured',
            ])
            ->with(['publisher:id,name', 'extracurricular:id,name'])
            ->visibleToPublic()
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when($contentCategory !== '', fn ($query) => $query->where('content_category', $contentCategory))
            ->when($extracurricularId, fn ($query) => $query->where('extracurricular_id', $extracurricularId))
            ->when($publishedFrom !== '', fn ($query) => $query->whereDate('publish_at', '>=', $publishedFrom))
            ->when($publishedUntil !== '', fn ($query) => $query->whereDate('publish_at', '<=', $publishedUntil));

        $articles = (clone $baseQuery)
            ->orderByDesc('is_featured')
            ->latest('publish_at')
            ->paginate(9)
            ->withQueryString();

        return view('public.articles', [
            'articles' => $articles,
            'search' => $search,
            'contentCategory' => $contentCategory,
            'extracurricularId' => $extracurricularId,
            'publishedFrom' => $publishedFrom,
            'publishedUntil' => $publishedUntil,
            'contentCategories' => Article::contentCategories(),
            'extracurriculars' => Extracurricular::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function articleShow(string $slug): View
    {
        $article = Article::query()
            ->select([
                'id',
                'title',
                'slug',
                'excerpt',
                'content',
                'content_category',
                'extracurricular_id',
                'published_by',
                'image_path',
                'image_alt_text',
                'publish_at',
                'created_at',
            ])
            ->with(['publisher:id,name', 'extracurricular:id,name'])
            ->visibleToPublic()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('public.article-detail', [
            'article' => $article,
            'relatedArticles' => Article::query()
                ->select([
                    'id',
                    'title',
                    'slug',
                    'excerpt',
                    'content',
                    'content_category',
                    'extracurricular_id',
                    'published_by',
                    'image_path',
                    'image_alt_text',
                    'publish_at',
                    'created_at',
                    'is_featured',
                ])
                ->with(['publisher:id,name', 'extracurricular:id,name'])
                ->visibleToPublic()
                ->whereKeyNot($article->id)
                ->when($article->content_category, fn ($query) => $query->where('content_category', $article->content_category))
                ->when($article->extracurricular_id, fn ($query) => $query->where('extracurricular_id', $article->extracurricular_id))
                ->orderByDesc('is_featured')
                ->latest('publish_at')
                ->limit(3)
                ->get(),
            'backToArticlesUrl' => route('public.articles.index'),
        ]);
    }

    public function sitemap(): Response
    {
        $staticPages = collect([
            [
                'loc' => route('landing'),
                'lastmod' => now()->toDateString(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => route('public.activities.index'),
                'lastmod' => now()->toDateString(),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
            [
                'loc' => route('public.activities.all'),
                'lastmod' => now()->toDateString(),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
            [
                'loc' => route('public.announcements'),
                'lastmod' => Announcement::query()->visibleToStudents()->max('updated_at')?->toDateString() ?? now()->toDateString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
            [
                'loc' => route('public.articles.index'),
                'lastmod' => Article::query()->visibleToPublic()->max('updated_at')?->toDateString() ?? now()->toDateString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
        ]);

        $categoryPages = collect($this->baseCategorySummaries(Extracurricular::activeCategoryCounts()))
            ->map(fn (array $category) => [
                'loc' => route('public.activities.category', $category['slug']),
                'lastmod' => now()->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ]);

        $activityPages = Extracurricular::query()
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->where('type', '!=', Extracurricular::TYPE_OLYMPIAD)
                    ->orWhereNull('branch_options');
            })
            ->orderBy('name')
            ->get(['id', 'updated_at'])
            ->map(fn (Extracurricular $activity) => [
                'loc' => route('public.extracurriculars.show', $activity),
                'lastmod' => optional($activity->updated_at)->toDateString() ?? now()->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ]);

        $articlePages = Article::query()
            ->visibleToPublic()
            ->orderByDesc('publish_at')
            ->get(['slug', 'updated_at'])
            ->map(fn (Article $article) => [
                'loc' => route('public.articles.show', $article->slug),
                'lastmod' => optional($article->updated_at)->toDateString() ?? now()->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ]);

        $pages = $staticPages
            ->concat($categoryPages)
            ->concat($activityPages)
            ->concat($articlePages)
            ->unique('loc')
            ->values();

        return response()
            ->view('public.sitemap', ['pages' => $pages], 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        return response(
            "User-agent: *\nAllow: /\nSitemap: ".route('public.sitemap')."\n",
            200,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
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
            $summary['display_label'] = $summary['key'] === Extracurricular::CATEGORY_MUSEUM
                ? 'Kegiatan Museum'
                : $summary['label'];
            $summary['image_url'] = $summary['image'] ?? null;
            $summary['has_image'] = filled($summary['image_url']);
            $summary['count_label'] = $summary['count'] === 1
                ? '1 kegiatan aktif'
                : $summary['count'].' kegiatan aktif';

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
        return Extracurricular::query()
            ->select([
                'id',
                'coach_id',
                'type',
                'name',
                'description',
                'requirements',
                'schedule_overview',
                'branch_options',
                'image_path',
                'is_active',
                'updated_at',
            ])
            ->with([
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
            $imageUrl = Extracurricular::assetUrl($extracurricular->image_path, $extracurricular->updated_at?->timestamp);

            if ($imageUrl) {
                return $imageUrl;
            }
        }

        return Extracurricular::makePreviewImage($extracurricular->name);
    }

    private function buildDetailPageData(Extracurricular $extracurricular, ?Registration $currentRegistration, ?User $user): array
    {
        $coachItems = $this->resolveCoachItems($extracurricular);
        $visibleCoaches = $coachItems->take(3)->values();
        $remainingCoachCount = max(0, $coachItems->count() - $visibleCoaches->count());
        $primaryLocation = $extracurricular->schedules
            ->pluck('location')
            ->filter(fn (?string $location) => filled($location))
            ->first();
        $quotaValue = $this->resolveQuotaValue($extracurricular);
        $participantCount = (int) ($extracurricular->participants_count ?? 0);
        $isQuotaFull = $quotaValue !== null && $participantCount >= $quotaValue;
        $requirements = $this->parseRequirements($extracurricular->requirements);
        $activityBadge = $this->makeStatusBadge(
            $extracurricular->is_active ? 'Kegiatan Aktif' : 'Kegiatan Tidak Aktif',
            $extracurricular->is_active ? 'success' : 'secondary'
        );
        $registrationBadge = $this->resolvePublicRegistrationBadge($extracurricular->is_active, $isQuotaFull);
        $studentBadge = $this->resolveStudentRegistrationBadge($currentRegistration);
        $schedules = $extracurricular->schedules
            ->map(fn ($schedule) => $this->mapScheduleForDetail($schedule))
            ->values();
        $scheduleSectionTitle = $schedules->count() <= 2 ? 'Jadwal terdekat' : 'Jadwal latihan';

        return [
            'user' => $user,
            'is_student' => $user?->hasRole(User::ROLE_STUDENT) ?? false,
            'coach_items' => $coachItems,
            'visible_coaches' => $visibleCoaches,
            'remaining_coach_count' => $remainingCoachCount,
            'primary_location' => $primaryLocation ?: 'Lokasi belum ditentukan.',
            'quota_value' => $quotaValue,
            'quota_text' => $quotaValue !== null ? "{$participantCount} / {$quotaValue} peserta" : ($participantCount > 0 ? "{$participantCount} peserta aktif" : 'Kuota belum ditentukan'),
            'quota_badge' => $this->makeStatusBadge(
                $isQuotaFull ? 'Kuota Penuh' : ($quotaValue !== null ? 'Kuota Tersedia' : 'Kuota Belum Ditentukan'),
                $isQuotaFull ? 'warning' : ($quotaValue !== null ? 'info' : 'secondary')
            ),
            'is_quota_full' => $isQuotaFull,
            'participant_count' => $participantCount,
            'requirements' => $requirements,
            'activity_badge' => $activityBadge,
            'registration_badge' => $registrationBadge,
            'student_badge' => $studentBadge,
            'cta' => $this->resolveDetailCta($extracurricular, $currentRegistration, $user, $isQuotaFull),
            'short_description' => Str::of((string) ($extracurricular->description ?: 'Informasi kegiatan ini akan diperbarui oleh sekolah.'))
                ->squish()
                ->limit(180)
                ->toString(),
            'schedule_section_title' => $scheduleSectionTitle,
            'schedules' => $schedules,
            'breadcrumbs' => [
                ['label' => 'Beranda', 'url' => route('landing')],
                ['label' => $extracurricular->category_label, 'url' => $this->backToActivityUrl($extracurricular)],
                ['label' => $extracurricular->name, 'url' => null],
            ],
        ];
    }

    private function resolveCoachItems(Extracurricular $extracurricular): Collection
    {
        $items = collect();

        if ($extracurricular->relationLoaded('coaches')) {
            $items = $extracurricular->coaches
                ->map(fn ($coach) => [
                    'id' => $coach->id,
                    'name' => $coach->user->name ?? null,
                ]);
        }

        if ($items->isEmpty() && $extracurricular->relationLoaded('coach') && $extracurricular->coach) {
            $items = collect([[
                'id' => $extracurricular->coach->id,
                'name' => $extracurricular->coach->user->name ?? null,
            ]]);
        }

        return $items
            ->filter(fn (array $coach) => filled($coach['name'] ?? null))
            ->unique(fn (array $coach) => ($coach['id'] ?? 'unknown').'-'.Str::lower((string) $coach['name']))
            ->values();
    }

    private function resolveQuotaValue(Extracurricular $extracurricular): ?int
    {
        foreach (['quota', 'member_quota', 'capacity'] as $attribute) {
            $value = $extracurricular->getAttribute($attribute);

            if (is_numeric($value) && (int) $value > 0) {
                return (int) $value;
            }
        }

        return null;
    }

    private function parseRequirements(?string $requirements): Collection
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $requirements))
            ->map(fn (?string $item) => trim((string) preg_replace('/^[\-\*\d\.\)\s]+/u', '', (string) $item)))
            ->filter()
            ->values();
    }

    private function resolvePublicRegistrationBadge(bool $isActive, bool $isQuotaFull): array
    {
        if (! $isActive) {
            return $this->makeStatusBadge('Pendaftaran Ditutup', 'secondary');
        }

        if ($isQuotaFull) {
            return $this->makeStatusBadge('Kuota Penuh', 'warning');
        }

        return $this->makeStatusBadge('Pendaftaran Dibuka', 'success');
    }

    private function resolveStudentRegistrationBadge(?Registration $registration): ?array
    {
        if (! $registration) {
            return null;
        }

        return match ($registration->displayStatus()) {
            Registration::STATUS_APPROVED => $this->makeStatusBadge('Diterima', 'success'),
            Registration::STATUS_REJECTED => $this->makeStatusBadge('Ditolak', 'danger'),
            Registration::STATUS_CANCELLED => $this->makeStatusBadge('Dibatalkan', 'secondary'),
            Registration::DISPLAY_STATUS_WAITING_TEST => $this->makeStatusBadge('Menunggu Tes', 'warning'),
            Registration::DISPLAY_STATUS_SCHEDULED_TEST => $this->makeStatusBadge('Tes Terjadwal', 'warning'),
            Registration::DISPLAY_STATUS_CANCELLATION_REQUESTED => $this->makeStatusBadge('Menunggu Konfirmasi Pembatalan', 'warning'),
            default => $this->makeStatusBadge('Menunggu Verifikasi', 'warning'),
        };
    }

    private function resolveDetailCta(Extracurricular $extracurricular, ?Registration $registration, ?User $user, bool $isQuotaFull): array
    {
        $base = [
            'title' => 'Pendaftaran kegiatan',
            'description' => 'Seluruh verifikasi tetap dilakukan di backend agar pendaftaran berjalan sesuai aturan sekolah.',
            'primary' => null,
            'secondary' => null,
            'status_note' => null,
        ];

        if (! $user) {
            return [
                ...$base,
                'status_note' => 'Masuk sebagai siswa untuk melanjutkan pendaftaran kegiatan ini.',
                'primary' => [
                    'label' => 'Masuk untuk Mendaftar',
                    'href' => route('public.extracurriculars.register', $extracurricular),
                    'variant' => 'light',
                    'icon' => 'bi-box-arrow-in-right',
                ],
                'secondary' => [
                    'label' => 'Buat Akun',
                    'href' => route('register'),
                    'variant' => 'outline-light',
                    'icon' => 'bi-person-plus',
                ],
            ];
        }

        $isStudent = $user->hasRole(User::ROLE_STUDENT);

        if (! $isStudent) {
            return [
                ...$base,
                'status_note' => 'Pendaftaran siswa hanya tersedia melalui akun siswa.',
                'primary' => [
                    'label' => 'Buka Dashboard',
                    'href' => route('dashboard'),
                    'variant' => 'light',
                    'icon' => 'bi-arrow-right-circle',
                ],
            ];
        }

        if ($registration && ! in_array($registration->status, [Registration::STATUS_REJECTED, Registration::STATUS_CANCELLED], true)) {
            return [
                ...$base,
                'status_note' => $this->resolveStudentRegistrationBadge($registration)['label'] ?? 'Status pendaftaran tersedia.',
                'primary' => [
                    'label' => 'Lihat Status Pendaftaran',
                    'href' => route('student.registrations.index'),
                    'variant' => 'light',
                    'icon' => 'bi-clipboard-check',
                ],
            ];
        }

        if (! $extracurricular->is_active) {
            return [
                ...$base,
                'status_note' => 'Kegiatan sedang tidak aktif sehingga pendaftaran belum tersedia.',
                'primary' => [
                    'label' => 'Pendaftaran Ditutup',
                    'href' => null,
                    'variant' => 'outline-light',
                    'icon' => 'bi-lock',
                    'disabled' => true,
                ],
            ];
        }

        if ($isQuotaFull) {
            return [
                ...$base,
                'status_note' => 'Kuota peserta yang tersedia untuk kegiatan ini sudah penuh.',
                'primary' => [
                    'label' => 'Kuota Penuh',
                    'href' => null,
                    'variant' => 'outline-light',
                    'icon' => 'bi-people-fill',
                    'disabled' => true,
                ],
            ];
        }

        return [
            ...$base,
            'status_note' => 'Pendaftaran dilakukan melalui form siswa dan akan diverifikasi oleh pembina atau admin.',
            'primary' => [
                'label' => 'Daftar Kegiatan Ini',
                'href' => route('student.extracurriculars.register', $extracurricular),
                'variant' => 'light',
                'icon' => 'bi-send-check',
            ],
        ];
    }

    private function mapScheduleForDetail($schedule): array
    {
        $date = $schedule->activity_date;
        $start = $schedule->start_time ? Str::of((string) $schedule->start_time)->substr(0, 5)->toString() : null;
        $end = $schedule->end_time ? Str::of((string) $schedule->end_time)->substr(0, 5)->toString() : null;
        $status = $this->resolveScheduleStatusBadge($schedule);

        return [
            'title' => $schedule->title,
            'type_label' => $schedule->schedule_type === 'talent_test' ? 'Tes' : 'Latihan',
            'date_label' => $date ? $date->locale('id')->translatedFormat('j F Y') : 'Tanggal belum ditentukan',
            'time_label' => $start ? trim($start.($end ? " - {$end}" : '')).' WITA' : 'Waktu belum ditentukan',
            'location' => $schedule->location ?: 'Lokasi belum ditentukan',
            'status' => $status,
        ];
    }

    private function resolveScheduleStatusBadge($schedule): array
    {
        $isCancelled = $schedule->cancelled_at !== null || $schedule->status === 'cancelled';

        if ($isCancelled) {
            return $this->makeStatusBadge('Dibatalkan', 'danger');
        }

        $today = now()->toDateString();
        $date = $schedule->activity_date?->toDateString();
        $endTime = $schedule->end_time ? Str::of((string) $schedule->end_time)->substr(0, 5)->toString() : null;
        $nowTime = now()->format('H:i');

        if ($date && $date < $today) {
            return $this->makeStatusBadge('Selesai', 'secondary');
        }

        if ($date === $today && $endTime !== null && $endTime < $nowTime) {
            return $this->makeStatusBadge('Selesai', 'secondary');
        }

        if ($date === $today) {
            return $this->makeStatusBadge('Hari Ini', 'warning');
        }

        return $this->makeStatusBadge('Akan Datang', 'success');
    }

    private function makeStatusBadge(string $label, string $tone): array
    {
        return [
            'label' => $label,
            'tone' => $tone,
            'class' => match ($tone) {
                'success' => 'badge-status-success',
                'warning' => 'badge-status-warning',
                'danger' => 'badge-status-danger',
                'info' => 'badge-status-info',
                default => 'badge-status-secondary',
            },
        ];
    }
}
