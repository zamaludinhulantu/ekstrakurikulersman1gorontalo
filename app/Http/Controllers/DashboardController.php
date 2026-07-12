<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\Coach;
use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        $user = auth()->user();

        return match ($user->role) {
            User::ROLE_ADMIN => redirect()->route('admin.dashboard'),
            User::ROLE_COACH => redirect()->route('coach.dashboard'),
            User::ROLE_STUDENT => redirect()->route('student.dashboard'),
            User::ROLE_PRINCIPAL => redirect()->route('principal.dashboard'),
            default => redirect()->route('login')->with('error', 'Role pengguna tidak dikenali.'),
        };
    }

    public function admin(): View
    {
        return view('dashboard.admin', [
            'totalUsers' => User::count(),
            'totalStudents' => Student::count(),
            'totalCoaches' => Coach::count(),
            'totalExtracurriculars' => Extracurricular::count(),
            'pendingRegistrations' => Registration::where('status', Registration::STATUS_PENDING)->count(),
            'approvedParticipants' => Registration::where('status', Registration::STATUS_APPROVED)->count(),
            'todaySchedules' => Schedule::whereDate('activity_date', Carbon::today())->count(),
            'attendanceCount' => Attendance::count(),
            'assessmentCount' => Assessment::count(),
            'recentRegistrations' => Registration::with(['student.user', 'extracurricular'])
                ->latest('registration_date')
                ->latest('id')
                ->limit(5)
                ->get(),
            'recentAnnouncements' => Announcement::with(['publisher', 'extracurricular'])
                ->where('is_active', true)
                ->latest()
                ->limit(4)
                ->get(),
        ]);
    }

    public function coach(): View
    {
        $coach = auth()->user()->coach;

        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $extracurricularIds = $coach->extracurriculars()->pluck('extracurriculars.id');
        $extracurricularIdList = $extracurricularIds->all();

        return view('dashboard.coach', [
            'coach' => $coach,
            'totalExtracurriculars' => count($extracurricularIdList),
            'totalParticipants' => Registration::whereIn('extracurricular_id', $extracurricularIdList)
                ->where('status', Registration::STATUS_APPROVED)
                ->count(),
            'todaySchedules' => Schedule::whereIn('extracurricular_id', $extracurricularIdList)
                ->whereDate('activity_date', Carbon::today())
                ->count(),
            'assessmentCount' => Assessment::whereIn('extracurricular_id', $extracurricularIdList)->count(),
            'recentAttendances' => Attendance::with(['student.user', 'schedule.extracurricular'])
                ->whereIn('extracurricular_id', $extracurricularIdList)
                ->latest('recorded_at')
                ->latest('id')
                ->limit(5)
                ->get(),
            'recentAssessments' => Assessment::with(['student.user', 'extracurricular'])
                ->whereIn('extracurricular_id', $extracurricularIdList)
                ->latest('assessment_date')
                ->latest('id')
                ->limit(5)
                ->get(),
            'recentAnnouncements' => Announcement::with('extracurricular')
                ->where('published_by', $coach->user_id)
                ->latest()
                ->limit(4)
                ->get(),
        ]);
    }

    public function student(): View
    {
        $student = auth()->user()->student;

        abort_unless($student, 404, 'Data siswa tidak ditemukan.');

        $registrationQuery = $student->registrations();
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

        $attendanceBaseQuery = $student->attendances();
        $assessmentBaseQuery = $student->assessments()->where('assessment_type', 'assessment');

        $recentAttendances = (clone $attendanceBaseQuery)
            ->with(['extracurricular', 'schedule'])
            ->latest('recorded_at')
            ->latest('id')
            ->limit(5)
            ->get();

        $recentAssessments = (clone $assessmentBaseQuery)
            ->with('extracurricular')
            ->latest('assessment_date')
            ->latest('id')
            ->limit(5)
            ->get();

        $nextSchedule = Schedule::with('extracurricular')
            ->whereIn('extracurricular_id', $approvedExtracurricularIdList)
            ->whereDate('activity_date', '>=', Carbon::today())
            ->orderBy('activity_date')
            ->orderBy('start_time')
            ->first();

        $attendanceBreakdown = (clone $attendanceBaseQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $totalAttendanceCount = (int) $attendanceBreakdown->sum();
        $effectivePresentCount = (int) (($attendanceBreakdown['present'] ?? 0) + ($attendanceBreakdown['permission'] ?? 0));
        $attendanceRate = $totalAttendanceCount > 0
            ? round(($effectivePresentCount / $totalAttendanceCount) * 100, 1)
            : 0.0;
        $averageAssessmentScore = round((float) ((clone $assessmentBaseQuery)->avg('score') ?? 0), 2);
        $attendanceBreakdown = [
            'present' => (int) ($attendanceBreakdown['present'] ?? 0),
            'permission' => (int) ($attendanceBreakdown['permission'] ?? 0),
            'sick' => (int) ($attendanceBreakdown['sick'] ?? 0),
            'absent' => (int) ($attendanceBreakdown['absent'] ?? 0),
        ];

        $monthlyAttendanceSummary = $this->buildMonthlyAttendanceSummary($attendanceBaseQuery);

        $monthlyAttendance = collect(range(5, 0, -1))
            ->map(function (int $monthsAgo) use ($monthlyAttendanceSummary): array {
                $month = Carbon::now()->subMonths($monthsAgo);
                $monthKey = $month->format('Y-m');

                return [
                    'label' => $month->translatedFormat('M Y'),
                    'total' => (int) ($monthlyAttendanceSummary[$monthKey]['total'] ?? 0),
                    'present' => (int) ($monthlyAttendanceSummary[$monthKey]['present'] ?? 0),
                ];
            })
            ->push([
                'label' => Carbon::now()->translatedFormat('M Y'),
                'total' => (int) ($monthlyAttendanceSummary[Carbon::now()->format('Y-m')]['total'] ?? 0),
                'present' => (int) ($monthlyAttendanceSummary[Carbon::now()->format('Y-m')]['present'] ?? 0),
            ]);

        $attendanceByExtracurricular = (clone $attendanceBaseQuery)
            ->selectRaw('extracurricular_id, COUNT(*) as total, SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present')
            ->groupBy('extracurricular_id')
            ->get()
            ->keyBy('extracurricular_id');
        $assessmentByExtracurricular = (clone $assessmentBaseQuery)
            ->selectRaw('extracurricular_id, COUNT(*) as total, AVG(score) as average_score')
            ->groupBy('extracurricular_id')
            ->get()
            ->keyBy('extracurricular_id');

        $performanceByExtracurricular = Registration::with('extracurricular:id,name')
            ->where('student_id', $student->id)
            ->where('status', Registration::STATUS_APPROVED)
            ->get()
            ->map(function (Registration $registration) use ($attendanceByExtracurricular, $assessmentByExtracurricular): array {
                $extracurricularId = $registration->extracurricular_id;
                $attendanceSummary = $attendanceByExtracurricular->get($extracurricularId);
                $assessmentSummary = $assessmentByExtracurricular->get($extracurricularId);
                $totalAttendances = (int) ($attendanceSummary->total ?? 0);
                $presentAttendances = (int) ($attendanceSummary->present ?? 0);

                return [
                    'name' => $registration->extracurricular->name ?? '-',
                    'attendance_rate' => $totalAttendances > 0 ? round(($presentAttendances / $totalAttendances) * 100, 1) : null,
                    'average_score' => isset($assessmentSummary->average_score)
                        ? round((float) $assessmentSummary->average_score, 2)
                        : null,
                    'assessment_total' => (int) ($assessmentSummary->total ?? 0),
                ];
            });

        $notifications = $this->buildStudentNotifications(
            student: $student,
            latestRegistration: $latestRegistration,
            nextSchedule: $nextSchedule,
            attendanceRate: $attendanceRate,
            pendingCount: $pendingRegistrationCount,
            approvedCount: $approvedRegistrationCount
        );

        return view('dashboard.student', [
            'student' => $student,
            'availableExtracurriculars' => Extracurricular::where('is_active', true)->count(),
            'totalRegistrations' => $totalRegistrations,
            'approvedRegistrations' => $approvedRegistrationCount,
            'upcomingSchedules' => Schedule::whereIn('extracurricular_id', $approvedExtracurricularIdList)
                ->whereDate('activity_date', '>=', Carbon::today())
                ->count(),
            'attendanceCount' => $totalAttendanceCount,
            'assessmentCount' => (clone $assessmentBaseQuery)->count(),
            'latestRegistration' => $latestRegistration,
            'nextSchedule' => $nextSchedule,
            'recentAttendances' => $recentAttendances,
            'recentAssessments' => $recentAssessments,
            'attendanceRate' => $attendanceRate,
            'averageAssessmentScore' => $averageAssessmentScore,
            'attendanceBreakdown' => $attendanceBreakdown,
            'monthlyAttendance' => $monthlyAttendance,
            'performanceByExtracurricular' => $performanceByExtracurricular,
            'notifications' => $notifications,
            'recentAnnouncements' => Announcement::with(['publisher', 'extracurricular'])
                ->where('is_active', true)
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    private function buildMonthlyAttendanceSummary(HasMany $attendanceBaseQuery): array
    {
        return (clone $attendanceBaseQuery)
            ->where('recorded_at', '>=', Carbon::now()->startOfMonth()->subMonths(5))
            ->get(['recorded_at', 'status'])
            ->groupBy(fn (Attendance $attendance): string => Carbon::parse($attendance->recorded_at)->format('Y-m'))
            ->map(fn ($records): array => [
                'total' => $records->count(),
                'present' => $records->where('status', 'present')->count(),
            ])
            ->all();
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
