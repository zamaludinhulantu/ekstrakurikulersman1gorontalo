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
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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

        return view('dashboard.coach', [
            'coach' => $coach,
            'totalExtracurriculars' => $coach->extracurriculars()->count(),
            'totalParticipants' => Registration::whereIn('extracurricular_id', $extracurricularIds)
                ->where('status', Registration::STATUS_APPROVED)
                ->count(),
            'todaySchedules' => Schedule::whereIn('extracurricular_id', $extracurricularIds)
                ->whereDate('activity_date', Carbon::today())
                ->count(),
            'assessmentCount' => Assessment::whereIn('extracurricular_id', $extracurricularIds)->count(),
            'recentAttendances' => Attendance::with(['student.user', 'schedule.extracurricular'])
                ->whereIn('extracurricular_id', $extracurricularIds)
                ->latest('recorded_at')
                ->latest('id')
                ->limit(5)
                ->get(),
            'recentAssessments' => Assessment::with(['student.user', 'extracurricular'])
                ->whereIn('extracurricular_id', $extracurricularIds)
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

        $registrations = $student->registrations()
            ->with('extracurricular')
            ->latest('registration_date')
            ->latest('id')
            ->get();

        $approvedRegistrations = $registrations->where('status', Registration::STATUS_APPROVED);
        $approvedRegistrationCount = $approvedRegistrations->count();
        $pendingRegistrationCount = $registrations->where('status', Registration::STATUS_PENDING)->count();
        $latestRegistration = $registrations->first();
        $approvedExtracurricularIds = $approvedRegistrations
            ->where('status', Registration::STATUS_APPROVED)
            ->pluck('extracurricular_id');

        $attendanceRecords = $student->attendances()
            ->with(['extracurricular', 'schedule'])
            ->latest('recorded_at')
            ->latest('id')
            ->get();

        $assessmentRecords = $student->assessments()
            ->with('extracurricular')
            ->latest('assessment_date')
            ->latest('id')
            ->get();

        $nextSchedule = Schedule::with('extracurricular')
            ->whereIn('extracurricular_id', $approvedExtracurricularIds)
            ->whereDate('activity_date', '>=', Carbon::today())
            ->orderBy('activity_date')
            ->orderBy('start_time')
            ->first();

        $attendanceRate = $this->calculateAttendanceRate($attendanceRecords);
        $averageAssessmentScore = round((float) ($assessmentRecords->avg('score') ?? 0), 2);
        $attendanceBreakdown = [
            'present' => $attendanceRecords->where('status', 'present')->count(),
            'permission' => $attendanceRecords->where('status', 'permission')->count(),
            'sick' => $attendanceRecords->where('status', 'sick')->count(),
            'absent' => $attendanceRecords->where('status', 'absent')->count(),
        ];

        $monthlyAttendance = collect(range(5, 0, -1))
            ->map(function (int $monthsAgo) use ($attendanceRecords): array {
                $month = Carbon::now()->subMonths($monthsAgo);
                $monthAttendances = $attendanceRecords->filter(
                    fn ($attendance) => optional($attendance->recorded_at)->format('Y-m') === $month->format('Y-m')
                );

                return [
                    'label' => $month->translatedFormat('M Y'),
                    'total' => $monthAttendances->count(),
                    'present' => $monthAttendances->where('status', 'present')->count(),
                ];
            })
            ->push([
                'label' => Carbon::now()->translatedFormat('M Y'),
                'total' => $attendanceRecords->filter(
                    fn ($attendance) => optional($attendance->recorded_at)->format('Y-m') === Carbon::now()->format('Y-m')
                )->count(),
                'present' => $attendanceRecords->filter(
                    fn ($attendance) => optional($attendance->recorded_at)->format('Y-m') === Carbon::now()->format('Y-m')
                )->where('status', 'present')->count(),
            ]);

        $performanceByExtracurricular = Registration::with('extracurricular')
            ->where('student_id', $student->id)
            ->where('status', Registration::STATUS_APPROVED)
            ->get()
            ->map(function (Registration $registration) use ($attendanceRecords, $assessmentRecords): array {
                $extracurricularId = $registration->extracurricular_id;
                $extracurricularAttendances = $attendanceRecords->where('extracurricular_id', $extracurricularId);
                $extracurricularAssessments = $assessmentRecords->where('extracurricular_id', $extracurricularId);
                $totalAttendances = $extracurricularAttendances->count();
                $presentAttendances = $extracurricularAttendances->where('status', 'present')->count();

                return [
                    'name' => $registration->extracurricular->name ?? '-',
                    'attendance_rate' => $totalAttendances > 0 ? round(($presentAttendances / $totalAttendances) * 100, 1) : null,
                    'average_score' => $extracurricularAssessments->count() > 0
                        ? round((float) $extracurricularAssessments->avg('score'), 2)
                        : null,
                    'assessment_total' => $extracurricularAssessments->count(),
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
            'totalRegistrations' => $registrations->count(),
            'approvedRegistrations' => $approvedRegistrationCount,
            'upcomingSchedules' => Schedule::whereIn('extracurricular_id', $approvedExtracurricularIds)
                ->whereDate('activity_date', '>=', Carbon::today())
                ->count(),
            'attendanceCount' => $student->attendances()->count(),
            'assessmentCount' => $student->assessments()->count(),
            'latestRegistration' => $latestRegistration,
            'nextSchedule' => $nextSchedule,
            'recentAttendances' => $attendanceRecords->take(5),
            'recentAssessments' => $assessmentRecords->take(5),
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

    private function calculateAttendanceRate(Collection $attendanceRecords): float
    {
        $total = $attendanceRecords->count();
        if ($total === 0) {
            return 0.0;
        }

        $effectivePresent = $attendanceRecords->whereIn('status', ['present', 'permission'])->count();

        return round(($effectivePresent / $total) * 100, 1);
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
