<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\Extracurricular;
use App\Models\Schedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $extracurricularId = $request->string('extracurricular_id')->toString();

        $schedules = Schedule::with('extracurricular.coaches.user')
            ->whereHas('extracurricular.coaches', fn ($query) => $query->whereKey($coach->id))
            ->when($extracurricularId, fn ($query, $idValue) => $query->where('extracurricular_id', $idValue))
            ->orderByDesc('activity_date')
            ->paginate(10)
            ->withQueryString();

        $extracurriculars = $coach->extracurriculars()->orderBy('name')->get();

        return view('coach.schedules.index', compact('schedules', 'extracurriculars', 'extracurricularId'));
    }

    public function create(): View
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        return view('coach.schedules.create', [
            'extracurriculars' => $coach->extracurriculars()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $validated = $this->validatePayload($request, $coach->id);
        $validated['coach_id'] = $coach->id;

        Schedule::create($validated);

        return redirect()->route('coach.schedules.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function edit(Schedule $schedule): View
    {
        $this->authorize('manageByCoach', $schedule);
        $coach = auth()->user()->coach;

        return view('coach.schedules.edit', [
            'schedule' => $schedule,
            'extracurriculars' => $coach->extracurriculars()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        $this->authorize('manageByCoach', $schedule);
        $coach = auth()->user()->coach;

        $validated = $this->validatePayload($request, $coach->id);
        $schedule->update($validated);

        return redirect()->route('coach.schedules.index')->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $this->authorize('manageByCoach', $schedule);

        $schedule->delete();

        return redirect()->route('coach.schedules.index')->with('success', 'Jadwal berhasil dihapus.');
    }

    private function validatePayload(Request $request, int $coachId): array
    {
        $allowedExtracurricularIds = Coach::findOrFail($coachId)->extracurriculars()->pluck('extracurriculars.id')->all();

        return $request->validate([
            'extracurricular_id' => ['required', Rule::in($allowedExtracurricularIds)],
            'title' => ['required', 'string', 'max:255'],
            'activity_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
    }
}
