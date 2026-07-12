<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Extracurricular;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function index(Request $request): View
    {
        $student = auth()->user()->student;
        abort_unless($student, 404, 'Data siswa tidak ditemukan.');

        $extracurricularId = $request->string('extracurricular_id')->toString();
        $allowedExtracurricularIds = Registration::where('student_id', $student->id)
            ->where('status', Registration::STATUS_APPROVED)
            ->pluck('extracurricular_id');

        $assessments = Assessment::with(['extracurricular', 'coach.user'])
            ->where('student_id', $student->id)
            ->where('assessment_type', 'assessment')
            ->when($extracurricularId, fn ($query, $idValue) => $query->where('extracurricular_id', $idValue))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('student.assessments.index', [
            'assessments' => $assessments,
            'extracurricularId' => $extracurricularId,
            'extracurriculars' => Extracurricular::whereIn('id', $allowedExtracurricularIds)->orderBy('name')->get(),
        ]);
    }
}
