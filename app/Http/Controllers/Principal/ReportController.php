<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Concerns\SanitizesCsvExports;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\Coach;
use App\Models\Extracurricular;
use App\Models\ExtracurricularAchievement;
use App\Models\Registration;
use App\Models\Schedule;
use App\Models\Student;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    use SanitizesCsvExports;

    private const REPORT_TYPES = ['members', 'registrations', 'attendances', 'achievements', 'activities'];

    public function index(Request $request): View
    {
        $filters = $this->validateFilters($request);
        $reportType = $filters['report_type'] ?? 'members';

        [$rows, $columns, $title, $description] = $this->reportPayload($reportType, $filters);

        return view('principal.reports.index', [
            'filters' => $filters,
            'reportType' => $reportType,
            'reportOptions' => $this->reportOptions(),
            'rows' => $this->paginateCollection($rows, 12, 'report_page'),
            'columns' => $columns,
            'reportTitle' => $title,
            'reportDescription' => $description,
            'extracurriculars' => Extracurricular::orderBy('name')->get(['id', 'name']),
            'classOptions' => collect(array_keys(Student::registrationClassOptions())),
            'schoolYearOptions' => $this->schoolYearOptions(),
        ]);
    }

    public function export(Request $request, string $type): StreamedResponse
    {
        abort_unless(in_array($type, self::REPORT_TYPES, true), 404);

        $filters = $this->validateFilters($request, true);
        [$rows, $columns, $title] = $this->reportPayload($type, $filters);
        $format = $filters['format'] ?? 'xls';
        $timestamp = now()->format('YmdHis');
        $filename = Str::slug($title).'-'.$timestamp;

        if ($format === 'pdf') {
            $html = view('principal.reports.print', [
                'rows' => $rows,
                'columns' => $columns,
                'reportTitle' => $title,
                'filters' => $filters,
                'reportOptions' => $this->reportOptions(),
                'printMode' => false,
            ])->render();

            $options = new Options();
            $options->set('isRemoteEnabled', false);
            $options->set('defaultFont', 'DejaVu Sans');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            return response()->streamDownload(
                static function () use ($dompdf): void {
                    echo $dompdf->output();
                },
                $filename.'.pdf',
                ['Content-Type' => 'application/pdf']
            );
        }

        $html = view('principal.reports.print', [
            'rows' => $rows,
            'columns' => $columns,
            'reportTitle' => $title,
            'filters' => $filters,
            'reportOptions' => $this->reportOptions(),
            'printMode' => false,
        ])->render();

        return response()->streamDownload(function () use ($html): void {
            echo "\xEF\xBB\xBF";
            echo $html;
        }, $filename.'.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function print(Request $request, string $type): View
    {
        abort_unless(in_array($type, self::REPORT_TYPES, true), 404);

        $filters = $this->validateFilters($request);
        [$rows, $columns, $title] = $this->reportPayload($type, $filters);

        return view('principal.reports.print', [
            'rows' => $rows,
            'columns' => $columns,
            'reportTitle' => $title,
            'filters' => $filters,
            'reportOptions' => $this->reportOptions(),
            'printMode' => true,
        ]);
    }

    private function reportPayload(string $type, array $filters): array
    {
        return match ($type) {
            'members' => [
                $this->memberRows($filters),
                [
                    ['key' => 'student', 'label' => 'Siswa'],
                    ['key' => 'class_name', 'label' => 'Kelas'],
                    ['key' => 'gender', 'label' => 'JK'],
                    ['key' => 'extracurricular', 'label' => 'Ekstrakurikuler'],
                    ['key' => 'coach', 'label' => 'Pembina'],
                    ['key' => 'joined_at', 'label' => 'Tanggal Bergabung'],
                ],
                'Laporan Data Anggota',
                'Rekap siswa yang sudah aktif menjadi anggota ekstrakurikuler.',
            ],
            'registrations' => [
                $this->registrationRows($filters),
                [
                    ['key' => 'student', 'label' => 'Siswa'],
                    ['key' => 'class_name', 'label' => 'Kelas'],
                    ['key' => 'gender', 'label' => 'JK'],
                    ['key' => 'extracurricular', 'label' => 'Ekstrakurikuler'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'registered_at', 'label' => 'Tanggal Daftar'],
                ],
                'Laporan Pendaftaran',
                'Pantau seluruh status pendaftaran siswa per ekstrakurikuler.',
            ],
            'attendances' => [
                $this->attendanceRows($filters),
                [
                    ['key' => 'student', 'label' => 'Siswa'],
                    ['key' => 'class_name', 'label' => 'Kelas'],
                    ['key' => 'extracurricular', 'label' => 'Ekstrakurikuler'],
                    ['key' => 'schedule', 'label' => 'Kegiatan'],
                    ['key' => 'date', 'label' => 'Tanggal'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
                'Laporan Kehadiran',
                'Rekap presensi dan kedisiplinan siswa pada setiap kegiatan.',
            ],
            'achievements' => [
                $this->achievementRows($filters),
                [
                    ['key' => 'title', 'label' => 'Nama Lomba / Prestasi'],
                    ['key' => 'extracurricular', 'label' => 'Ekstrakurikuler'],
                    ['key' => 'student', 'label' => 'Siswa'],
                    ['key' => 'level', 'label' => 'Tingkat'],
                    ['key' => 'result', 'label' => 'Hasil'],
                    ['key' => 'date', 'label' => 'Tanggal'],
                    ['key' => 'documentation', 'label' => 'Dokumentasi'],
                ],
                'Laporan Prestasi',
                'Daftar prestasi siswa dan ekstrakurikuler yang tercatat di sistem.',
            ],
            default => [
                $this->activityRows($filters),
                [
                    ['key' => 'title', 'label' => 'Kegiatan'],
                    ['key' => 'extracurricular', 'label' => 'Ekstrakurikuler'],
                    ['key' => 'coach', 'label' => 'Pembina'],
                    ['key' => 'type', 'label' => 'Jenis'],
                    ['key' => 'date', 'label' => 'Tanggal'],
                    ['key' => 'location', 'label' => 'Lokasi'],
                ],
                'Laporan Kegiatan',
                'Agenda latihan, seleksi, lomba, dan kegiatan lainnya.',
            ],
        };
    }

    private function memberRows(array $filters): Collection
    {
        return Registration::with(['student.user', 'extracurricular.coaches.user', 'extracurricular.coach.user'])
            ->where('status', Registration::STATUS_APPROVED)
            ->when($filters['extracurricular_id'] ?? null, fn ($query, $value) => $query->where('extracurricular_id', $value))
            ->when($filters['class_name'] ?? null, function ($query, $value): void {
                $query->whereHas('student', function ($studentQuery) use ($value): void {
                    $studentQuery->whereRaw(Student::normalizedClassExpression('class_name').' = ?', [Student::normalizedClassComparable($value)]);
                });
            })
            ->when($filters['gender'] ?? null, fn ($query, $value) => $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('gender', $value)))
            ->tap(fn ($query) => $this->applySchoolYearWindow($query, 'registration_date', $filters))
            ->latest('registration_date')
            ->get()
            ->map(fn (Registration $row) => [
                'student' => $row->student->user->name ?? '-',
                'class_name' => $row->student->class_name ?? '-',
                'gender' => $this->genderLabel($row->student->gender ?? null),
                'extracurricular' => $row->extracurricular->name ?? '-',
                'coach' => $row->extracurricular->coach_names,
                'joined_at' => optional($row->registration_date)->format('d-m-Y') ?: '-',
            ]);
    }

    private function registrationRows(array $filters): Collection
    {
        return Registration::with(['student.user', 'extracurricular'])
            ->when($filters['extracurricular_id'] ?? null, fn ($query, $value) => $query->where('extracurricular_id', $value))
            ->when($filters['class_name'] ?? null, function ($query, $value): void {
                $query->whereHas('student', function ($studentQuery) use ($value): void {
                    $studentQuery->whereRaw(Student::normalizedClassExpression('class_name').' = ?', [Student::normalizedClassComparable($value)]);
                });
            })
            ->when($filters['gender'] ?? null, fn ($query, $value) => $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('gender', $value)))
            ->tap(fn ($query) => $this->applySchoolYearWindow($query, 'registration_date', $filters))
            ->latest('registration_date')
            ->get()
            ->map(fn (Registration $row) => [
                'student' => $row->student->user->name ?? '-',
                'class_name' => $row->student->class_name ?? '-',
                'gender' => $this->genderLabel($row->student->gender ?? null),
                'extracurricular' => $row->extracurricular->name ?? '-',
                'status' => $this->registrationStatusLabel($row->status),
                'registered_at' => optional($row->registration_date)->format('d-m-Y') ?: '-',
            ]);
    }

    private function attendanceRows(array $filters): Collection
    {
        return Attendance::with(['student.user', 'extracurricular.coaches.user', 'extracurricular.coach.user', 'schedule'])
            ->where(function ($query): void {
                $query->whereNull('save_state')->orWhere('save_state', Attendance::SAVE_STATE_FINALIZED);
            })
            ->when($filters['extracurricular_id'] ?? null, fn ($query, $value) => $query->where('extracurricular_id', $value))
            ->when($filters['class_name'] ?? null, function ($query, $value): void {
                $query->whereHas('student', function ($studentQuery) use ($value): void {
                    $studentQuery->whereRaw(Student::normalizedClassExpression('class_name').' = ?', [Student::normalizedClassComparable($value)]);
                });
            })
            ->when($filters['gender'] ?? null, fn ($query, $value) => $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('gender', $value)))
            ->tap(fn ($query) => $this->applyRelatedSchoolYearWindow($query, 'schedule', 'activity_date', $filters))
            ->latest('recorded_at')
            ->get()
            ->map(fn (Attendance $row) => [
                'student' => $row->student->user->name ?? '-',
                'class_name' => $row->student->class_name ?? '-',
                'extracurricular' => $row->extracurricular->name ?? '-',
                'schedule' => $row->schedule->title ?? '-',
                'date' => optional($row->schedule->activity_date)->format('d-m-Y') ?: '-',
                'status' => $row->display_status_label,
            ]);
    }

    private function achievementRows(array $filters): Collection
    {
        $assessmentAchievements = Assessment::with(['student.user', 'extracurricular'])
            ->where('assessment_type', 'achievement')
            ->when($filters['extracurricular_id'] ?? null, fn ($query, $value) => $query->where('extracurricular_id', $value))
            ->when($filters['class_name'] ?? null, function ($query, $value): void {
                $query->whereHas('student', function ($studentQuery) use ($value): void {
                    $studentQuery->whereRaw(Student::normalizedClassExpression('class_name').' = ?', [Student::normalizedClassComparable($value)]);
                });
            })
            ->when($filters['gender'] ?? null, fn ($query, $value) => $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('gender', $value)))
            ->tap(fn ($query) => $this->applySchoolYearWindow($query, 'assessment_date', $filters))
            ->get()
            ->map(fn (Assessment $row) => [
                'title' => $row->title,
                'extracurricular' => $row->extracurricular->name ?? '-',
                'student' => $row->student->user->name ?? 'Prestasi Kegiatan',
                'level' => $this->inferAchievementLevel($row->title, $row->description),
                'result' => $row->score !== null ? 'Skor '.$row->score : ($row->description ? Str::limit($row->description, 60) : '-'),
                'date' => optional($row->assessment_date)->format('d-m-Y') ?: '-',
                'documentation' => $row->description ? 'Deskripsi tersedia' : 'Belum ada',
                '_sort' => optional($row->assessment_date)->timestamp ?? 0,
            ]);

        $extracurricularAchievements = ExtracurricularAchievement::with('extracurricular')
            ->when($filters['extracurricular_id'] ?? null, fn ($query, $value) => $query->where('extracurricular_id', $value))
            ->tap(fn ($query) => $this->applySchoolYearWindow($query, 'achievement_date', $filters))
            ->get()
            ->map(fn (ExtracurricularAchievement $row) => [
                'title' => $row->title,
                'extracurricular' => $row->extracurricular->name ?? '-',
                'student' => 'Prestasi Ekstrakurikuler',
                'level' => $this->inferAchievementLevel($row->title, $row->description),
                'result' => $row->description ? Str::limit($row->description, 60) : '-',
                'date' => optional($row->achievement_date)->format('d-m-Y') ?: '-',
                'documentation' => $row->description ? 'Deskripsi tersedia' : 'Belum ada',
                '_sort' => optional($row->achievement_date)->timestamp ?? 0,
            ]);

        return $assessmentAchievements
            ->concat($extracurricularAchievements)
            ->sortByDesc('_sort')
            ->values()
            ->map(function (array $row): array {
                unset($row['_sort']);

                return $row;
            });
    }

    private function activityRows(array $filters): Collection
    {
        return Schedule::with(['extracurricular.coaches.user', 'extracurricular.coach.user'])
            ->when($filters['extracurricular_id'] ?? null, fn ($query, $value) => $query->where('extracurricular_id', $value))
            ->tap(fn ($query) => $this->applySchoolYearWindow($query, 'activity_date', $filters))
            ->latest('activity_date')
            ->get()
            ->map(fn (Schedule $row) => [
                'title' => $row->title,
                'extracurricular' => $row->extracurricular->name ?? '-',
                'coach' => $row->extracurricular->coach_names,
                'type' => $this->scheduleAudienceLabel($row),
                'date' => optional($row->activity_date)->format('d-m-Y') ?: '-',
                'location' => $row->location ?: '-',
            ]);
    }

    private function validateFilters(Request $request, bool $includeFormat = false): array
    {
        $rules = [
            'report_type' => ['nullable', Rule::in(self::REPORT_TYPES)],
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'school_year' => ['nullable', 'regex:/^\d{4}-\d{4}$/'],
            'semester' => ['nullable', Rule::in(['all', 'odd', 'even'])],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
        ];

        if ($includeFormat) {
            $rules['format'] = ['nullable', Rule::in(['pdf', 'xls'])];
        }

        $filters = $request->validate($rules);
        $filters['report_type'] = $filters['report_type'] ?? 'members';
        $filters['class_name'] = Student::normalizeClassName($filters['class_name'] ?? null);
        $filters['semester'] = $filters['semester'] ?? 'all';
        $filters['school_year'] = $filters['school_year'] ?? $this->currentSchoolYear();

        return $filters;
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

    private function applySchoolYearWindow($query, string $column, array $filters): void
    {
        [$start, $end] = $this->dateWindow($filters);
        $query->whereDate($column, '>=', $start)->whereDate($column, '<=', $end);

        if (! empty($filters['month'])) {
            $query->whereMonth($column, $filters['month']);
        }
    }

    private function applyRelatedSchoolYearWindow($query, string $relation, string $column, array $filters): void
    {
        [$start, $end] = $this->dateWindow($filters);
        $query->whereHas($relation, function ($relatedQuery) use ($column, $start, $end, $filters): void {
            $relatedQuery->whereDate($column, '>=', $start)->whereDate($column, '<=', $end);

            if (! empty($filters['month'])) {
                $relatedQuery->whereMonth($column, $filters['month']);
            }
        });
    }

    private function dateWindow(array $filters): array
    {
        $schoolYear = $filters['school_year'] ?? $this->currentSchoolYear();
        [$startYear, $endYear] = array_map('intval', explode('-', $schoolYear));
        $semester = $filters['semester'] ?? 'all';

        return match ($semester) {
            'odd' => [Carbon::create($startYear, 7, 1)->startOfDay(), Carbon::create($startYear, 12, 31)->endOfDay()],
            'even' => [Carbon::create($endYear, 1, 1)->startOfDay(), Carbon::create($endYear, 6, 30)->endOfDay()],
            default => [Carbon::create($startYear, 7, 1)->startOfDay(), Carbon::create($endYear, 6, 30)->endOfDay()],
        };
    }

    private function currentSchoolYear(): string
    {
        $now = now();
        $startYear = $now->month >= 7 ? $now->year : $now->year - 1;

        return $startYear.'-'.($startYear + 1);
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

    private function reportOptions(): array
    {
        return [
            'members' => 'Data Anggota',
            'registrations' => 'Pendaftaran',
            'attendances' => 'Kehadiran',
            'achievements' => 'Prestasi',
            'activities' => 'Kegiatan',
        ];
    }

    private function registrationStatusLabel(string $status): string
    {
        return match ($status) {
            Registration::STATUS_APPROVED => 'Diterima',
            Registration::STATUS_REJECTED => 'Ditolak',
            default => 'Masih Diperiksa',
        };
    }

    private function genderLabel(?string $gender): string
    {
        return match ($gender) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => '-',
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
            $schedule->isTalentTest() => 'Seleksi Anggota',
            str_contains($title, 'rapat') => 'Rapat',
            str_contains($title, 'lomba') || str_contains($title, 'kompetisi') || str_contains($title, 'tanding') => 'Lomba',
            default => 'Latihan',
        };
    }
}
