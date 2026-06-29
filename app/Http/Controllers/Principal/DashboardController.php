<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\Extracurricular;
use App\Models\Registration;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $attendanceSummary = Attendance::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $assessmentSummary = Assessment::selectRaw('assessment_type, COUNT(*) as total')
            ->groupBy('assessment_type')
            ->pluck('total', 'assessment_type');

        return view('dashboard.principal', [
            'totalExtracurriculars' => Extracurricular::count(),
            'totalParticipants' => Registration::where('status', Registration::STATUS_APPROVED)->count(),
            'totalAttendances' => Attendance::count(),
            'totalAssessments' => Assessment::count(),
            'attendanceSummary' => $attendanceSummary,
            'assessmentSummary' => $assessmentSummary,
        ]);
    }
}
