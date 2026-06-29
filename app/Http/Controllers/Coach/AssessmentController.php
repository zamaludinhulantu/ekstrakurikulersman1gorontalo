<?php

namespace App\Http\Controllers\Coach;

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
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $extracurricularId = $request->string('extracurricular_id')->toString();
        $extracurricularIds = $coach->extracurriculars()->pluck('extracurriculars.id');

        $assessments = Assessment::with(['student.user', 'extracurricular'])
            ->whereIn('extracurricular_id', $extracurricularIds)
            ->when($extracurricularId, fn ($query, $idValue) => $query->where('extracurricular_id', $idValue))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $students = Registration::with('student.user')
            ->whereIn('extracurricular_id', $extracurricularIds)
            ->where('status', Registration::STATUS_APPROVED)
            ->get()
            ->pluck('student')
            ->filter()
            ->unique('id')
            ->values();

        return view('coach.assessments.index', [
            'assessments' => $assessments,
            'extracurriculars' => $coach->extracurriculars()->orderBy('name')->get(),
            'students' => $students,
            'extracurricularId' => $extracurricularId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $validated = $this->validatePayload($request, $coach->id);
        $validated['coach_id'] = $coach->id;

        Assessment::create($validated);

        return redirect()->route('coach.assessments.index')->with('success', 'Data prestasi/penilaian berhasil ditambahkan.');
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

        $validated = $this->validatePayload($request, $coach->id);
        $assessment->update($validated);

        return redirect()->route('coach.assessments.index')->with('success', 'Data prestasi/penilaian berhasil diperbarui.');
    }

    public function destroy(Assessment $assessment): RedirectResponse
    {
        $this->authorize('manageByCoach', $assessment);

        $assessment->delete();

        return redirect()->route('coach.assessments.index')->with('success', 'Data prestasi/penilaian berhasil dihapus.');
    }

    private function validatePayload(Request $request, int $coachId): array
    {
        $allowedExtracurricularIds = Coach::findOrFail($coachId)->extracurriculars()->pluck('extracurriculars.id')->all();

        $validated = $request->validate([
            'extracurricular_id' => ['required', Rule::in($allowedExtracurricularIds)],
            'student_id' => ['required', 'exists:students,id'],
            'assessment_type' => ['required', Rule::in(['achievement', 'assessment'])],
            'title' => ['required', 'string', 'max:255'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string'],
            'assessment_date' => ['required', 'date'],
        ]);

        $isParticipant = Registration::where('student_id', $validated['student_id'])
            ->where('extracurricular_id', $validated['extracurricular_id'])
            ->where('status', Registration::STATUS_APPROVED)
            ->exists();

        if (! $isParticipant) {
            throw ValidationException::withMessages([
                'student_id' => 'Siswa belum terdaftar sebagai peserta aktif ekstrakurikuler tersebut.',
            ]);
        }

        return $validated;
    }
}
