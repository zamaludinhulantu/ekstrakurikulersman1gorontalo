<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Concerns\SanitizesCsvExports;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Coach;
use App\Models\Extracurricular;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    use SanitizesCsvExports;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'coach_id' => ['nullable', 'exists:coaches,id'],
            'status' => ['nullable', Rule::in(['present', 'absent', 'sick', 'permission'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $attendances = $this->buildAttendanceQuery($filters)
            ->latest('recorded_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('principal.attendances.index', [
            'attendances' => $attendances,
            'extracurriculars' => Extracurricular::orderBy('name')->get(),
            'coaches' => Coach::with('user')->orderBy('nip')->get(),
            'extracurricularId' => $filters['extracurricular_id'] ?? null,
            'coachId' => $filters['coach_id'] ?? null,
            'status' => $filters['status'] ?? null,
            'dateFrom' => $filters['date_from'] ?? null,
            'dateTo' => $filters['date_to'] ?? null,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'coach_id' => ['nullable', 'exists:coaches,id'],
            'status' => ['nullable', Rule::in(['present', 'absent', 'sick', 'permission'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'format' => ['nullable', Rule::in(['csv', 'xls'])],
        ]);

        $format = $filters['format'] ?? 'csv';
        $delimiter = $format === 'xls' ? "\t" : ',';
        $extension = $format === 'xls' ? 'xls' : 'csv';
        $filename = 'presensi-kepala-sekolah-'.Carbon::now()->format('YmdHis').'.'.$extension;

        return response()->streamDownload(function () use ($filters, $delimiter): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Siswa', 'Ekstrakurikuler', 'Pembina', 'Jadwal', 'Tanggal', 'Status', 'Catatan'], $delimiter);

            $this->buildAttendanceQuery($filters)
                ->orderByDesc('recorded_at')
                ->orderByDesc('id')
                ->each(function (Attendance $row) use ($handle, $delimiter): void {
                    fputcsv($handle, $this->sanitizeExportRow([
                        $row->student->user->name ?? '-',
                        $row->extracurricular->name ?? '-',
                        $row->schedule->coach->user->name ?? $row->extracurricular->coach_names,
                        $row->schedule->title ?? '-',
                        optional($row->schedule->activity_date)->format('Y-m-d'),
                        $this->mapStatusLabel($row->status),
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

    private function buildAttendanceQuery(array $filters)
    {
        return Attendance::with(['student.user', 'extracurricular.coaches.user', 'extracurricular.coach.user', 'schedule.coach.user'])
            ->where(function ($query): void {
                $query->whereNull('save_state')
                    ->orWhere('save_state', Attendance::SAVE_STATE_FINALIZED);
            })
            ->when($filters['extracurricular_id'] ?? null, fn ($query, $value) => $query->where('extracurricular_id', $value))
            ->when($filters['coach_id'] ?? null, function ($query, $value): void {
                $query->where(function ($subQuery) use ($value): void {
                    $subQuery->whereHas('schedule', fn ($scheduleQuery) => $scheduleQuery->where('coach_id', $value))
                        ->orWhereHas('extracurricular.coaches', fn ($coachQuery) => $coachQuery->whereKey($value));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['date_from'] ?? null, function ($query, $value): void {
                $query->whereHas('schedule', fn ($subQuery) => $subQuery->whereDate('activity_date', '>=', $value));
            })
            ->when($filters['date_to'] ?? null, function ($query, $value): void {
                $query->whereHas('schedule', fn ($subQuery) => $subQuery->whereDate('activity_date', '<=', $value));
            });
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
}
