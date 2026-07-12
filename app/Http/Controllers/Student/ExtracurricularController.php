<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExtracurricularController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $student = auth()->user()->student;

        $registrations = $student
            ? Registration::where('student_id', $student->id)->pluck('status', 'extracurricular_id')
            : collect();

        $extracurriculars = Extracurricular::with(['coach.user', 'coaches.user'])
            ->withCount([
                'registrations as participants_count' => fn ($query) => $query->where('status', Registration::STATUS_APPROVED),
            ])
            ->where('is_active', true)
            ->when($search, function ($query, $searchValue) {
                $query->where(function ($subQuery) use ($searchValue): void {
                    $subQuery->where('name', 'like', "%{$searchValue}%")
                        ->orWhere('description', 'like', "%{$searchValue}%");
                });
            })
            ->orderBy('name')
            ->paginate(9)
            ->withQueryString();

        return view('student.extracurriculars.index', [
            'extracurriculars' => $extracurriculars,
            'search' => $search,
            'registrationStatuses' => $registrations,
        ]);
    }

    public function show(Extracurricular $extracurricular): View
    {
        $student = auth()->user()->student;
        $registration = null;

        if ($student) {
            $registration = Registration::where('student_id', $student->id)
                ->where('extracurricular_id', $extracurricular->id)
                ->first();
        }

        $extracurricular->load([
            'coach.user',
            'coaches.user',
            'schedules' => fn ($query) => $query->orderByDesc('activity_date')->limit(8),
            'assessments' => fn ($query) => $query->where('assessment_type', 'achievement')->latest('assessment_date')->limit(5),
        ]);

        return view('student.extracurriculars.show', [
            'extracurricular' => $extracurricular,
            'registration' => $registration,
        ]);
    }
}
