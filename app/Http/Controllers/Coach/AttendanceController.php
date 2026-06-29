<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Registration;
use App\Models\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $schedules = Schedule::with('extracurricular.coaches.user')
            ->whereHas('extracurricular.coaches', fn ($query) => $query->whereKey($coach->id))
            ->orderByDesc('activity_date')
            ->get();

        $selectedSchedule = null;
        $participants = collect();
        $attendanceMap = collect();

        if ($request->filled('schedule_id')) {
            $selectedSchedule = Schedule::with('extracurricular.coaches.user')
                ->whereHas('extracurricular.coaches', fn ($query) => $query->whereKey($coach->id))
                ->findOrFail($request->integer('schedule_id'));

            $participants = Registration::with('student.user')
                ->where('extracurricular_id', $selectedSchedule->extracurricular_id)
                ->where('status', Registration::STATUS_APPROVED)
                ->get();

            $attendanceMap = Attendance::where('schedule_id', $selectedSchedule->id)
                ->get()
                ->keyBy('student_id');
        }

        return view('coach.attendances.index', [
            'schedules' => $schedules,
            'selectedSchedule' => $selectedSchedule,
            'participants' => $participants,
            'attendanceMap' => $attendanceMap,
        ]);
    }

    public function save(Request $request, Schedule $schedule): RedirectResponse
    {
        $this->authorize('manageByCoach', $schedule);
        $allowedStudentIds = Registration::where('extracurricular_id', $schedule->extracurricular_id)
            ->where('status', Registration::STATUS_APPROVED)
            ->pluck('student_id')
            ->all();

        $validated = $request->validate([
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.student_id' => ['required', Rule::in($allowedStudentIds)],
            'rows.*.status' => ['required', Rule::in(['present', 'absent', 'sick', 'permission'])],
            'rows.*.notes' => ['nullable', 'string'],
        ]);

        collect($validated['rows'])->each(function (array $row) use ($schedule): void {
            Attendance::updateOrCreate(
                [
                    'schedule_id' => $schedule->id,
                    'student_id' => $row['student_id'],
                ],
                [
                    'extracurricular_id' => $schedule->extracurricular_id,
                    'recorded_by' => auth()->id(),
                    'status' => $row['status'],
                    'notes' => $row['notes'] ?? null,
                    'recorded_at' => now(),
                ]
            );
        });

        return redirect()->route('coach.attendances.index', ['schedule_id' => $schedule->id])
            ->with('success', 'Presensi berhasil disimpan.');
    }

    public function export(Request $request): StreamedResponse
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $validated = $request->validate([
            'schedule_id' => ['nullable', 'integer'],
            'format' => ['nullable', Rule::in(['csv', 'xls'])],
        ]);

        $format = $validated['format'] ?? 'csv';
        $delimiter = $format === 'xls' ? "\t" : ',';
        $extension = $format === 'xls' ? 'xls' : 'csv';
        $scheduleId = $validated['schedule_id'] ?? null;

        $query = Attendance::with(['student.user', 'extracurricular', 'schedule'])
            ->whereHas('schedule.extracurricular.coaches', fn ($subQuery) => $subQuery->whereKey($coach->id))
            ->when($scheduleId, fn ($subQuery, $value) => $subQuery->where('schedule_id', $value))
            ->orderByDesc('recorded_at')
            ->orderByDesc('id');

        $filename = 'presensi-pembina-'.Carbon::now()->format('YmdHis').'.'.$extension;

        return response()->streamDownload(function () use ($query, $delimiter): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Siswa', 'Ekstrakurikuler', 'Jadwal', 'Tanggal', 'Status', 'Catatan'], $delimiter);

            $query->each(function (Attendance $row) use ($handle, $delimiter): void {
                fputcsv($handle, [
                    $row->student->user->name ?? '-',
                    $row->extracurricular->name ?? '-',
                    $row->schedule->title ?? '-',
                    optional($row->schedule->activity_date)->format('Y-m-d'),
                    $this->mapStatusLabel($row->status),
                    $row->notes ?? '-',
                ], $delimiter);
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
            'absent' => 'Alpa',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            default => $status,
        };
    }
}
