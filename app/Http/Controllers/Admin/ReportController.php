<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SanitizesCsvExports;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AuditLog;
use App\Models\Attendance;
use App\Models\Coach;
use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    use SanitizesCsvExports;

    public function participants(Request $request): View
    {
        $filters = $request->validate([
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'coach_id' => ['nullable', 'exists:coaches,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'sort' => ['nullable', Rule::in(['name', 'nis', 'class_name', 'registration_date'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 50, 100])],
        ]);
        $filters['class_name'] = Student::normalizeClassName($filters['class_name'] ?? null);
        $filters['sort'] = $filters['sort'] ?? 'registration_date';
        $filters['direction'] = $filters['direction'] ?? 'desc';
        $filters['per_page'] = (int) ($filters['per_page'] ?? 20);

        $participantQuery = $this->participantsQuery($filters);
        $direction = $filters['direction'];
        $participants = match ($filters['sort']) {
            'name' => $participantQuery->orderByRaw(
                "(SELECT users.name FROM users INNER JOIN students ON students.user_id = users.id WHERE students.id = registrations.student_id LIMIT 1) {$direction}"
            ),
            'nis' => $participantQuery->orderByRaw(
                "(SELECT students.nis FROM students WHERE students.id = registrations.student_id LIMIT 1) {$direction}"
            ),
            'class_name' => $participantQuery->orderByRaw(
                "(SELECT students.class_name FROM students WHERE students.id = registrations.student_id LIMIT 1) {$direction}"
            ),
            default => $participantQuery->orderBy('registration_date', $direction),
        };
        $participants = $participants
            ->orderBy('registrations.id', $direction)
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('admin.reports.participants', [
            'participants' => $participants,
            'extracurriculars' => Extracurricular::orderBy('name')->get(),
            'coaches' => Coach::with('user')->orderBy('nip')->get(),
            'extracurricularId' => $filters['extracurricular_id'] ?? null,
            'coachId' => $filters['coach_id'] ?? null,
            'search' => $filters['search'] ?? '',
            'className' => $filters['class_name'] ?? '',
            'dateFrom' => $filters['date_from'] ?? null,
            'dateTo' => $filters['date_to'] ?? null,
            'sort' => $filters['sort'],
            'direction' => $filters['direction'],
            'perPage' => $filters['per_page'],
            'classOptions' => collect(array_keys(Student::registrationClassOptions())),
        ]);
    }

    public function schedules(Request $request): View
    {
        $filters = $request->validate([
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'coach_id' => ['nullable', 'exists:coaches,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $schedules = $this->schedulesQuery($filters)
            ->orderByDesc('activity_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.schedules', [
            'schedules' => $schedules,
            'extracurriculars' => Extracurricular::orderBy('name')->get(),
            'coaches' => Coach::with('user')->orderBy('nip')->get(),
            'extracurricularId' => $filters['extracurricular_id'] ?? null,
            'coachId' => $filters['coach_id'] ?? null,
            'dateFrom' => $filters['date_from'] ?? null,
            'dateTo' => $filters['date_to'] ?? null,
        ]);
    }

    public function destroySchedule(Schedule $schedule): RedirectResponse
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $schedule->loadMissing(['extracurricular', 'coach.user']);

        AuditLog::query()->create([
            'user_id' => auth()->id(),
            'action' => 'schedule.deleted',
            'subject_type' => Schedule::class,
            'subject_id' => $schedule->id,
            'description' => 'Super admin menghapus jadwal latihan.',
            'metadata' => [
                'title' => $schedule->title,
                'extracurricular_name' => $schedule->extracurricular->name ?? null,
                'coach_name' => $schedule->coach->user->name ?? null,
                'activity_date' => optional($schedule->activity_date)->format('d-m-Y'),
                'start_time' => substr((string) $schedule->start_time, 0, 5),
                'end_time' => substr((string) $schedule->end_time, 0, 5),
                'location' => $schedule->location,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $schedule->delete();

        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal latihan berhasil dihapus.');
    }

    public function attendances(Request $request): View
    {
        $filters = $request->validate([
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'coach_id' => ['nullable', 'exists:coaches,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['present', 'absent', 'sick', 'permission'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $attendances = $this->attendancesQuery($filters)
            ->latest('recorded_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.attendances', [
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

    public function assessments(Request $request): View
    {
        $filters = $request->validate([
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'coach_id' => ['nullable', 'exists:coaches,id'],
            'assessment_type' => ['nullable', Rule::in(['achievement', 'assessment'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $assessments = $this->assessmentsQuery($filters)
            ->latest('assessment_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.assessments', [
            'assessments' => $assessments,
            'extracurriculars' => Extracurricular::orderBy('name')->get(),
            'coaches' => Coach::with('user')->orderBy('nip')->get(),
            'extracurricularId' => $filters['extracurricular_id'] ?? null,
            'coachId' => $filters['coach_id'] ?? null,
            'assessmentType' => $filters['assessment_type'] ?? null,
            'dateFrom' => $filters['date_from'] ?? null,
            'dateTo' => $filters['date_to'] ?? null,
        ]);
    }

    public function export(Request $request, string $type): StreamedResponse
    {
        abort_unless(in_array($type, ['participants', 'schedules', 'attendances', 'assessments'], true), 404);

        $filters = $request->validate([
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'coach_id' => ['nullable', 'exists:coaches,id'],
            'status' => ['nullable', Rule::in(['present', 'absent', 'sick', 'permission'])],
            'assessment_type' => ['nullable', Rule::in(['achievement', 'assessment'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'format' => ['nullable', Rule::in(['csv', 'xls'])],
            'sort' => ['nullable', Rule::in(['name', 'nis', 'class_name', 'registration_date'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 50, 100])],
        ]);
        $filters['class_name'] = Student::normalizeClassName($filters['class_name'] ?? null);

        $format = $filters['format'] ?? 'csv';
        $delimiter = $format === 'xls' ? "\t" : ',';
        $extension = $format === 'xls' ? 'xls' : 'csv';
        $filename = 'laporan-'.$type.'-'.Carbon::now()->format('YmdHis').'.'.$extension;

        return response()->streamDownload(function () use ($type, $filters, $delimiter): void {
            $handle = fopen('php://output', 'w');

            if ($type === 'participants') {
                fputcsv($handle, ['Siswa', 'NIS', 'Ekstrakurikuler', 'Tanggal Daftar', 'Pembina'], $delimiter);
                $this->participantsQuery($filters)
                    ->each(function ($row) use ($handle, $delimiter): void {
                        fputcsv($handle, $this->sanitizeExportRow([
                            $row->student->user->name ?? '-',
                            $row->student->nis ?? '-',
                            $row->extracurricular->name ?? '-',
                            optional($row->registration_date)->format('Y-m-d'),
                            $row->extracurricular->coach_names,
                        ]), $delimiter);
                    });
            } elseif ($type === 'schedules') {
                fputcsv($handle, ['Ekstrakurikuler', 'Pembina', 'Judul', 'Tanggal', 'Jam', 'Lokasi'], $delimiter);
                $this->schedulesQuery($filters)
                    ->each(function ($row) use ($handle, $delimiter): void {
                        fputcsv($handle, $this->sanitizeExportRow([
                            $row->extracurricular->name ?? '-',
                            $row->coach->user->name ?? $row->extracurricular->coach_names,
                            $row->title,
                            optional($row->activity_date)->format('Y-m-d'),
                            substr((string) $row->start_time, 0, 5).' - '.substr((string) $row->end_time, 0, 5),
                            $row->location ?: '-',
                        ]), $delimiter);
                    });
            } elseif ($type === 'attendances') {
                fputcsv($handle, ['Siswa', 'Ekstrakurikuler', 'Jadwal', 'Status', 'Tanggal'], $delimiter);
                $this->attendancesQuery($filters)
                    ->each(function ($row) use ($handle, $delimiter): void {
                        fputcsv($handle, $this->sanitizeExportRow([
                            $row->student->user->name ?? '-',
                            $row->extracurricular->name ?? '-',
                            $row->schedule->title ?? '-',
                            $this->formatAttendanceStatus($row->status),
                            optional($row->schedule->activity_date)->format('Y-m-d'),
                        ]), $delimiter);
                    });
            } else {
                fputcsv($handle, ['Siswa', 'Ekstrakurikuler', 'Jenis', 'Judul', 'Nilai', 'Tanggal', 'Pembina'], $delimiter);
                $this->assessmentsQuery($filters)
                    ->each(function ($row) use ($handle, $delimiter): void {
                        fputcsv($handle, $this->sanitizeExportRow([
                            $row->student->user->name ?? ($row->assessment_type === 'achievement' ? 'Prestasi kegiatan' : '-'),
                            $row->extracurricular->name ?? '-',
                            $row->assessment_type === 'achievement' ? 'Prestasi Kegiatan' : 'Penilaian Siswa',
                            $row->title,
                            $row->score ?? '-',
                            optional($row->assessment_date)->format('Y-m-d'),
                            $row->coach->user->name ?? '-',
                        ]), $delimiter);
                    });
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => $format === 'xls'
                ? 'application/vnd.ms-excel; charset=UTF-8'
                : 'text/csv; charset=UTF-8',
        ]);
    }

    private function participantsQuery(array $filters)
    {
        return Registration::with(['student.user', 'extracurricular.coaches.user', 'extracurricular.coach.user'])
            ->where('status', Registration::STATUS_APPROVED)
            ->when($filters['extracurricular_id'] ?? null, fn ($query, $value) => $query->where('extracurricular_id', $value))
            ->when($filters['coach_id'] ?? null, function ($query, $value): void {
                $query->whereHas('extracurricular.coaches', fn ($subQuery) => $subQuery->whereKey($value));
            })
            ->when($filters['class_name'] ?? null, function ($query, string $className): void {
                $comparable = Student::normalizedClassComparable($className);
                $query->whereHas('student', fn ($studentQuery) => $studentQuery
                    ->whereRaw(Student::normalizedClassExpression('class_name').' = ?', [$comparable]));
            })
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery->whereHas('student', function ($studentQuery) use ($search): void {
                        $studentQuery->where('nis', 'like', "%{$search}%")
                            ->orWhere('class_name', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($userQuery) use ($search): void {
                                $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            });
                    })->orWhereHas('extracurricular', fn ($activityQuery) => $activityQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['date_from'] ?? null, fn ($query, $value) => $query->whereDate('registration_date', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($query, $value) => $query->whereDate('registration_date', '<=', $value));
    }

    private function schedulesQuery(array $filters)
    {
        return Schedule::with(['extracurricular.coaches.user', 'coach.user'])
            ->when($filters['extracurricular_id'] ?? null, fn ($query, $value) => $query->where('extracurricular_id', $value))
            ->when($filters['coach_id'] ?? null, function ($query, $value): void {
                $query->where(function ($subQuery) use ($value): void {
                    $subQuery->where('coach_id', $value)
                        ->orWhereHas('extracurricular.coaches', fn ($coachQuery) => $coachQuery->whereKey($value));
                });
            })
            ->when($filters['date_from'] ?? null, fn ($query, $value) => $query->whereDate('activity_date', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($query, $value) => $query->whereDate('activity_date', '<=', $value));
    }

    private function attendancesQuery(array $filters)
    {
        return Attendance::with(['student.user', 'extracurricular.coaches.user', 'schedule.coach.user'])
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

    private function assessmentsQuery(array $filters)
    {
        return Assessment::with(['student.user', 'extracurricular.coaches.user', 'coach.user'])
            ->when($filters['extracurricular_id'] ?? null, fn ($query, $value) => $query->where('extracurricular_id', $value))
            ->when($filters['coach_id'] ?? null, function ($query, $value): void {
                $query->where(function ($subQuery) use ($value): void {
                    $subQuery->where('coach_id', $value)
                        ->orWhereHas('extracurricular.coaches', fn ($coachQuery) => $coachQuery->whereKey($value));
                });
            })
            ->when($filters['assessment_type'] ?? null, fn ($query, $value) => $query->where('assessment_type', $value))
            ->when($filters['date_from'] ?? null, fn ($query, $value) => $query->whereDate('assessment_date', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($query, $value) => $query->whereDate('assessment_date', '<=', $value));
    }

    private function formatAttendanceStatus(string $status): string
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
