<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Article;
use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\Extracurricular;
use App\Models\ExtracurricularAchievement;
use App\Models\Registration;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->validateFilters($request);
        $activeTab = $filters['tab'] ?? 'overview';
        $schoolYear = $filters['school_year'] ?? $this->currentSchoolYear();
        $semester = $filters['semester'] ?? 'all';
        [$windowStart, $windowEnd] = $this->dateWindow($schoolYear, $semester);

        $summary = Cache::remember(
            'principal.dashboard.summary.v4',
            now()->addMinutes(10),
            fn (): array => $this->buildSummaryCards()
        );

        $charts = Cache::remember(
            'principal.dashboard.charts.'.md5(json_encode([$schoolYear, $semester])),
            now()->addMinutes(10),
            fn (): array => $this->buildCharts($windowStart, $windowEnd)
        );

        return view('dashboard.principal', [
            'summary' => $summary,
            'charts' => $charts,
            'activities' => $this->buildExtracurricularPaginator($filters, $windowStart, $windowEnd),
            'joinedStudents' => $this->buildJoinedStudentsPaginator($filters, $windowStart, $windowEnd),
            'notJoinedStudents' => $this->buildNotJoinedStudentsPaginator($filters),
            'lowAttendanceStudents' => $this->lowAttendanceStudents($filters, $windowStart, $windowEnd),
            'lowActivityExtracurriculars' => $this->lowActivityExtracurriculars($windowStart, $windowEnd),
            'achievementRows' => $this->buildAchievementPaginator($filters, $windowStart, $windowEnd),
            'upcomingAgenda' => $this->upcomingAgenda(),
            'importantAnnouncements' => $this->importantAnnouncements(),
            'latestNews' => $this->latestNews(),
            'activeTab' => $activeTab,
            'filters' => $filters,
            'schoolYearOptions' => $this->schoolYearOptions(),
            'classOptions' => collect(array_keys(Student::registrationClassOptions())),
            'extracurricularOptions' => Extracurricular::orderBy('name')->get(['id', 'name']),
            'reportLinks' => [
                'members' => route('principal.reports.index', array_merge($request->query(), ['report_type' => 'members'])),
                'registrations' => route('principal.reports.index', array_merge($request->query(), ['report_type' => 'registrations'])),
                'attendances' => route('principal.reports.index', array_merge($request->query(), ['report_type' => 'attendances'])),
                'achievements' => route('principal.reports.index', array_merge($request->query(), ['report_type' => 'achievements'])),
                'activities' => route('principal.reports.index', array_merge($request->query(), ['report_type' => 'activities'])),
            ],
            'windowLabel' => $this->windowLabel($schoolYear, $semester),
        ]);
    }

    private function validateFilters(Request $request): array
    {
        $filters = $request->validate([
            'tab' => ['nullable', Rule::in(['overview', 'activities', 'students', 'attendance', 'achievements', 'agenda', 'reports'])],
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'school_year' => ['nullable', 'regex:/^\d{4}-\d{4}$/'],
            'semester' => ['nullable', Rule::in(['all', 'odd', 'even'])],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $filters['tab'] = $filters['tab'] ?? 'overview';
        $filters['class_name'] = Student::normalizeClassName($filters['class_name'] ?? null);
        $filters['school_year'] = $filters['school_year'] ?? $this->currentSchoolYear();
        $filters['semester'] = $filters['semester'] ?? 'all';

        return $filters;
    }

    private function buildSummaryCards(): array
    {
        $totalStudents = Student::count();
        $studentsJoined = Registration::query()
            ->where('status', Registration::STATUS_APPROVED)
            ->distinct('student_id')
            ->count('student_id');

        $registrationSummary = Registration::query()
            ->selectRaw('
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rejected_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_count
            ', [
                Registration::STATUS_APPROVED,
                Registration::STATUS_REJECTED,
                Registration::STATUS_PENDING,
            ])
            ->first();

        return [
            'total_extracurriculars' => Extracurricular::count(),
            'total_students' => $totalStudents,
            'students_joined' => $studentsJoined,
            'students_not_joined' => max($totalStudents - $studentsJoined, 0),
            'registrations_approved' => (int) ($registrationSummary->approved_count ?? 0),
            'registrations_rejected' => (int) ($registrationSummary->rejected_count ?? 0),
            'registrations_pending' => (int) ($registrationSummary->pending_count ?? 0),
        ];
    }

    private function buildCharts(Carbon $windowStart, Carbon $windowEnd): array
    {
        $extracurriculars = Extracurricular::orderBy('name')->get(['id', 'name']);

        $memberCounts = Registration::query()
            ->selectRaw('extracurricular_id, COUNT(*) as total')
            ->where('status', Registration::STATUS_APPROVED)
            ->groupBy('extracurricular_id')
            ->pluck('total', 'extracurricular_id');

        $registrationCounts = Registration::query()
            ->selectRaw('extracurricular_id, COUNT(*) as total')
            ->whereDate('registration_date', '>=', $windowStart)
            ->whereDate('registration_date', '<=', $windowEnd)
            ->groupBy('extracurricular_id')
            ->pluck('total', 'extracurricular_id');

        $attendanceCounts = Attendance::query()
            ->selectRaw('extracurricular_id, COUNT(*) as total, SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present')
            ->where(function ($query): void {
                $query->whereNull('save_state')->orWhere('save_state', Attendance::SAVE_STATE_FINALIZED);
            })
            ->whereHas('schedule', function ($query) use ($windowStart, $windowEnd): void {
                $query->whereDate('activity_date', '>=', $windowStart)
                    ->whereDate('activity_date', '<=', $windowEnd);
            })
            ->groupBy('extracurricular_id')
            ->get()
            ->keyBy('extracurricular_id');

        $achievementCounts = Assessment::query()
            ->selectRaw('extracurricular_id, COUNT(*) as total')
            ->where('assessment_type', 'achievement')
            ->whereDate('assessment_date', '>=', $windowStart)
            ->whereDate('assessment_date', '<=', $windowEnd)
            ->groupBy('extracurricular_id')
            ->pluck('total', 'extracurricular_id');

        $achievementCatalogCounts = ExtracurricularAchievement::query()
            ->selectRaw('extracurricular_id, COUNT(*) as total')
            ->whereDate('achievement_date', '>=', $windowStart)
            ->whereDate('achievement_date', '<=', $windowEnd)
            ->groupBy('extracurricular_id')
            ->pluck('total', 'extracurricular_id');

        $memberChart = $extracurriculars->map(fn (Extracurricular $item) => [
            'label' => $item->name,
            'value' => (int) ($memberCounts[$item->id] ?? 0),
        ])->sortByDesc('value')->values();

        $interestChart = $extracurriculars->map(fn (Extracurricular $item) => [
            'label' => $item->name,
            'value' => (int) ($registrationCounts[$item->id] ?? 0),
        ])->values();

        $attendanceChart = $extracurriculars->map(function (Extracurricular $item) use ($attendanceCounts): array {
            $summary = $attendanceCounts->get($item->id);
            $total = (int) ($summary->total ?? 0);
            $present = (int) ($summary->present ?? 0);

            return [
                'label' => $item->name,
                'value' => $total > 0 ? round(($present / $total) * 100, 1) : 0.0,
            ];
        })->sortByDesc('value')->values();

        $achievementChart = $extracurriculars->map(fn (Extracurricular $item) => [
            'label' => $item->name,
            'value' => (int) ($achievementCounts[$item->id] ?? 0) + (int) ($achievementCatalogCounts[$item->id] ?? 0),
        ])->sortByDesc('value')->values();

        return [
            'member_chart' => $memberChart->take(8)->values(),
            'top_interest' => $interestChart->sortByDesc('value')->take(5)->values(),
            'lowest_interest' => $interestChart->sortBy('value')->take(5)->values(),
            'registration_trend' => $this->registrationTrend($windowStart, $windowEnd),
            'attendance_chart' => $attendanceChart->take(8)->values(),
            'achievement_chart' => $achievementChart->take(8)->values(),
        ];
    }

    private function registrationTrend(Carbon $windowStart, Carbon $windowEnd): Collection
    {
        $driver = DB::getDriverName();
        $monthExpression = $driver === 'sqlite'
            ? "strftime('%Y-%m', registration_date)"
            : "DATE_FORMAT(registration_date, '%Y-%m')";

        $grouped = Registration::query()
            ->selectRaw("{$monthExpression} as month_key, COUNT(*) as total")
            ->whereDate('registration_date', '>=', $windowStart)
            ->whereDate('registration_date', '<=', $windowEnd)
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $period = collect();
        $cursor = $windowStart->copy()->startOfMonth();
        while ($cursor->lte($windowEnd)) {
            $key = $cursor->format('Y-m');
            $period->push([
                'label' => $cursor->translatedFormat('M Y'),
                'value' => (int) ($grouped[$key] ?? 0),
            ]);
            $cursor->addMonth();
        }

        return $period;
    }

    private function buildExtracurricularPaginator(array $filters, Carbon $windowStart, Carbon $windowEnd): LengthAwarePaginator
    {
        $query = Extracurricular::with(['coaches.user', 'coach.user'])
            ->when($filters['extracurricular_id'] ?? null, fn ($builder, $value) => $builder->whereKey($value))
            ->when($filters['search'] ?? null, fn ($builder, $value) => $builder->where('name', 'like', "%{$value}%"))
            ->orderBy('name');

        $paginator = $query->paginate(8, ['*'], 'activities_page')->withQueryString();
        $items = $paginator->getCollection();
        $ids = $items->pluck('id');

        $approvedMembers = Registration::query()
            ->selectRaw('extracurricular_id, COUNT(*) as total')
            ->where('status', Registration::STATUS_APPROVED)
            ->whereIn('extracurricular_id', $ids)
            ->groupBy('extracurricular_id')
            ->pluck('total', 'extracurricular_id');

        $allRegistrations = Registration::query()
            ->selectRaw('extracurricular_id, COUNT(*) as total')
            ->whereIn('extracurricular_id', $ids)
            ->groupBy('extracurricular_id')
            ->pluck('total', 'extracurricular_id');

        $attendanceSummary = Attendance::query()
            ->selectRaw('extracurricular_id, COUNT(*) as total, SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present')
            ->whereIn('extracurricular_id', $ids)
            ->where(function ($query): void {
                $query->whereNull('save_state')->orWhere('save_state', Attendance::SAVE_STATE_FINALIZED);
            })
            ->whereHas('schedule', function ($query) use ($windowStart, $windowEnd): void {
                $query->whereDate('activity_date', '>=', $windowStart)
                    ->whereDate('activity_date', '<=', $windowEnd);
            })
            ->groupBy('extracurricular_id')
            ->get()
            ->keyBy('extracurricular_id');

        $recentSchedule = Schedule::query()
            ->whereIn('extracurricular_id', $ids)
            ->whereDate('activity_date', '>=', now()->subDays(30))
            ->orderBy('activity_date')
            ->get()
            ->groupBy('extracurricular_id');

        $paginator->setCollection($items->map(function (Extracurricular $item) use ($approvedMembers, $allRegistrations, $attendanceSummary, $recentSchedule): Extracurricular {
            $summary = $attendanceSummary->get($item->id);
            $totalAttendance = (int) ($summary->total ?? 0);
            $presentAttendance = (int) ($summary->present ?? 0);
            $schedule = $recentSchedule->get($item->id)?->first();
            $scheduleText = $schedule && $schedule->activity_date
                ? trim(($schedule->title ?: 'Jadwal kegiatan').' - '.Carbon::parse($schedule->activity_date)->format('d-m-Y'))
                : ($item->schedule_overview ?: 'Belum ada jadwal terbaru');

            $item->setAttribute('principal_members_count', (int) ($approvedMembers[$item->id] ?? 0));
            $item->setAttribute('principal_registration_count', (int) ($allRegistrations[$item->id] ?? 0));
            $item->setAttribute('principal_attendance_rate', $totalAttendance > 0 ? round(($presentAttendance / $totalAttendance) * 100, 1) : 0.0);
            $item->setAttribute('principal_recent_schedule_text', $scheduleText);
            $item->setAttribute('principal_quota', $item->quota ?? $item->member_quota ?? $item->capacity ?? null);

            return $item;
        }));

        return $paginator;
    }

    private function buildJoinedStudentsPaginator(array $filters, Carbon $windowStart, Carbon $windowEnd): LengthAwarePaginator
    {
        $query = Student::with([
            'user',
            'registrations' => function ($query) use ($filters, $windowStart, $windowEnd): void {
                $query->with('extracurricular')
                    ->when($filters['extracurricular_id'] ?? null, fn ($builder, $value) => $builder->where('extracurricular_id', $value))
                    ->whereDate('registration_date', '>=', $windowStart)
                    ->whereDate('registration_date', '<=', $windowEnd)
                    ->latest('registration_date');
            },
        ])
            ->whereHas('registrations', function ($query) use ($filters, $windowStart, $windowEnd): void {
                $query->where('status', Registration::STATUS_APPROVED)
                    ->when($filters['extracurricular_id'] ?? null, fn ($builder, $value) => $builder->where('extracurricular_id', $value))
                    ->whereDate('registration_date', '>=', $windowStart)
                    ->whereDate('registration_date', '<=', $windowEnd);
            })
            ->when($filters['class_name'] ?? null, function ($query, $value): void {
                $query->whereRaw(Student::normalizedClassExpression('class_name').' = ?', [Student::normalizedClassComparable($value)]);
            })
            ->when($filters['gender'] ?? null, fn ($query, $value) => $query->where('gender', $value))
            ->when($filters['search'] ?? null, function ($query, $value): void {
                $query->where(function ($studentQuery) use ($value): void {
                    $studentQuery->where('nis', 'like', "%{$value}%")
                        ->orWhere('class_name', 'like', "%{$value}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$value}%"));
                });
            })
            ->latest();

        return $query->paginate(8, ['*'], 'joined_students_page')->withQueryString();
    }

    private function buildNotJoinedStudentsPaginator(array $filters): LengthAwarePaginator
    {
        $query = Student::with('user')
            ->whereDoesntHave('registrations', fn ($builder) => $builder->where('status', Registration::STATUS_APPROVED))
            ->when($filters['class_name'] ?? null, function ($query, $value): void {
                $query->whereRaw(Student::normalizedClassExpression('class_name').' = ?', [Student::normalizedClassComparable($value)]);
            })
            ->when($filters['gender'] ?? null, fn ($query, $value) => $query->where('gender', $value))
            ->when($filters['search'] ?? null, function ($query, $value): void {
                $query->where(function ($studentQuery) use ($value): void {
                    $studentQuery->where('nis', 'like', "%{$value}%")
                        ->orWhere('class_name', 'like', "%{$value}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$value}%"));
                });
            })
            ->latest();

        return $query->paginate(8, ['*'], 'not_joined_students_page')->withQueryString();
    }

    private function lowAttendanceStudents(array $filters, Carbon $windowStart, Carbon $windowEnd): Collection
    {
        $rows = Attendance::query()
            ->selectRaw('student_id, COUNT(*) as total, SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present')
            ->where(function ($query): void {
                $query->whereNull('save_state')->orWhere('save_state', Attendance::SAVE_STATE_FINALIZED);
            })
            ->when($filters['extracurricular_id'] ?? null, fn ($query, $value) => $query->where('extracurricular_id', $value))
            ->whereHas('schedule', function ($query) use ($windowStart, $windowEnd): void {
                $query->whereDate('activity_date', '>=', $windowStart)
                    ->whereDate('activity_date', '<=', $windowEnd);
            })
            ->groupBy('student_id')
            ->havingRaw('COUNT(*) >= 2')
            ->get()
            ->map(function ($row): array {
                $rate = $row->total > 0 ? round(($row->present / $row->total) * 100, 1) : 0.0;

                return [
                    'student_id' => $row->student_id,
                    'attendance_rate' => $rate,
                    'total_sessions' => (int) $row->total,
                ];
            })
            ->filter(fn (array $row) => $row['attendance_rate'] < 75)
            ->sortBy('attendance_rate')
            ->take(8)
            ->values();

        $students = Student::with('user')->whereIn('id', $rows->pluck('student_id'))->get()->keyBy('id');

        return $rows->map(function (array $row) use ($students): array {
            $student = $students->get($row['student_id']);

            return [
                'name' => $student?->user?->name ?? '-',
                'class_name' => $student?->class_name ?? '-',
                'attendance_rate' => $row['attendance_rate'],
                'total_sessions' => $row['total_sessions'],
            ];
        });
    }

    private function lowActivityExtracurriculars(Carbon $windowStart, Carbon $windowEnd): Collection
    {
        $scheduleCounts = Schedule::query()
            ->selectRaw('extracurricular_id, COUNT(*) as total')
            ->whereDate('activity_date', '>=', $windowStart)
            ->whereDate('activity_date', '<=', $windowEnd)
            ->groupBy('extracurricular_id')
            ->pluck('total', 'extracurricular_id');

        $attendanceCounts = Attendance::query()
            ->selectRaw('extracurricular_id, COUNT(*) as total')
            ->where(function ($query): void {
                $query->whereNull('save_state')->orWhere('save_state', Attendance::SAVE_STATE_FINALIZED);
            })
            ->whereHas('schedule', function ($query) use ($windowStart, $windowEnd): void {
                $query->whereDate('activity_date', '>=', $windowStart)
                    ->whereDate('activity_date', '<=', $windowEnd);
            })
            ->groupBy('extracurricular_id')
            ->pluck('total', 'extracurricular_id');

        return Extracurricular::with(['coaches.user', 'coach.user'])
            ->get()
            ->map(function (Extracurricular $item) use ($scheduleCounts, $attendanceCounts): array {
                $scheduleCount = (int) ($scheduleCounts[$item->id] ?? 0);
                $attendanceCount = (int) ($attendanceCounts[$item->id] ?? 0);

                return [
                    'name' => $item->name,
                    'coach' => $item->coach_names,
                    'schedule_count' => $scheduleCount,
                    'attendance_count' => $attendanceCount,
                    'status' => $scheduleCount === 0 || $attendanceCount === 0 ? 'Perlu perhatian' : 'Normal',
                ];
            })
            ->sortBy(fn (array $row) => $row['schedule_count'] + $row['attendance_count'])
            ->take(6)
            ->values();
    }

    private function buildAchievementPaginator(array $filters, Carbon $windowStart, Carbon $windowEnd): LengthAwarePaginator
    {
        $rows = Assessment::with(['student.user', 'extracurricular'])
            ->where('assessment_type', 'achievement')
            ->when($filters['extracurricular_id'] ?? null, fn ($query, $value) => $query->where('extracurricular_id', $value))
            ->when($filters['search'] ?? null, fn ($query, $value) => $query->where('title', 'like', "%{$value}%"))
            ->whereDate('assessment_date', '>=', $windowStart)
            ->whereDate('assessment_date', '<=', $windowEnd)
            ->get()
            ->map(fn (Assessment $row) => [
                'title' => $row->title,
                'extracurricular' => $row->extracurricular->name ?? '-',
                'student' => $row->student->user->name ?? 'Prestasi kegiatan',
                'level' => $this->inferAchievementLevel($row->title, $row->description),
                'result' => $row->score !== null ? 'Skor '.$row->score : ($row->description ? Str::limit($row->description, 70) : '-'),
                'date' => optional($row->assessment_date)->format('d-m-Y') ?: '-',
                'documentation' => $row->description ?: 'Belum ada dokumentasi tertulis.',
                '_sort' => optional($row->assessment_date)?->timestamp ?? 0,
            ])
            ->concat(
                ExtracurricularAchievement::with('extracurricular')
                    ->when($filters['extracurricular_id'] ?? null, fn ($query, $value) => $query->where('extracurricular_id', $value))
                    ->when($filters['search'] ?? null, fn ($query, $value) => $query->where('title', 'like', "%{$value}%"))
                    ->whereDate('achievement_date', '>=', $windowStart)
                    ->whereDate('achievement_date', '<=', $windowEnd)
                    ->get()
                    ->map(fn (ExtracurricularAchievement $row) => [
                        'title' => $row->title,
                        'extracurricular' => $row->extracurricular->name ?? '-',
                        'student' => 'Prestasi ekstrakurikuler',
                        'level' => $this->inferAchievementLevel($row->title, $row->description),
                        'result' => $row->description ? Str::limit($row->description, 70) : '-',
                        'date' => optional($row->achievement_date)->format('d-m-Y') ?: '-',
                        'documentation' => $row->description ?: 'Belum ada dokumentasi tertulis.',
                        '_sort' => optional($row->achievement_date)?->timestamp ?? 0,
                    ])
            )
            ->sortByDesc('_sort')
            ->values()
            ->map(function (array $row): array {
                unset($row['_sort']);

                return $row;
            });

        return $this->paginateCollection($rows, 8, 'achievement_page');
    }

    private function upcomingAgenda(): Collection
    {
        return Schedule::with(['extracurricular.coaches.user', 'extracurricular.coach.user'])
            ->whereDate('activity_date', '>=', today())
            ->orderBy('activity_date')
            ->orderBy('start_time')
            ->limit(8)
            ->get()
            ->map(fn (Schedule $row) => [
                'title' => $row->title,
                'extracurricular' => $row->extracurricular->name ?? '-',
                'coach' => $row->extracurricular->coach_names,
                'type' => $this->scheduleAudienceLabel($row),
                'date' => optional($row->activity_date)->translatedFormat('d F Y') ?: '-',
                'time' => trim(substr((string) $row->start_time, 0, 5).' - '.substr((string) $row->end_time, 0, 5), ' -'),
                'location' => $row->location ?: 'Belum ditentukan',
            ]);
    }

    private function importantAnnouncements(): Collection
    {
        return Announcement::with(['publisher', 'extracurricular'])
            ->visibleToStudents()
            ->latest('publish_at')
            ->limit(5)
            ->get();
    }

    private function latestNews(): Collection
    {
        $announcementNews = Announcement::with(['publisher', 'extracurricular'])
            ->visibleToStudents()
            ->latest('publish_at')
            ->limit(4)
            ->get()
            ->map(fn (Announcement $item) => [
                'type' => 'announcement',
                'title' => $item->title,
                'subtitle' => $item->extracurricular->name ?? 'Informasi sekolah',
                'date' => optional($item->publish_at ?? $item->created_at)->translatedFormat('d M Y H:i') ?? '-',
                'content' => Str::limit($item->content, 120),
                '_sort' => optional($item->publish_at ?? $item->created_at)?->timestamp ?? 0,
            ]);

        $articleNews = Article::with(['publisher', 'extracurricular'])
            ->visibleToPublic()
            ->latest('publish_at')
            ->limit(4)
            ->get()
            ->map(fn (Article $item) => [
                'type' => 'article',
                'title' => $item->title,
                'subtitle' => $item->extracurricular->name ?? 'Artikel sekolah',
                'date' => optional($item->publish_at ?? $item->created_at)->translatedFormat('d M Y H:i') ?? '-',
                'content' => Str::limit($item->excerpt ?: strip_tags($item->content), 120),
                '_sort' => optional($item->publish_at ?? $item->created_at)?->timestamp ?? 0,
            ]);

        $achievementNews = Assessment::with(['student.user', 'extracurricular'])
            ->where('assessment_type', 'achievement')
            ->latest('assessment_date')
            ->limit(4)
            ->get()
            ->map(fn (Assessment $item) => [
                'type' => 'achievement',
                'title' => $item->title,
                'subtitle' => $item->extracurricular->name ?? 'Prestasi kegiatan',
                'date' => optional($item->assessment_date)->translatedFormat('d M Y') ?? '-',
                'content' => $item->description ? Str::limit($item->description, 120) : 'Prestasi baru tercatat di sistem.',
                '_sort' => optional($item->assessment_date)?->timestamp ?? 0,
            ]);

        return $announcementNews
            ->concat($articleNews)
            ->concat($achievementNews)
            ->sortByDesc('_sort')
            ->take(6)
            ->map(function (array $item): array {
                unset($item['_sort']);

                return $item;
            })
            ->values();
    }

    private function paginateCollection(Collection $items, int $perPage, string $pageName): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $slice,
            $items->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
                'query' => request()->query(),
            ]
        );
    }

    private function schoolYearOptions(): Collection
    {
        $years = collect([
            Registration::query()->min('registration_date'),
            Schedule::query()->min('activity_date'),
            Assessment::query()->min('assessment_date'),
            ExtracurricularAchievement::query()->min('achievement_date'),
        ])->filter()->map(fn ($date) => Carbon::parse($date)->year);

        $startYear = $years->min() ?: now()->year;
        $currentStartYear = now()->month >= 7 ? now()->year : now()->year - 1;

        return collect(range($startYear, $currentStartYear))
            ->map(fn (int $year) => $year.'-'.($year + 1))
            ->reverse()
            ->values();
    }

    private function currentSchoolYear(): string
    {
        $startYear = now()->month >= 7 ? now()->year : now()->year - 1;

        return $startYear.'-'.($startYear + 1);
    }

    private function dateWindow(string $schoolYear, string $semester): array
    {
        [$startYear, $endYear] = array_map('intval', explode('-', $schoolYear));

        return match ($semester) {
            'odd' => [Carbon::create($startYear, 7, 1)->startOfDay(), Carbon::create($startYear, 12, 31)->endOfDay()],
            'even' => [Carbon::create($endYear, 1, 1)->startOfDay(), Carbon::create($endYear, 6, 30)->endOfDay()],
            default => [Carbon::create($startYear, 7, 1)->startOfDay(), Carbon::create($endYear, 6, 30)->endOfDay()],
        };
    }

    private function windowLabel(string $schoolYear, string $semester): string
    {
        return match ($semester) {
            'odd' => 'Semester Ganjil '.$schoolYear,
            'even' => 'Semester Genap '.$schoolYear,
            default => 'Tahun Ajaran '.$schoolYear,
        };
    }

    private function inferAchievementLevel(?string $title, ?string $description): string
    {
        $haystack = Str::lower(trim($title.' '.$description));

        return match (true) {
            str_contains($haystack, 'internasional') => 'Internasional',
            str_contains($haystack, 'nasional') => 'Nasional',
            str_contains($haystack, 'provinsi') => 'Provinsi',
            str_contains($haystack, 'kota'), str_contains($haystack, 'kabupaten') => 'Kab/Kota',
            str_contains($haystack, 'sekolah') => 'Sekolah',
            default => '-',
        };
    }

    private function scheduleAudienceLabel(Schedule $schedule): string
    {
        $title = Str::lower($schedule->title);

        return match (true) {
            $schedule->isTalentTest() => 'Seleksi anggota',
            str_contains($title, 'rapat') => 'Rapat',
            str_contains($title, 'lomba') || str_contains($title, 'kompetisi') || str_contains($title, 'tanding') => 'Lomba',
            default => 'Latihan',
        };
    }
}
