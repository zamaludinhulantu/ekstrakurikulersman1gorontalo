<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\Extracurricular;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TalentTestController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'coach_id' => ['nullable', 'exists:coaches,id'],
            'status' => ['nullable', Rule::in(['scheduled', 'cancelled', 'completed'])],
        ]);

        $tests = Schedule::with([
            'extracurricular.coaches.user',
            'coach.user',
            'talentTestParticipants.student.user',
            'talentTestResults',
        ])
            ->where('schedule_type', 'talent_test')
            ->when($filters['extracurricular_id'] ?? null, fn ($query, $value) => $query->where('extracurricular_id', $value))
            ->when($filters['coach_id'] ?? null, function ($query, $value): void {
                $query->where(function ($subQuery) use ($value): void {
                    $subQuery->where('coach_id', $value)
                        ->orWhereHas('extracurricular.coaches', fn ($coachQuery) => $coachQuery->whereKey($value));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->withCount('talentTestParticipants')
            ->orderByDesc('activity_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.talent-tests.index', [
            'tests' => $tests,
            'extracurriculars' => Extracurricular::orderBy('name')->get(),
            'coaches' => Coach::with('user')->orderBy('nip')->get(),
            'extracurricularId' => $filters['extracurricular_id'] ?? null,
            'coachId' => $filters['coach_id'] ?? null,
            'status' => $filters['status'] ?? null,
        ]);
    }
}
