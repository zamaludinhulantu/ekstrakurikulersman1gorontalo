<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Concerns\SanitizesCsvExports;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    use SanitizesCsvExports;

    public function index(Request $request): View
    {
        $student = auth()->user()->student;
        abort_unless($student, 404, 'Data siswa tidak ditemukan.');

        $extracurricularId = $request->string('extracurricular_id')->toString();
        $month = $request->string('month')->toString();
        $year = $request->string('year')->toString();
        $status = $request->string('status')->toString();
        $period = $request->string('period')->toString() ?: 'all';
        $allowedExtracurricularIds = Registration::where('student_id', $student->id)
            ->where('status', Registration::STATUS_APPROVED)
            ->pluck('extracurricular_id');

        $baseRecords = $this->buildAttendanceRecords(
            studentId: $student->id,
            extracurricularIds: $allowedExtracurricularIds->all(),
            extracurricularId: $extracurricularId,
            month: $month,
            year: $year,
            period: $period
        );

        $attendanceSummary = [
            'total' => $baseRecords->count(),
            'present' => $baseRecords->where('student_attendance_status', 'present')->count(),
            'late' => $baseRecords->where('student_attendance_status', 'late')->count(),
            'permission' => $baseRecords->where('student_attendance_status', 'permission')->count(),
            'sick' => $baseRecords->where('student_attendance_status', 'sick')->count(),
            'absent' => $baseRecords->where('student_attendance_status', 'absent')->count(),
            'pending' => $baseRecords->where('student_attendance_status', 'pending')->count(),
        ];

        $effectivePresentCount = $attendanceSummary['present'] + $attendanceSummary['late'];
        $attendanceRate = $attendanceSummary['total'] > 0
            ? round(($effectivePresentCount / $attendanceSummary['total']) * 100, 1)
            : 0.0;

        $records = $baseRecords
            ->when($status, fn (Collection $items) => $items->where('student_attendance_status', $status))
            ->values();

        $attendances = $this->paginateCollection($records, 10, 'page', $request);

        $monthOptions = Schedule::whereIn('extracurricular_id', $allowedExtracurricularIds)
            ->whereNull('cancelled_at')
            ->orderBy('activity_date')
            ->get()
            ->map(fn (Schedule $schedule) => optional($schedule->activity_date)->format('m'))
            ->filter()
            ->unique()
            ->values();

        $yearOptions = Schedule::whereIn('extracurricular_id', $allowedExtracurricularIds)
            ->whereNull('cancelled_at')
            ->orderByDesc('activity_date')
            ->get()
            ->map(fn (Schedule $schedule) => optional($schedule->activity_date)->format('Y'))
            ->filter()
            ->unique()
            ->values();

        return view('student.attendances.index', [
            'attendances' => $attendances,
            'extracurricularId' => $extracurricularId,
            'month' => $month,
            'year' => $year,
            'status' => $status,
            'period' => in_array($period, ['month', 'semester', 'all'], true) ? $period : 'all',
            'attendanceSummary' => $attendanceSummary,
            'attendanceRate' => $attendanceRate,
            'monthOptions' => $monthOptions,
            'yearOptions' => $yearOptions,
            'extracurriculars' => Extracurricular::whereIn('id', $allowedExtracurricularIds)->orderBy('name')->get(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $student = auth()->user()->student;
        abort_unless($student, 404, 'Data siswa tidak ditemukan.');

        $allowedExtracurricularIds = Registration::where('student_id', $student->id)
            ->where('status', Registration::STATUS_APPROVED)
            ->pluck('extracurricular_id')
            ->all();

        $validated = $request->validate([
            'extracurricular_id' => ['nullable', Rule::in($allowedExtracurricularIds)],
            'month' => ['nullable', 'digits:2'],
            'year' => ['nullable', 'digits:4'],
            'status' => ['nullable', Rule::in(['present', 'late', 'permission', 'sick', 'absent', 'pending'])],
            'period' => ['nullable', Rule::in(['month', 'semester', 'all'])],
            'format' => ['nullable', Rule::in(['csv', 'xls'])],
        ]);

        $format = $validated['format'] ?? 'csv';
        $delimiter = $format === 'xls' ? "\t" : ',';
        $extension = $format === 'xls' ? 'xls' : 'csv';
        $filename = 'presensi-siswa-'.Carbon::now()->format('YmdHis').'.'.$extension;

        $records = $this->buildAttendanceRecords(
            studentId: $student->id,
            extracurricularIds: $allowedExtracurricularIds,
            extracurricularId: (string) ($validated['extracurricular_id'] ?? ''),
            month: (string) ($validated['month'] ?? ''),
            year: (string) ($validated['year'] ?? ''),
            period: (string) ($validated['period'] ?? 'all')
        )->when($validated['status'] ?? null, fn (Collection $items, $value) => $items->where('student_attendance_status', $value))->values();

        return response()->streamDownload(function () use ($records, $delimiter): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Ekstrakurikuler', 'Nama Kegiatan', 'Waktu', 'Status', 'Catatan'], $delimiter);

            $records->each(function (Schedule $row) use ($handle, $delimiter): void {
                fputcsv($handle, $this->sanitizeExportRow([
                    optional($row->activity_date)->format('Y-m-d'),
                    $row->extracurricular->name ?? '-',
                    $row->title ?? '-',
                    trim(substr((string) $row->start_time, 0, 5).' - '.substr((string) $row->end_time, 0, 5)),
                    $row->student_attendance_label,
                    $row->student_attendance_notes ?? '-',
                ]), $delimiter);
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => $format === 'xls'
                ? 'application/vnd.ms-excel; charset=UTF-8'
                : 'text/csv; charset=UTF-8',
        ]);
    }

    private function mapStatusLabel(string $status): string
    {
        return match ($status) {
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Alpa',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            'pending' => 'Belum dicatat',
            default => $status,
        };
    }

    private function buildAttendanceRecords(
        int $studentId,
        array $extracurricularIds,
        string $extracurricularId = '',
        string $month = '',
        string $year = '',
        string $period = 'all'
    ): Collection {
        $now = now();
        $semesterMonths = $now->month <= 6 ? [1, 2, 3, 4, 5, 6] : [7, 8, 9, 10, 11, 12];

        $schedules = Schedule::with('extracurricular')
            ->whereIn('extracurricular_id', $extracurricularIds)
            ->whereNull('cancelled_at')
            ->when($extracurricularId, fn ($query, $value) => $query->where('extracurricular_id', $value))
            ->when($period === 'month', fn ($query) => $query->whereMonth('activity_date', $now->month)->whereYear('activity_date', $now->year))
            ->when($period === 'semester', fn ($query) => $query->whereYear('activity_date', $now->year)->whereIn(DB::raw('MONTH(activity_date)'), $semesterMonths))
            ->when($month, fn ($query, $value) => $query->whereMonth('activity_date', (int) $value))
            ->when($year, fn ($query, $value) => $query->whereYear('activity_date', (int) $value))
            ->orderByDesc('activity_date')
            ->orderByDesc('start_time')
            ->get();

        $attendanceMap = Attendance::where('student_id', $studentId)
            ->when(Schema::hasColumn('attendances', 'save_state'), function ($query): void {
                $query->where(function ($subQuery): void {
                    $subQuery->whereNull('save_state')
                        ->orWhere('save_state', Attendance::SAVE_STATE_FINALIZED);
                });
            })
            ->whereIn('schedule_id', $schedules->pluck('id'))
            ->get()
            ->keyBy('schedule_id');

        return $schedules->map(function (Schedule $schedule) use ($attendanceMap): Schedule {
            $attendance = $attendanceMap->get($schedule->id);
            $status = $attendance?->display_status ?: 'pending';

            $schedule->setAttribute('student_attendance_status', $status);
            $schedule->setAttribute('student_attendance_label', $this->mapStatusLabel($status));
            $schedule->setAttribute('student_attendance_notes', $attendance?->notes);

            return $schedule;
        });
    }

    private function paginateCollection(Collection $items, int $perPage, string $pageName, Request $request): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return tap(new LengthAwarePaginator(
            $slice,
            $items->count(),
            $perPage,
            $page,
            ['pageName' => $pageName, 'path' => $request->url(), 'query' => $request->query()]
        ), function (LengthAwarePaginator $paginator): void {
            $paginator->withQueryString();
        });
    }
}
