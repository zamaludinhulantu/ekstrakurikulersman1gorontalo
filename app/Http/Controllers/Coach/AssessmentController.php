<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Concerns\SanitizesCsvExports;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Coach;
use App\Models\Extracurricular;
use App\Models\Registration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssessmentController extends Controller
{
    use SanitizesCsvExports;

    public function index(Request $request): View
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $extracurricularIds = $coach->extracurriculars()->pluck('extracurriculars.id');
        $historyExtracurricularId = $request->string('history_extracurricular_id')->toString();
        $historyType = $request->string('history_type')->toString();
        $historyStatus = $request->string('history_status')->toString();
        $historyMonth = $request->string('history_month')->toString();
        $historyPeriod = $request->string('history_period')->toString();
        $activeTab = $request->string('tab')->toString() ?: old('active_tab', 'assessment');

        if (! in_array($activeTab, ['achievement', 'assessment', 'history'], true)) {
            $activeTab = 'assessment';
        }

        $assessments = Assessment::with(['student.user', 'extracurricular'])
            ->whereIn('extracurricular_id', $extracurricularIds)
            ->when($historyExtracurricularId, fn ($query, $idValue) => $query->where('extracurricular_id', $idValue))
            ->when($historyType, fn ($query, $value) => $query->where('assessment_type', $value))
            ->when($historyStatus, fn ($query, $value) => $query->where('status', $value))
            ->when($historyMonth, fn ($query, $value) => $query->whereMonth('assessment_date', (int) $value))
            ->when($historyPeriod === 'recent', fn ($query) => $query->whereDate('assessment_date', '>=', now()->subDays(30)))
            ->when($historyPeriod === 'semester', fn ($query) => $query->whereYear('assessment_date', now()->year)->whereIn(DB::raw('MONTH(assessment_date)'), now()->month <= 6 ? [1, 2, 3, 4, 5, 6] : [7, 8, 9, 10, 11, 12]))
            ->latest('assessment_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $approvedRegistrations = Registration::with('student.user')
            ->whereIn('extracurricular_id', $extracurricularIds)
            ->where('status', Registration::STATUS_APPROVED)
            ->orderBy('extracurricular_id')
            ->get()
            ->filter(fn ($registration) => $registration->student && $registration->student->user)
            ->values();

        $students = $approvedRegistrations
            ->pluck('student')
            ->unique('id')
            ->values();

        $assessmentLookup = Assessment::query()
            ->where('coach_id', $coach->id)
            ->where('assessment_type', 'assessment')
            ->whereIn('extracurricular_id', $extracurricularIds)
            ->get()
            ->mapWithKeys(function (Assessment $assessment) {
                $key = implode('|', [
                    $assessment->extracurricular_id,
                    $assessment->student_id,
                    $assessment->title,
                    optional($assessment->assessment_date)->format('Y-m-d'),
                ]);

                return [$key => $assessment->status];
            });

        $recentAchievements = Assessment::with('extracurricular')
            ->where('coach_id', $coach->id)
            ->where('assessment_type', 'achievement')
            ->latest('assessment_date')
            ->latest('id')
            ->limit(4)
            ->get();

        $historyMonthOptions = Assessment::query()
            ->whereIn('extracurricular_id', $extracurricularIds)
            ->orderByDesc('assessment_date')
            ->get()
            ->map(fn (Assessment $assessment) => optional($assessment->assessment_date)->format('m'))
            ->filter()
            ->unique()
            ->values();

        return view('coach.assessments.index', [
            'assessments' => $assessments,
            'extracurriculars' => $coach->extracurriculars()->with('coaches.user')->orderBy('name')->get(),
            'students' => $students,
            'approvedRegistrations' => $approvedRegistrations,
            'assessmentLookup' => $assessmentLookup,
            'historyExtracurricularId' => $historyExtracurricularId,
            'historyType' => $historyType,
            'historyStatus' => $historyStatus,
            'historyMonth' => $historyMonth,
            'historyPeriod' => $historyPeriod,
            'historyMonthOptions' => $historyMonthOptions,
            'recentAchievements' => $recentAchievements,
            'activeTab' => $activeTab,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        if ($request->input('entry_mode') === 'mass' && $request->input('assessment_type') === 'assessment') {
            $savedCount = $this->storeMassAssessments($request, $coach);

            return redirect()->route('coach.assessments.index', ['tab' => 'assessment'])->with('success', $request->input('submit_action') === 'draft'
                ? "Draft penilaian berhasil disimpan untuk {$savedCount} siswa."
                : "Penilaian berhasil disimpan untuk {$savedCount} siswa.");
        }

        $validated = $this->validateSinglePayload($request, $coach->id);
        $validated['coach_id'] = $coach->id;
        $validated['status'] = Assessment::STATUS_PUBLISHED;

        Assessment::create($validated);

        $redirectParameters = $request->filled('active_tab')
            ? ['tab' => $request->input('active_tab')]
            : [];

        return redirect()->route('coach.assessments.index', $redirectParameters)->with('success', 'Data prestasi kegiatan / penilaian siswa berhasil ditambahkan.');
    }

    public function edit(Assessment $assessment): View
    {
        $this->authorize('manageByCoach', $assessment);
        $coach = auth()->user()->coach;

        $extracurricularIds = $coach->extracurriculars()->pluck('extracurriculars.id');
        $students = Registration::with('student.user')
            ->whereIn('extracurricular_id', $extracurricularIds)
            ->where('status', Registration::STATUS_APPROVED)
            ->get()
            ->pluck('student')
            ->filter()
            ->unique('id')
            ->values();

        return view('coach.assessments.edit', [
            'assessment' => $assessment,
            'extracurriculars' => $coach->extracurriculars()->orderBy('name')->get(),
            'students' => $students,
        ]);
    }

    public function update(Request $request, Assessment $assessment): RedirectResponse
    {
        $this->authorize('manageByCoach', $assessment);
        $coach = auth()->user()->coach;

        $validated = $this->validateSinglePayload($request, $coach->id);
        $assessment->update($validated);

        return redirect()->route('coach.assessments.index')->with('success', 'Data prestasi kegiatan / penilaian siswa berhasil diperbarui.');
    }

    public function destroy(Assessment $assessment): RedirectResponse
    {
        $this->authorize('manageByCoach', $assessment);

        $assessment->delete();

        return redirect()->route('coach.assessments.index')->with('success', 'Data prestasi kegiatan / penilaian siswa berhasil dihapus.');
    }

    public function export(Request $request): StreamedResponse
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $extracurricularIds = $coach->extracurriculars()->pluck('extracurriculars.id')->all();
        $validated = $request->validate([
            'history_extracurricular_id' => ['nullable', Rule::in($extracurricularIds)],
            'history_type' => ['nullable', Rule::in(['achievement', 'assessment'])],
            'history_status' => ['nullable', Rule::in(['draft', 'published'])],
            'history_month' => ['nullable', 'digits:2'],
            'history_period' => ['nullable', Rule::in(['recent', 'semester'])],
            'format' => ['nullable', Rule::in(['csv', 'xls'])],
        ]);

        $format = $validated['format'] ?? 'csv';
        $delimiter = $format === 'xls' ? "\t" : ',';
        $extension = $format === 'xls' ? 'xls' : 'csv';
        $filename = 'prestasi-penilaian-pembina-'.Carbon::now()->format('YmdHis').'.'.$extension;

        return response()->streamDownload(function () use ($coach, $validated, $delimiter): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Jenis', 'Judul', 'Subjek', 'Ekstrakurikuler', 'Hasil', 'Status', 'Tanggal', 'Catatan'], $delimiter);

            $this->historyQuery($coach->id, $validated)
                ->orderByDesc('assessment_date')
                ->orderByDesc('id')
                ->each(function (Assessment $row) use ($handle, $delimiter): void {
                    $isAchievement = $row->assessment_type === 'achievement';
                    fputcsv($handle, $this->sanitizeExportRow([
                        $isAchievement ? 'Prestasi' : 'Penilaian',
                        $row->title,
                        $isAchievement ? ($row->extracurricular->name ?? 'Tim Ekstrakurikuler') : ($row->student->user->name ?? '-'),
                        $row->extracurricular->name ?? '-',
                        $isAchievement ? 'Prestasi tercatat' : ($row->score ?? '-'),
                        $row->status === 'draft' ? 'Draft' : 'Dipublikasikan',
                        optional($row->assessment_date)->format('Y-m-d'),
                        $row->description ?? '-',
                    ]), $delimiter);
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => $format === 'xls'
                ? 'application/vnd.ms-excel; charset=UTF-8'
                : 'text/csv; charset=UTF-8',
        ]);
    }

    private function validateSinglePayload(Request $request, int $coachId): array
    {
        $allowedExtracurricularIds = Coach::findOrFail($coachId)->extracurriculars()->pluck('extracurriculars.id')->all();

        $validated = $request->validate([
            'extracurricular_id' => ['required', Rule::in($allowedExtracurricularIds)],
            'student_id' => ['nullable', 'exists:students,id'],
            'assessment_type' => ['required', Rule::in(['achievement', 'assessment'])],
            'title' => ['nullable', 'string', 'max:255'],
            'title_option' => ['nullable', Rule::in($this->assessmentTitleOptions())],
            'custom_title' => ['nullable', 'string', 'max:255'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string'],
            'assessment_date' => ['required', 'date'],
        ]);

        $validated['title'] = $this->resolveAssessmentTitle($validated);

        if (($validated['assessment_type'] ?? null) === 'assessment' && empty($validated['student_id'])) {
            throw ValidationException::withMessages([
                'student_id' => 'Siswa wajib dipilih untuk data penilaian.',
            ]);
        }

        if (! empty($validated['student_id'])) {
            $isParticipant = Registration::where('student_id', $validated['student_id'])
                ->where('extracurricular_id', $validated['extracurricular_id'])
                ->where('status', Registration::STATUS_APPROVED)
                ->exists();

            if (! $isParticipant) {
                throw ValidationException::withMessages([
                    'student_id' => 'Siswa belum terdaftar sebagai peserta aktif ekstrakurikuler tersebut.',
                ]);
            }
        }

        if (($validated['assessment_type'] ?? null) === 'achievement') {
            $validated['student_id'] = null;
            $validated['score'] = null;
        }

        unset($validated['title_option'], $validated['custom_title']);

        return $validated;
    }

    private function storeMassAssessments(Request $request, Coach $coach): int
    {
        $allowedExtracurricularIds = $coach->extracurriculars()->pluck('extracurriculars.id')->all();
        $validated = $request->validate([
            'entry_mode' => ['required', Rule::in(['mass'])],
            'submit_action' => ['required', Rule::in(['draft', 'publish'])],
            'extracurricular_id' => ['required', Rule::in($allowedExtracurricularIds)],
            'coach_id' => ['nullable', 'integer'],
            'assessment_type' => ['required', Rule::in(['assessment'])],
            'title_option' => ['required', Rule::in($this->assessmentTitleOptions())],
            'custom_title' => ['nullable', 'string', 'max:255'],
            'assessment_date' => ['required', 'date'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.student_id' => ['required', 'exists:students,id'],
            'rows.*.score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'rows.*.description' => ['nullable', 'string'],
        ]);

        $title = $this->resolveAssessmentTitle([
            'title_option' => $validated['title_option'],
            'custom_title' => $validated['custom_title'] ?? null,
        ]);

        $activeStudentIds = Registration::query()
            ->where('extracurricular_id', $validated['extracurricular_id'])
            ->where('status', Registration::STATUS_APPROVED)
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $rows = collect($validated['rows'])
            ->map(function (array $row): array {
                $row['student_id'] = (int) $row['student_id'];
                $row['description'] = trim((string) ($row['description'] ?? ''));

                return $row;
            })
            ->filter(fn (array $row) => ($row['score'] ?? null) !== null || $row['description'] !== '')
            ->values();

        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'rows' => 'Isi minimal satu nilai atau catatan siswa sebelum menyimpan.',
            ]);
        }

        $invalidStudentIds = $rows->pluck('student_id')
            ->reject(fn (int $studentId) => in_array($studentId, $activeStudentIds, true))
            ->all();

        if ($invalidStudentIds !== []) {
            throw ValidationException::withMessages([
                'rows' => 'Ada siswa yang bukan anggota aktif ekstrakurikuler terpilih.',
            ]);
        }

        if ($validated['submit_action'] === 'publish') {
            $missingScore = $rows->contains(fn (array $row) => $row['score'] === null || $row['score'] === '');
            if ($missingScore) {
                throw ValidationException::withMessages([
                    'rows' => 'Semua siswa yang diisi harus memiliki nilai sebelum disimpan sebagai penilaian final.',
                ]);
            }
        }

        $status = $validated['submit_action'] === 'draft'
            ? Assessment::STATUS_DRAFT
            : Assessment::STATUS_PUBLISHED;

        $existingRows = Assessment::query()
            ->where('coach_id', $coach->id)
            ->where('assessment_type', 'assessment')
            ->where('extracurricular_id', $validated['extracurricular_id'])
            ->where('title', $title)
            ->whereDate('assessment_date', $validated['assessment_date'])
            ->whereIn('student_id', $rows->pluck('student_id')->all())
            ->get()
            ->keyBy('student_id');

        $timestamp = now();
        $insertRows = [];
        $updateRows = [];

        foreach ($rows as $row) {
            $basePayload = [
                'student_id' => $row['student_id'],
                'extracurricular_id' => (int) $validated['extracurricular_id'],
                'coach_id' => $coach->id,
                'assessment_type' => 'assessment',
                'status' => $status,
                'title' => $title,
                'score' => $row['score'] !== null && $row['score'] !== '' ? $row['score'] : null,
                'description' => $row['description'] !== '' ? $row['description'] : null,
                'assessment_date' => $validated['assessment_date'],
                'updated_at' => $timestamp,
            ];

            $existing = $existingRows->get($row['student_id']);
            if ($existing) {
                $updateRows[] = array_merge($basePayload, ['id' => $existing->id]);
            } else {
                $insertRows[] = array_merge($basePayload, ['created_at' => $timestamp]);
            }
        }

        DB::transaction(function () use ($insertRows, $updateRows): void {
            if ($insertRows !== []) {
                Assessment::query()->insert($insertRows);
            }

            foreach ($updateRows as $payload) {
                $id = $payload['id'];
                unset($payload['id']);
                Assessment::query()->whereKey($id)->update($payload);
            }
        });

        return count($insertRows) + count($updateRows);
    }

    private function assessmentTitleOptions(): array
    {
        return [
            'Kedisiplinan',
            'Kehadiran',
            'Kerja sama',
            'Keaktifan',
            'Kemampuan teknis',
            'Perkembangan',
            'Sikap',
            'Penilaian lain',
        ];
    }

    private function resolveAssessmentTitle(array $validated): string
    {
        $title = trim((string) ($validated['title'] ?? ''));
        if ($title !== '') {
            return $title;
        }

        $titleOption = trim((string) ($validated['title_option'] ?? ''));
        if ($titleOption === 'Penilaian lain') {
            $customTitle = trim((string) ($validated['custom_title'] ?? ''));
            if ($customTitle === '') {
                throw ValidationException::withMessages([
                    'custom_title' => 'Nama penilaian wajib diisi saat memilih Penilaian lain.',
                ]);
            }

            return $customTitle;
        }

        if ($titleOption !== '') {
            return $titleOption;
        }

        throw ValidationException::withMessages([
            'title' => 'Jenis atau judul penilaian wajib diisi.',
        ]);
    }

    private function historyQuery(int $coachId, array $filters)
    {
        return Assessment::with(['student.user', 'extracurricular'])
            ->where('coach_id', $coachId)
            ->when($filters['history_extracurricular_id'] ?? null, fn ($query, $value) => $query->where('extracurricular_id', $value))
            ->when($filters['history_type'] ?? null, fn ($query, $value) => $query->where('assessment_type', $value))
            ->when($filters['history_status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['history_month'] ?? null, fn ($query, $value) => $query->whereMonth('assessment_date', (int) $value))
            ->when(($filters['history_period'] ?? null) === 'recent', fn ($query) => $query->whereDate('assessment_date', '>=', now()->subDays(30)))
            ->when(($filters['history_period'] ?? null) === 'semester', fn ($query) => $query->whereYear('assessment_date', now()->year)->whereIn(DB::raw('MONTH(assessment_date)'), now()->month <= 6 ? [1, 2, 3, 4, 5, 6] : [7, 8, 9, 10, 11, 12]));
    }
}
