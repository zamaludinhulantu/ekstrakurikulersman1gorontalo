<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Extracurricular;
use App\Models\Registration;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $student = auth()->user()->student;
        abort_unless($student, 404, 'Data siswa tidak ditemukan.');

        $extracurricularId = $request->string('extracurricular_id')->toString();
        $allowedExtracurricularIds = Registration::where('student_id', $student->id)
            ->where('status', Registration::STATUS_APPROVED)
            ->pluck('extracurricular_id');

        $attendances = Attendance::with(['extracurricular', 'schedule'])
            ->where('student_id', $student->id)
            ->when($extracurricularId, fn ($query, $idValue) => $query->where('extracurricular_id', $idValue))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('student.attendances.index', [
            'attendances' => $attendances,
            'extracurricularId' => $extracurricularId,
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
            'format' => ['nullable', Rule::in(['csv', 'xls'])],
        ]);

        $format = $validated['format'] ?? 'csv';
        $delimiter = $format === 'xls' ? "\t" : ',';
        $extension = $format === 'xls' ? 'xls' : 'csv';
        $filename = 'presensi-siswa-'.Carbon::now()->format('YmdHis').'.'.$extension;

        $query = Attendance::with(['extracurricular', 'schedule'])
            ->where('student_id', $student->id)
            ->when($validated['extracurricular_id'] ?? null, fn ($subQuery, $value) => $subQuery->where('extracurricular_id', $value))
            ->orderByDesc('recorded_at')
            ->orderByDesc('id');

        return response()->streamDownload(function () use ($query, $delimiter): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Ekstrakurikuler', 'Jadwal', 'Tanggal', 'Status', 'Catatan'], $delimiter);

            $query->each(function (Attendance $row) use ($handle, $delimiter): void {
                fputcsv($handle, [
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
