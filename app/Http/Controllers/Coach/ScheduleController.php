<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\Registration;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
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
            'extracurriculars' => $this->extracurricularOptions($coach->id),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $validated = $this->validatePayload($request, $coach->id);
        $validated['coach_id'] = $coach->id;
        DB::transaction(function () use ($validated, $request): void {
            $schedule = Schedule::create($validated);
            $this->syncParticipants($schedule, $request->input('participant_registration_ids', []));
        });

        return redirect()->route('coach.schedules.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function edit(Schedule $schedule): View
    {
        $this->authorize('manageByCoach', $schedule);
        $coach = auth()->user()->coach;
        $schedule->load('scheduleParticipants');

        return view('coach.schedules.edit', [
            'schedule' => $schedule,
            'extracurriculars' => $this->extracurricularOptions($coach->id),
        ]);
    }

    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        $this->authorize('manageByCoach', $schedule);
        $coach = auth()->user()->coach;

        $validated = $this->validatePayload($request, $coach->id);
        DB::transaction(function () use ($schedule, $validated, $request): void {
            $schedule->update($validated);
            $this->syncParticipants($schedule, $request->input('participant_registration_ids', []));
        });

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
            'participant_registration_ids' => ['nullable', 'array'],
            'participant_registration_ids.*' => ['integer'],
        ]);
    }

    private function extracurricularOptions(int $coachId)
    {
        return Coach::findOrFail($coachId)
            ->extracurriculars()
            ->with(['registrations' => function ($query): void {
                $query->where('status', Registration::STATUS_APPROVED)
                    ->with('student.user')
                    ->orderBy('created_at');
            }])
            ->orderBy('name')
            ->get();
    }

    private function syncParticipants(Schedule $schedule, array $registrationIds): void
    {
        $selectedIds = collect($registrationIds)
            ->map(fn ($value) => (int) $value)
            ->filter()
            ->unique()
            ->values();

        $registrations = Registration::query()
            ->with('student')
            ->where('extracurricular_id', $schedule->extracurricular_id)
            ->where('status', Registration::STATUS_APPROVED)
            ->whereIn('id', $selectedIds)
            ->get();

        $existingStudentIds = [];
        foreach ($registrations as $registration) {
            $existingStudentIds[] = $registration->student_id;
            $schedule->scheduleParticipants()->updateOrCreate(
                ['student_id' => $registration->student_id],
                [
                    'registration_id' => $registration->id,
                    'assigned_by' => auth()->id(),
                ]
            );
        }

        $schedule->scheduleParticipants()
            ->whereNotIn('student_id', $existingStudentIds)
            ->delete();
    }
}
