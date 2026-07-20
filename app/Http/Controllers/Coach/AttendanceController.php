<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Concerns\SanitizesCsvExports;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Registration;
use App\Models\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    use SanitizesCsvExports;

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
                ->orderBy('created_at')
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
            'rows.*.status' => ['nullable', Rule::in(['present', 'late', 'absent', 'sick', 'permission'])],
            'rows.*.notes' => ['nullable', 'string'],
            'submit_action' => ['nullable', Rule::in(['draft', 'finalize'])],
        ]);

        $rows = collect($validated['rows'])
            ->map(function (array $row): array {
                return [
                    'student_id' => (int) $row['student_id'],
                    'status' => $row['status'] ?? null,
                    'notes' => trim((string) ($row['notes'] ?? '')),
                ];
            });

        $invalidNotes = $rows->contains(fn (array $row) => $row['status'] === null && $row['notes'] !== '');
        if ($invalidNotes) {
            throw ValidationException::withMessages([
                'rows' => 'Pilih status kehadiran sebelum menambahkan catatan presensi.',
            ]);
        }

        $existingStudentIds = Attendance::query()
            ->where('schedule_id', $schedule->id)
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $submitAction = $validated['submit_action'] ?? 'finalize';
        $timestamp = now();
        $supportsEnhancedSchema = $this->supportsEnhancedAttendanceSchema();
        $saveState = $submitAction === 'draft'
            ? Attendance::SAVE_STATE_DRAFT
            : Attendance::SAVE_STATE_FINALIZED;

        $upsertRows = [];
        $deleteStudentIds = [];

        foreach ($rows as $row) {
            if ($row['status'] === null) {
                if (in_array($row['student_id'], $existingStudentIds, true)) {
                    $deleteStudentIds[] = $row['student_id'];
                }

                continue;
            }

            $isLate = $row['status'] === 'late';
            $status = $isLate ? 'present' : $row['status'];
            $payload = [
                'schedule_id' => $schedule->id,
                'student_id' => $row['student_id'],
                'extracurricular_id' => $schedule->extracurricular_id,
                'recorded_by' => auth()->id(),
                'status' => $status,
                'notes' => $row['notes'] !== '' ? $row['notes'] : null,
                'recorded_at' => $timestamp,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            if ($supportsEnhancedSchema) {
                $payload['is_late'] = $isLate;
                $payload['save_state'] = $saveState;
                $payload['finalized_at'] = $submitAction === 'finalize' ? $timestamp : null;
            }

            $upsertRows[] = $payload;
        }

        DB::transaction(function () use ($schedule, $deleteStudentIds, $upsertRows, $supportsEnhancedSchema): void {
            if ($deleteStudentIds !== []) {
                Attendance::query()
                    ->where('schedule_id', $schedule->id)
                    ->whereIn('student_id', $deleteStudentIds)
                    ->delete();
            }

            if ($upsertRows !== []) {
                $updateColumns = ['extracurricular_id', 'recorded_by', 'status', 'notes', 'recorded_at', 'updated_at'];
                if ($supportsEnhancedSchema) {
                    $updateColumns = array_merge($updateColumns, ['is_late', 'save_state', 'finalized_at']);
                }

                Attendance::query()->upsert(
                    $upsertRows,
                    ['schedule_id', 'student_id'],
                    $updateColumns
                );
            }
        });

        return redirect()->route('coach.attendances.index', ['schedule_id' => $schedule->id])
            ->with('success', $submitAction === 'draft'
                ? 'Draft presensi berhasil disimpan.'
                : 'Presensi berhasil difinalisasi.');
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
                fputcsv($handle, $this->sanitizeExportRow([
                    $row->student->user->name ?? '-',
                    $row->extracurricular->name ?? '-',
                    $row->schedule->title ?? '-',
                    optional($row->schedule->activity_date)->format('Y-m-d'),
                    $row->display_status_label,
                    $row->notes ?? '-',
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
            'absent' => 'Tidak Hadir',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            default => $status,
        };
    }

    private function supportsEnhancedAttendanceSchema(): bool
    {
        return Schema::hasColumns('attendances', ['is_late', 'save_state', 'finalized_at']);
    }
}
