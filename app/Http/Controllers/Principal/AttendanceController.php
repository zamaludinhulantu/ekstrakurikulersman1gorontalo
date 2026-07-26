<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Concerns\SanitizesCsvExports;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Coach;
use App\Models\Extracurricular;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    use SanitizesCsvExports;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'coach_id' => ['nullable', 'exists:coaches,id'],
            'status' => ['nullable', Rule::in(['present', 'absent', 'sick', 'permission'])],
            'class_name' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', Rule::in(['all', ...array_keys(Extracurricular::categoryDefinitions())])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
        $filters['class_name'] = Student::normalizeClassName($filters['class_name'] ?? null);
        $filters['category'] = $filters['category'] ?? 'all';

        $attendances = $this->buildAttendanceQuery($filters)
            ->latest('recorded_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('principal.attendances.index', [
            'attendances' => $attendances,
            'extracurriculars' => Extracurricular::orderBy('name')->get(),
            'coaches' => Coach::with('user')->orderBy('nip')->get(),
            'search' => $filters['search'] ?? '',
            'extracurricularId' => $filters['extracurricular_id'] ?? null,
            'coachId' => $filters['coach_id'] ?? null,
            'status' => $filters['status'] ?? null,
            'className' => $filters['class_name'] ?? '',
            'classOptions' => collect(array_keys(Student::registrationClassOptions())),
            'category' => $filters['category'] ?? 'all',
            'categories' => collect(Extracurricular::categoryDefinitions())
                ->map(fn (array $definition) => ['key' => $definition['key'], 'label' => $definition['label']])
                ->values(),
            'dateFrom' => $filters['date_from'] ?? null,
            'dateTo' => $filters['date_to'] ?? null,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'coach_id' => ['nullable', 'exists:coaches,id'],
            'status' => ['nullable', Rule::in(['present', 'absent', 'sick', 'permission'])],
            'class_name' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', Rule::in(['all', ...array_keys(Extracurricular::categoryDefinitions())])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'format' => ['nullable', Rule::in(['csv', 'xls'])],
        ]);
        $filters['class_name'] = Student::normalizeClassName($filters['class_name'] ?? null);
        $filters['category'] = $filters['category'] ?? 'all';

        $format = $filters['format'] ?? 'csv';
        $timestamp = Carbon::now()->format('YmdHis');
        $filenameBase = $this->buildFilename($filters);
        $rows = $this->buildAttendanceQuery($filters)
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->get();

        if ($format === 'xls') {
            $html = view('principal.attendances.export-xls', [
                'attendances' => $rows,
                'filterSummary' => $this->filterSummary($filters),
                'controller' => $this,
            ])->render();

            return response()->streamDownload(function () use ($html): void {
                echo "\xEF\xBB\xBF";
                echo $html;
            }, $filenameBase.'-'.$timestamp.'.xls', [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            ]);
        }

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Siswa', 'Ekstrakurikuler', 'Pembina', 'Jadwal', 'Tanggal', 'Status', 'Catatan']);

            $rows->each(function (Attendance $row) use ($handle): void {
                fputcsv($handle, $this->sanitizeExportRow([
                    $row->student->user->name ?? '-',
                    $row->extracurricular->name ?? '-',
                    $row->schedule->coach->user->name ?? $row->extracurricular->coach_names,
                    $row->schedule->title ?? '-',
                    optional($row->schedule->activity_date)->format('Y-m-d'),
                    $this->mapStatusLabel($row->status),
                    $row->notes ?? '-',
                ]));
            });

            fclose($handle);
        }, $filenameBase.'-'.$timestamp.'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
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
            ->when(($filters['category'] ?? 'all') !== 'all', function ($query) use ($filters): void {
                $ids = Extracurricular::idsForCategory($filters['category']);

                if ($ids === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->whereIn('extracurricular_id', $ids);
            })
            ->when($filters['coach_id'] ?? null, function ($query, $value): void {
                $query->where(function ($subQuery) use ($value): void {
                    $subQuery->whereHas('schedule', fn ($scheduleQuery) => $scheduleQuery->where('coach_id', $value))
                        ->orWhereHas('extracurricular.coaches', fn ($coachQuery) => $coachQuery->whereKey($value));
                });
            })
            ->when($filters['class_name'] ?? null, function ($query, $value): void {
                $query->whereHas('student', function ($studentQuery) use ($value): void {
                    $studentQuery->whereRaw(
                        Student::normalizedClassExpression('class_name').' = ?',
                        [Student::normalizedClassComparable($value)]
                    );
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['search'] ?? null, function ($query, $searchValue): void {
                $query->where(function ($attendanceQuery) use ($searchValue): void {
                    $attendanceQuery->whereHas('student.user', function ($userQuery) use ($searchValue): void {
                        $userQuery->where('name', 'like', "%{$searchValue}%");
                    })->orWhereHas('student', function ($studentQuery) use ($searchValue): void {
                        $studentQuery->where('nis', 'like', "%{$searchValue}%")
                            ->orWhere('class_name', 'like', "%{$searchValue}%");
                    })->orWhereHas('extracurricular', function ($activityQuery) use ($searchValue): void {
                        $activityQuery->where('name', 'like', "%{$searchValue}%");
                    })->orWhereHas('schedule', function ($scheduleQuery) use ($searchValue): void {
                        $scheduleQuery->where('title', 'like', "%{$searchValue}%")
                            ->orWhere('location', 'like', "%{$searchValue}%");
                    });
                });
            })
            ->when($filters['date_from'] ?? null, function ($query, $value): void {
                $query->whereHas('schedule', fn ($subQuery) => $subQuery->whereDate('activity_date', '>=', $value));
            })
            ->when($filters['date_to'] ?? null, function ($query, $value): void {
                $query->whereHas('schedule', fn ($subQuery) => $subQuery->whereDate('activity_date', '<=', $value));
            });
    }

    public function mapStatusLabel(string $status): string
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

    private function buildFilename(array $filters): string
    {
        $segments = ['presensi-kepsek'];

        if (($filters['category'] ?? 'all') !== 'all') {
            $definition = collect(Extracurricular::categoryDefinitions())->firstWhere('key', $filters['category']);
            if ($definition) {
                $segments[] = $definition['label'];
            }
        }

        if (! empty($filters['extracurricular_id'])) {
            $segments[] = Extracurricular::query()->find($filters['extracurricular_id'])?->name;
        }

        if (! empty($filters['coach_id'])) {
            $segments[] = Coach::with('user')->find($filters['coach_id'])?->user?->name;
        }

        if (! empty($filters['class_name'])) {
            $segments[] = 'kelas-'.$filters['class_name'];
        }

        if (! empty($filters['status'])) {
            $segments[] = $this->mapStatusLabel($filters['status']);
        }

        if (! empty($filters['date_from']) || ! empty($filters['date_to'])) {
            $segments[] = trim(implode('-', array_filter([
                $filters['date_from'] ?? null,
                $filters['date_to'] ?? null,
            ])), '-');
        }

        return Str::slug(implode('-', array_filter($segments)));
    }

    public function exportValue(mixed $value): string
    {
        return (string) ($value ?? '-');
    }

    private function filterSummary(array $filters): array
    {
        $categoryLabel = 'Semua kategori';
        if (($filters['category'] ?? 'all') !== 'all') {
            $definition = collect(Extracurricular::categoryDefinitions())->firstWhere('key', $filters['category']);
            $categoryLabel = $definition['label'] ?? $filters['category'];
        }

        return [
            'search' => $filters['search'] ?? 'Semua data',
            'extracurricular' => ! empty($filters['extracurricular_id'])
                ? (Extracurricular::query()->find($filters['extracurricular_id'])?->name ?? 'Tidak ditemukan')
                : 'Semua ekstrakurikuler',
            'coach' => ! empty($filters['coach_id'])
                ? (Coach::with('user')->find($filters['coach_id'])?->user?->name ?? 'Tidak ditemukan')
                : 'Semua pembina',
            'class_name' => $filters['class_name'] ?? 'Semua kelas',
            'status' => ! empty($filters['status']) ? $this->mapStatusLabel($filters['status']) : 'Semua status',
            'category' => $categoryLabel,
            'period' => trim(implode(' s.d. ', array_filter([
                ! empty($filters['date_from']) ? Carbon::parse($filters['date_from'])->format('d-m-Y') : null,
                ! empty($filters['date_to']) ? Carbon::parse($filters['date_to'])->format('d-m-Y') : null,
            ]))) ?: 'Semua tanggal',
        ];
    }
}
