<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $student = auth()->user()->student;
        abort_unless($student, 404, 'Data siswa tidak ditemukan.');

        $approvedExtracurricularIds = Registration::where('student_id', $student->id)
            ->where('status', Registration::STATUS_APPROVED)
            ->pluck('extracurricular_id');

        $date = $request->string('date')->toString();

        $schedules = Schedule::with(['extracurricular', 'coach.user'])
            ->whereIn('extracurricular_id', $approvedExtracurricularIds)
            ->when($date, fn ($query, $dateValue) => $query->whereDate('activity_date', $dateValue))
            ->orderByDesc('activity_date')
            ->paginate(10)
            ->withQueryString();

        return view('student.schedules.index', [
            'schedules' => $schedules,
            'date' => $date,
            'extracurriculars' => Extracurricular::whereIn('id', $approvedExtracurricularIds)->orderBy('name')->get(),
        ]);
    }
}
