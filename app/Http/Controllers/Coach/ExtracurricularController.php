<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\Registration;
use Illuminate\View\View;

class ExtracurricularController extends Controller
{
    public function index(): View
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $extracurriculars = Extracurricular::with('coaches.user')
            ->whereHas('coaches', fn ($query) => $query->whereKey($coach->id))
            ->withCount([
                'registrations as participants_count' => fn ($query) => $query->where('status', Registration::STATUS_APPROVED),
            ])
            ->orderBy('name')
            ->get();

        return view('coach.extracurriculars.index', compact('extracurriculars'));
    }

    public function participants(Extracurricular $extracurricular): View
    {
        $this->authorize('viewByCoach', $extracurricular);

        $participants = $extracurricular->registrations()
            ->with(['student.user', 'talentTestResults'])
            ->where('status', Registration::STATUS_APPROVED)
            ->latest()
            ->paginate(15);

        return view('coach.extracurriculars.participants', compact('extracurricular', 'participants'));
    }
}
