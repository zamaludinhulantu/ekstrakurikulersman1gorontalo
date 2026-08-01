<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\TalentTestParticipant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        $user = auth()->user();

        return match ($user->role) {
            User::ROLE_SUPER_ADMIN => redirect()->route('super-admin.dashboard'),
            User::ROLE_ADMIN => redirect()->route('admin.dashboard'),
            User::ROLE_COACH => redirect()->route('coach.dashboard'),
            User::ROLE_STUDENT => redirect()->route('student.dashboard'),
            User::ROLE_PRINCIPAL => redirect()->route('principal.dashboard'),
            default => redirect()->route('login')->with('error', 'Role pengguna tidak dikenali.'),
        };
    }

    public function superAdmin(): View
    {
        return $this->buildAdminDashboardView(
            title: 'Dashboard Super Admin',
            subtitle: 'Pantau operasional harian dan kontrol akses sistem dari satu dashboard.',
        );
    }

    public function admin(): View
    {
        return $this->buildAdminDashboardView(
            title: 'Dashboard Admin/Kesiswaan',
            subtitle: 'Pantau pendaftaran, data ekskul, dan aktivitas utama dari satu dashboard.',
        );
    }

    private function buildAdminDashboardView(string $title, string $subtitle): View
    {
        $registrationSummary = Registration::query()
            ->selectRaw("
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rejected_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled_count,
                SUM(CASE WHEN cancellation_requested_at IS NOT NULL THEN 1 ELSE 0 END) as cancellation_request_count
            ", [
                Registration::STATUS_PENDING,
                Registration::STATUS_APPROVED,
                Registration::STATUS_REJECTED,
                Registration::STATUS_CANCELLED,
            ])
            ->first();
        $pendingRegistrations = Registration::query()
            ->where('status', Registration::STATUS_PENDING)
            ->whereNull('cancellation_requested_at')
            ->where(function ($query): void {
                $query->whereNull('willing_to_take_test')->orWhere('willing_to_take_test', false);
            })
            ->count();
        $approvedMemberships = (int) ($registrationSummary->approved_count ?? 0);
        $approvedStudents = Registration::query()
            ->where('status', Registration::STATUS_APPROVED)
            ->distinct()
            ->count('student_id');
        $upcomingTalentTests = $this->upcomingScheduleQuery()
            ->where('schedule_type', 'talent_test')
            ->count();
        $unassignedActivities = Extracurricular::query()
            ->where('is_active', true)
            ->whereNull('coach_id')
            ->whereDoesntHave('coaches')
            ->count();
        $testsWithoutSchedule = Registration::query()
            ->where('status', Registration::STATUS_PENDING)
            ->where('willing_to_take_test', true)
            ->whereNull('cancellation_requested_at')
            ->whereDoesntHave('talentTestParticipants')
            ->count();
        $draftAnnouncements = Announcement::supportsEnhancedSchema()
            ? Announcement::where('publication_status', Announcement::STATUS_DRAFT)->count()
            : Announcement::where('is_active', false)->count();

        $actionItems = collect([
            [
                'label' => 'Pendaftaran menunggu',
                'description' => 'Pendaftaran baru yang belum memperoleh keputusan.',
                'count' => $pendingRegistrations,
                'href' => route('admin.registrations.index', ['status' => Registration::STATUS_PENDING]),
                'icon' => 'bi-hourglass-split',
                'tone' => 'warning',
            ],
            [
                'label' => 'Permintaan pembatalan',
                'description' => 'Keikutsertaan yang menunggu konfirmasi Admin atau Pembina.',
                'count' => (int) ($registrationSummary->cancellation_request_count ?? 0),
                'href' => route('admin.registrations.index', ['status' => Registration::DISPLAY_STATUS_CANCELLATION_REQUESTED]),
                'icon' => 'bi-person-x',
                'tone' => 'danger',
            ],
            [
                'label' => 'Tes belum dijadwalkan',
                'description' => 'Peserta bersedia tes tetapi belum masuk jadwal tes.',
                'count' => $testsWithoutSchedule,
                'href' => route('admin.registrations.index', ['status' => Registration::DISPLAY_STATUS_WAITING_TEST]),
                'icon' => 'bi-calendar-x',
                'tone' => 'warning',
            ],
            [
                'label' => 'Kegiatan tanpa pembina',
                'description' => 'Kegiatan aktif yang belum memiliki penanggung jawab.',
                'count' => $unassignedActivities,
                'href' => route('admin.extracurriculars.index'),
                'icon' => 'bi-person-exclamation',
                'tone' => 'danger',
            ],
            [
                'label' => 'Pengumuman draft',
                'description' => 'Informasi yang belum dipublikasikan.',
                'count' => $draftAnnouncements,
                'href' => route('admin.announcements.index'),
                'icon' => 'bi-megaphone',
                'tone' => 'primary',
            ],
        ])->filter(fn (array $item): bool => $item['count'] > 0)->values()->all();

        return view('dashboard.admin', [
            'dashboardTitle' => $title,
            'dashboardSubtitle' => $subtitle,
            'totalStudents' => Student::count(),
            'totalExtracurriculars' => Extracurricular::where('is_active', true)->count(),
            'pendingRegistrations' => $pendingRegistrations,
            'approvedMemberships' => $approvedMemberships,
            'approvedStudents' => $approvedStudents,
            'upcomingTalentTests' => $upcomingTalentTests,
            'actionItems' => $actionItems,
            'statusDistribution' => [
                ['key' => 'pending', 'label' => 'Menunggu', 'value' => (int) ($registrationSummary->pending_count ?? 0)],
                ['key' => 'approved', 'label' => 'Diterima', 'value' => $approvedMemberships],
                ['key' => 'rejected', 'label' => 'Ditolak', 'value' => (int) ($registrationSummary->rejected_count ?? 0)],
                ['key' => 'cancelled', 'label' => 'Dibatalkan', 'value' => (int) ($registrationSummary->cancelled_count ?? 0)],
            ],
            'upcomingSchedules' => $this->upcomingScheduleQuery()
                ->with('extracurricular:id,name')
                ->limit(5)
                ->get(),
            'recentRegistrations' => Registration::with([
                'student.user',
                'extracurricular:id,name',
                'talentTestResults:id,registration_id,status,published_at',
                'talentTestParticipants:id,registration_id,schedule_id',
            ])
                ->latest('created_at')
                ->latest('id')
                ->limit(5)
                ->get(),
            'registrationTrend' => $this->buildMonthlyRegistrationTrend(),
            'popularExtracurriculars' => $this->buildPopularExtracurriculars(),
            'popularRegistrations' => $this->buildPopularRegistrations(),
            'dashboardUpdatedAt' => now(),
        ]);
    }

    private function buildPopularExtracurriculars(): array
    {
        $items = Extracurricular::query()
            ->where('is_active', true)
            ->withCount([
                'registrations as participant_count' => fn ($query) => $query
                    ->where('status', Registration::STATUS_APPROVED),
            ])
            ->orderByDesc('participant_count')
            ->orderBy('name')
            ->get(['id', 'name']);

        $maximum = max(1, (int) $items->max('participant_count'));

        return $items
            ->filter(fn (Extracurricular $item): bool => (int) $item->participant_count > 0)
            ->values()
            ->map(fn (Extracurricular $extracurricular): array => [
                'name' => $extracurricular->name,
                'total' => (int) $extracurricular->participant_count,
                'width' => round(((int) $extracurricular->participant_count / $maximum) * 100, 2),
            ])
            ->all();
    }

    private function buildPopularRegistrations(): array
    {
        $items = Extracurricular::query()
            ->where('is_active', true)
            ->withCount([
                'registrations as registration_count' => fn ($query) => $query
                    ->where('status', Registration::STATUS_PENDING)
                    ->whereNull('cancellation_requested_at')
                    ->where(function ($registrationQuery): void {
                        $registrationQuery->whereNull('willing_to_take_test')
                            ->orWhere('willing_to_take_test', false);
                    }),
            ])
            ->orderByDesc('registration_count')
            ->orderBy('name')
            ->get(['id', 'name']);

        $items = $items->filter(
            fn (Extracurricular $extracurricular): bool => (int) $extracurricular->registration_count > 0
        )->values();
        $maximum = max(1, (int) $items->max('registration_count'));

        return $items
            ->map(fn (Extracurricular $extracurricular): array => [
                'name' => $extracurricular->name,
                'total' => (int) $extracurricular->registration_count,
                'width' => round(((int) $extracurricular->registration_count / $maximum) * 100, 2),
            ])
            ->all();
    }

    private function buildMonthlyRegistrationTrend(): array
    {
        $driver = Registration::query()->getModel()->getConnection()->getDriverName();
        $monthExpression = match ($driver) {
            'sqlite' => "strftime('%Y-%m', registration_date)",
            default => "DATE_FORMAT(registration_date, '%Y-%m')",
        };

        $summary = Registration::query()
            ->whereDate('registration_date', '>=', Carbon::now()->startOfMonth()->subMonths(5)->toDateString())
            ->selectRaw("{$monthExpression} as month_key, status, COUNT(*) as total")
            ->groupBy('month_key', 'status')
            ->get()
            ->groupBy('month_key')
            ->map(fn ($rows): array => $rows->pluck('total', 'status')
                ->map(fn ($total): int => (int) $total)
                ->all());

        $months = collect(range(5, 0))
            ->map(function (int $monthsAgo) use ($summary): array {
                $month = Carbon::now()->subMonths($monthsAgo);
                $counts = $summary->get($month->format('Y-m'), []);

                return [
                    'label' => $month->translatedFormat('M'),
                    'year' => $month->format('Y'),
                    'pending' => (int) ($counts[Registration::STATUS_PENDING] ?? 0),
                    'approved' => (int) ($counts[Registration::STATUS_APPROVED] ?? 0),
                    'rejected' => (int) ($counts[Registration::STATUS_REJECTED] ?? 0),
                    'cancelled' => (int) ($counts[Registration::STATUS_CANCELLED] ?? 0),
                ];
            })
            ->map(function (array $month): array {
                $month['total'] = $month['pending']
                    + $month['approved']
                    + $month['rejected']
                    + $month['cancelled'];

                return $month;
            });

        $maximum = max(1, (int) $months->max('total'));

        return [
            'months' => $months->map(function (array $month) use ($maximum): array {
                foreach (['pending', 'approved', 'rejected', 'cancelled'] as $status) {
                    $month["{$status}_height"] = round(($month[$status] / $maximum) * 100, 2);
                }

                return $month;
            })->all(),
            'maximum' => $maximum,
        ];
    }

    private function upcomingScheduleQuery()
    {
        return Schedule::query()
            ->where('status', 'scheduled')
            ->where(function ($query): void {
                $query->whereDate('activity_date', '>', Carbon::today())
                    ->orWhere(function ($todayQuery): void {
                        $todayQuery->whereDate('activity_date', Carbon::today())
                            ->whereTime('start_time', '>=', Carbon::now()->format('H:i:s'));
                    });
            })
            ->orderBy('activity_date')
            ->orderBy('start_time');
    }

    public function coach(): View
    {
        $coach = auth()->user()->coach;

        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $extracurricularIds = $coach->extracurriculars()->pluck('extracurriculars.id');
        $extracurricularIdList = $extracurricularIds->all();
        $pendingTalentAssessments = TalentTestParticipant::query()
            ->whereHas('schedule', function ($query) use ($extracurricularIdList): void {
                $query->where('schedule_type', 'talent_test')
                    ->whereIn('extracurricular_id', $extracurricularIdList);
            })
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('talent_test_results')
                    ->whereColumn('talent_test_results.schedule_id', 'talent_test_participants.schedule_id')
                    ->whereColumn('talent_test_results.student_id', 'talent_test_participants.student_id');
            })
            ->count();
        $registrationScope = Registration::query()->whereIn('extracurricular_id', $extracurricularIdList);
        $activeMemberships = (clone $registrationScope)
            ->where('status', Registration::STATUS_APPROVED)
            ->count();
        $activeStudents = (clone $registrationScope)
            ->where('status', Registration::STATUS_APPROVED)
            ->distinct()
            ->count('student_id');
        $pendingRegistrations = (clone $registrationScope)
            ->where('status', Registration::STATUS_PENDING)
            ->whereNull('cancellation_requested_at')
            ->where(function ($query): void {
                $query->whereNull('willing_to_take_test')->orWhere('willing_to_take_test', false);
            })
            ->count();
        $cancellationRequests = (clone $registrationScope)
            ->whereNotNull('cancellation_requested_at')
            ->count();
        $upcomingTalentTests = $this->upcomingScheduleQuery()
            ->whereIn('extracurricular_id', $extracurricularIdList)
            ->where('schedule_type', 'talent_test')
            ->count();
        $draftAnnouncements = Announcement::supportsEnhancedSchema()
            ? Announcement::where('published_by', $coach->user_id)
                ->where('publication_status', Announcement::STATUS_DRAFT)
                ->count()
            : Announcement::where('published_by', $coach->user_id)->where('is_active', false)->count();
        $actionItems = collect([
            [
                'label' => 'Pendaftaran menunggu',
                'description' => 'Pendaftar kegiatan binaan yang belum diperiksa.',
                'count' => $pendingRegistrations,
                'href' => route('coach.registrations.index', ['status' => Registration::STATUS_PENDING]),
                'icon' => 'bi-hourglass-split',
                'tone' => 'warning',
            ],
            [
                'label' => 'Permintaan pembatalan',
                'description' => 'Siswa yang menunggu keputusan pembatalan.',
                'count' => $cancellationRequests,
                'href' => route('coach.registrations.index', ['status' => Registration::DISPLAY_STATUS_CANCELLATION_REQUESTED]),
                'icon' => 'bi-person-x',
                'tone' => 'danger',
            ],
            [
                'label' => 'Hasil tes belum dinilai',
                'description' => 'Peserta tes kegiatan binaan tanpa hasil penilaian.',
                'count' => $pendingTalentAssessments,
                'href' => route('coach.talent-tests.index'),
                'icon' => 'bi-pencil-square',
                'tone' => 'warning',
            ],
            [
                'label' => 'Pengumuman draft',
                'description' => 'Pengumuman Anda yang belum dipublikasikan.',
                'count' => $draftAnnouncements,
                'href' => route('coach.announcements.index'),
                'icon' => 'bi-megaphone',
                'tone' => 'primary',
            ],
        ])->filter(fn (array $item): bool => $item['count'] > 0)->values()->all();

        return view('dashboard.coach', [
            'coach' => $coach,
            'totalExtracurriculars' => count($extracurricularIdList),
            'totalParticipants' => $activeMemberships,
            'activeStudents' => $activeStudents,
            'pendingRegistrations' => $pendingRegistrations,
            'upcomingTalentTests' => $upcomingTalentTests,
            'actionItems' => $actionItems,
            'upcomingSchedules' => $this->upcomingScheduleQuery()
                ->whereIn('extracurricular_id', $extracurricularIdList)
                ->with('extracurricular:id,name')
                ->limit(5)
                ->get(),
            'recentRegistrations' => Registration::with([
                'student.user',
                'extracurricular:id,name',
                'talentTestResults:id,registration_id,status,published_at',
                'talentTestParticipants:id,registration_id,schedule_id',
            ])
                ->whereIn('extracurricular_id', $extracurricularIdList)
                ->latest('created_at')
                ->latest('id')
                ->limit(5)
                ->get(),
            'recentAttendances' => Attendance::with(['student.user', 'schedule.extracurricular'])
                ->whereIn('extracurricular_id', $extracurricularIdList)
                ->latest('recorded_at')
                ->latest('id')
                ->limit(5)
                ->get(),
            'recentAnnouncements' => Announcement::with('extracurricular')
                ->where('published_by', $coach->user_id)
                ->latest()
                ->limit(4)
                ->get(),
            'dashboardUpdatedAt' => now(),
        ]);
    }

    public function student(): View
    {
        $student = auth()->user()->student;

        abort_unless($student, 404, 'Data siswa tidak ditemukan.');

        $registrationQuery = $student->registrations()
            ->where('status', '!=', Registration::STATUS_CANCELLED);
        $totalRegistrations = (clone $registrationQuery)->count();
        $approvedRegistrationCount = (clone $registrationQuery)
            ->where('status', Registration::STATUS_APPROVED)
            ->count();
        $pendingRegistrationCount = (clone $registrationQuery)
            ->where('status', Registration::STATUS_PENDING)
            ->count();
        $latestRegistration = (clone $registrationQuery)
            ->with('extracurricular')
            ->latest('registration_date')
            ->latest('id')
            ->first();

        $approvedExtracurricularIds = (clone $registrationQuery)
            ->where('status', Registration::STATUS_APPROVED)
            ->pluck('extracurricular_id');
        $approvedExtracurricularIdList = $approvedExtracurricularIds->all();

        $nextSchedule = Schedule::with('extracurricular')
            ->whereIn('extracurricular_id', $approvedExtracurricularIdList)
            ->where('status', 'scheduled')
            ->where(function ($query): void {
                $query->whereDate('activity_date', '>', Carbon::today())
                    ->orWhere(function ($todayQuery): void {
                        $todayQuery->whereDate('activity_date', Carbon::today())
                            ->whereTime('start_time', '>=', Carbon::now()->format('H:i:s'));
                    });
            })
            ->orderBy('activity_date')
            ->orderBy('start_time')
            ->first();

        $attendanceBreakdown = $student->attendances()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $totalAttendanceCount = (int) $attendanceBreakdown->sum();
        $effectivePresentCount = (int) (($attendanceBreakdown['present'] ?? 0) + ($attendanceBreakdown['permission'] ?? 0));
        $attendanceRate = $totalAttendanceCount > 0
            ? round(($effectivePresentCount / $totalAttendanceCount) * 100, 1)
            : 0.0;
        $notifications = $this->buildStudentNotifications(
            student: $student,
            latestRegistration: $latestRegistration,
            nextSchedule: $nextSchedule,
            attendanceRate: $attendanceRate,
            pendingCount: $pendingRegistrationCount,
            approvedCount: $approvedRegistrationCount
        );

        $upcomingTalentTests = TalentTestParticipant::with(['schedule.extracurricular'])
            ->where('student_id', $student->id)
            ->whereHas('schedule', function ($query): void {
                $query->where('schedule_type', 'talent_test')
                    ->where('status', 'scheduled')
                    ->where(function ($scheduleQuery): void {
                        $scheduleQuery->whereDate('activity_date', '>', Carbon::today())
                            ->orWhere(function ($todayQuery): void {
                                $todayQuery->whereDate('activity_date', Carbon::today())
                                    ->whereTime('start_time', '>=', Carbon::now()->format('H:i:s'));
                            });
                    });
            })
            ->orderBy(
                Schedule::select('activity_date')
                    ->whereColumn('schedules.id', 'talent_test_participants.schedule_id')
                    ->limit(1)
            )
            ->limit(3)
            ->get();

        return view('dashboard.student', [
            'student' => $student,
            'availableExtracurriculars' => Extracurricular::where('is_active', true)->count(),
            'totalRegistrations' => $totalRegistrations,
            'approvedRegistrations' => $approvedRegistrationCount,
            'upcomingSchedules' => $this->upcomingScheduleQuery()
                ->whereIn('extracurricular_id', $approvedExtracurricularIdList)
                ->count(),
            'latestRegistration' => $latestRegistration,
            'nextSchedule' => $nextSchedule,
            'notifications' => $notifications,
            'upcomingTalentTests' => $upcomingTalentTests,
            'recentAnnouncements' => Announcement::with(['publisher', 'extracurricular'])
                ->visibleToStudents()
                ->where(function ($query) use ($approvedExtracurricularIdList): void {
                    $query->whereNull('extracurricular_id')
                        ->orWhereIn('extracurricular_id', $approvedExtracurricularIdList);
                })
                ->latest()
                ->limit(5)
                ->get(),
            'dashboardUpdatedAt' => now(),
        ]);
    }

    private function buildStudentNotifications(
        Student $student,
        ?Registration $latestRegistration,
        ?Schedule $nextSchedule,
        float $attendanceRate,
        int $pendingCount,
        int $approvedCount
    ): array {
        $notifications = [];

        if ($pendingCount > 0) {
            $notifications[] = [
                'type' => 'warning',
                'icon' => 'bi-hourglass-split',
                'message' => "Ada {$pendingCount} pendaftaran yang masih menunggu verifikasi admin.",
            ];
        }

        if ($latestRegistration && $latestRegistration->status === Registration::STATUS_REJECTED) {
            $notifications[] = [
                'type' => 'danger',
                'icon' => 'bi-exclamation-octagon',
                'message' => 'Pendaftaran terakhir ditolak. Periksa catatan admin dan ajukan ulang bila perlu.',
            ];
        }

        if ($nextSchedule && $nextSchedule->activity_date?->diffInDays(Carbon::today()) <= 3) {
            $scheduleDate = $nextSchedule->activity_date?->translatedFormat('d M Y') ?? 'tanggal belum tersedia';
            $scheduleName = $nextSchedule->title ?: 'Kegiatan ekstrakurikuler';
            $extracurricularName = $nextSchedule->extracurricular->name ?? 'ekstrakurikuler Anda';

            $notifications[] = [
                'type' => 'success',
                'icon' => 'bi-calendar-event',
                'message' => "{$scheduleName} untuk {$extracurricularName} dijadwalkan pada {$scheduleDate}. Pastikan Anda siap hadir tepat waktu.",
            ];
        }

        if ($attendanceRate > 0 && $attendanceRate < 75) {
            $notifications[] = [
                'type' => 'danger',
                'icon' => 'bi-activity',
                'message' => "Persentase kehadiran Anda {$attendanceRate}%. Tingkatkan presensi agar pembinaan tetap optimal.",
            ];
        }

        if ($approvedCount === 0) {
            $notifications[] = [
                'type' => 'warning',
                'icon' => 'bi-grid-1x2',
                'message' => 'Anda belum memiliki ekstrakurikuler aktif. Silakan pilih dan daftar ekstrakurikuler yang tersedia.',
            ];
        }

        return $notifications;
    }
}
