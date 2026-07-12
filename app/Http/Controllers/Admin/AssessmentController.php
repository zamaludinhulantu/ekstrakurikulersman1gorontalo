<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Coach;
use App\Models\Extracurricular;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'coach_id' => ['nullable', 'exists:coaches,id'],
            'assessment_type' => ['nullable', Rule::in(['achievement', 'assessment'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $assessments = Assessment::with(['student.user', 'extracurricular', 'coach.user'])
            ->when($filters['extracurricular_id'] ?? null, fn ($query, $value) => $query->where('extracurricular_id', $value))
            ->when($filters['coach_id'] ?? null, function ($query, $value): void {
                $query->where(function ($subQuery) use ($value): void {
                    $subQuery->where('coach_id', $value)
                        ->orWhereHas('extracurricular.coaches', fn ($coachQuery) => $coachQuery->whereKey($value));
                });
            })
            ->when($filters['assessment_type'] ?? null, fn ($query, $value) => $query->where('assessment_type', $value))
            ->when($filters['date_from'] ?? null, fn ($query, $value) => $query->whereDate('assessment_date', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($query, $value) => $query->whereDate('assessment_date', '<=', $value))
            ->latest('assessment_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.assessments.index', $this->buildViewData([
            'assessments' => $assessments,
            'extracurricularId' => $filters['extracurricular_id'] ?? null,
            'coachId' => $filters['coach_id'] ?? null,
            'assessmentType' => $filters['assessment_type'] ?? null,
            'dateFrom' => $filters['date_from'] ?? null,
            'dateTo' => $filters['date_to'] ?? null,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        Assessment::create($this->validatePayload($request));

        return redirect()->route('admin.assessments.index')->with('success', 'Data prestasi kegiatan / penilaian siswa berhasil ditambahkan.');
    }

    public function edit(Assessment $assessment): View
    {
        $assessment->load(['student.user', 'coach.user', 'extracurricular']);

        return view('admin.assessments.edit', $this->buildViewData([
            'assessment' => $assessment,
        ]));
    }

    public function update(Request $request, Assessment $assessment): RedirectResponse
    {
        $assessment->update($this->validatePayload($request));

        return redirect()->route('admin.assessments.index')->with('success', 'Data prestasi kegiatan / penilaian siswa berhasil diperbarui.');
    }

    public function destroy(Assessment $assessment): RedirectResponse
    {
        $assessment->delete();

        return redirect()->route('admin.assessments.index')->with('success', 'Data prestasi kegiatan / penilaian siswa berhasil dihapus.');
    }

    private function buildViewData(array $overrides = []): array
    {
        $extracurriculars = Extracurricular::with(['coach.user', 'coaches.user'])
            ->orderBy('name')
            ->get();

        $approvedRegistrations = Registration::with(['student.user', 'extracurricular'])
            ->where('status', Registration::STATUS_APPROVED)
            ->whereHas('student.user')
            ->orderBy('extracurricular_id')
            ->orderBy('student_id')
            ->get();

        $coaches = Coach::with(['user', 'extracurriculars'])
            ->whereHas('user')
            ->get()
            ->sortBy(fn (Coach $coach) => $coach->user->name ?? $coach->nip)
            ->values();

        return array_merge([
            'extracurriculars' => $extracurriculars,
            'approvedRegistrations' => $approvedRegistrations,
            'coaches' => $coaches,
        ], $overrides);
    }

    private function validatePayload(Request $request): array
    {
        $validated = $request->validate([
            'extracurricular_id' => ['required', 'exists:extracurriculars,id'],
            'student_id' => ['nullable', 'exists:students,id'],
            'coach_id' => ['nullable', 'exists:coaches,id'],
            'assessment_type' => ['required', Rule::in(['achievement', 'assessment'])],
            'title' => ['required', 'string', 'max:255'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string'],
            'assessment_date' => ['required', 'date'],
        ]);

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

        if (! empty($validated['coach_id'])) {
            $coachMatchesExtracurricular = Extracurricular::query()
                ->whereKey($validated['extracurricular_id'])
                ->where(function ($query) use ($validated): void {
                    $query->where('coach_id', $validated['coach_id'])
                        ->orWhereHas('coaches', fn ($coachQuery) => $coachQuery->whereKey($validated['coach_id']));
                })
                ->exists();

            if (! $coachMatchesExtracurricular) {
                throw ValidationException::withMessages([
                    'coach_id' => 'Pembina yang dipilih tidak terkait dengan ekstrakurikuler tersebut.',
                ]);
            }
        }

        return $validated;
    }
}
