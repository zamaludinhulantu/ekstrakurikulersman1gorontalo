<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveTalentTestResultsRequest;
use App\Http\Requests\StoreTalentTestRequest;
use App\Models\Extracurricular;
use App\Models\NotificationPreference;
use App\Models\Registration;
use App\Models\Schedule;
use App\Models\TalentTestAspect;
use App\Models\TalentTestParticipant;
use App\Models\TalentTestResult;
use App\Support\NotificationCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TalentTestController extends Controller
{
    public function index(Request $request): View
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $extracurricularId = $request->string('extracurricular_id')->toString();
        $status = $request->string('status')->toString();
        $period = $request->string('period')->toString();
        $search = $request->string('search')->toString();

        $baseQuery = Schedule::query()
            ->where('schedule_type', 'talent_test')
            ->whereHas('extracurricular.coaches', fn ($query) => $query->whereKey($coach->id));

        $summaryTests = (clone $baseQuery)
            ->withCount('talentTestParticipants')
            ->withCount([
                'talentTestResults as draft_results_count' => fn ($query) => $query->where('status', 'draft'),
                'talentTestResults as published_results_count' => fn ($query) => $query->where('status', 'published'),
            ])
            ->get();

        $tests = (clone $baseQuery)
            ->with(['extracurricular'])
            ->withCount('talentTestParticipants')
            ->withCount([
                'talentTestResults as draft_results_count' => fn ($query) => $query->where('status', 'draft'),
                'talentTestResults as published_results_count' => fn ($query) => $query->where('status', 'published'),
            ])
            ->when($extracurricularId, fn ($query, $value) => $query->where('extracurricular_id', $value))
            ->when($search, fn ($query, $value) => $query->where('title', 'like', "%{$value}%"))
            ->when($period, function ($query, $value): void {
                match ($value) {
                    'today' => $query->whereDate('activity_date', today()),
                    'week' => $query->whereBetween('activity_date', [today()->startOfWeek(), today()->endOfWeek()]),
                    'month' => $query->whereMonth('activity_date', today()->month)->whereYear('activity_date', today()->year),
                    default => null,
                };
            })
            ->when($status, function ($query, $value): void {
                match ($value) {
                    'draft' => $query->where('status', 'draft'),
                    'scheduled' => $query->where('status', 'scheduled')->whereDate('activity_date', '>', today()),
                    'ongoing' => $query->where('status', 'scheduled')->whereDate('activity_date', today()),
                    'completed' => $query->where('status', 'completed'),
                    'cancelled' => $query->where('status', 'cancelled'),
                    default => null,
                };
            })
            ->orderByDesc('activity_date')
            ->orderByDesc('start_time')
            ->paginate(10)
            ->withQueryString();

        $tests->getCollection()->transform(function (Schedule $test): Schedule {
            [$statusKey, $statusLabel] = $this->resolveScheduleStatus($test);
            $test->setAttribute('coach_status_key', $statusKey);
            $test->setAttribute('coach_status_label', $statusLabel);
            $test->setAttribute('has_unpublished_results', (int) $test->draft_results_count > 0);
            $test->setAttribute('participant_count_label', (string) $test->talent_test_participants_count);

            return $test;
        });

        return view('coach.talent-tests.index', [
            'tests' => $tests,
            'extracurricularId' => $extracurricularId,
            'status' => $status,
            'period' => $period,
            'search' => $search,
            'extracurriculars' => $coach->extracurriculars()->orderBy('name')->get(),
            'summary' => [
                'upcoming' => $summaryTests->filter(fn (Schedule $test) => $this->resolveScheduleStatus($test)[0] === 'scheduled')->count(),
                'grading' => $summaryTests->filter(fn (Schedule $test) => (int) $test->draft_results_count > 0 && $test->status !== 'cancelled')->count(),
                'unpublished' => $summaryTests->sum(fn (Schedule $test) => (int) $test->draft_results_count),
                'completed' => $summaryTests->filter(fn (Schedule $test) => $this->resolveScheduleStatus($test)[0] === 'completed')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $extracurriculars = $coach->extracurriculars()->with([
            'registrations.student.user' => fn ($query) => $query->orderBy('name'),
            'talentTestAspects',
        ])->orderBy('name')->get();

        return view('coach.talent-tests.create', [
            'extracurriculars' => $extracurriculars,
        ]);
    }

    public function store(StoreTalentTestRequest $request): RedirectResponse
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        DB::transaction(function () use ($request, $coach): void {
            $schedule = Schedule::create([
                'extracurricular_id' => $request->integer('extracurricular_id'),
                'coach_id' => $coach->id,
                'schedule_type' => 'talent_test',
                'title' => $request->string('title')->toString(),
                'activity_date' => $request->string('activity_date')->toString(),
                'start_time' => $request->string('start_time')->toString(),
                'end_time' => $request->string('end_time')->toString(),
                'location' => $request->string('location')->toString(),
                'description' => $request->string('description')->toString() ?: null,
                'equipment' => $request->string('equipment')->toString() ?: null,
                'instructions' => $request->string('instructions')->toString() ?: null,
                'status' => 'scheduled',
            ]);

            $this->syncParticipants($schedule, $request->input('participant_registration_ids', []));
            $this->notifyTalentParticipants($schedule, 'Jadwal tes bakat baru telah dibuat untuk Anda.', 'talent-test-created-'.$schedule->id);
        });

        return redirect()->route('coach.talent-tests.index')->with('success', 'Jadwal tes bakat berhasil dibuat.');
    }

    public function edit(Schedule $talentTest): View
    {
        $this->guardTalentTest($talentTest);
        $this->authorize('manageByCoach', $talentTest);

        $coach = auth()->user()->coach;
        $extracurriculars = $coach->extracurriculars()->with([
            'registrations.student.user' => fn ($query) => $query->orderBy('name'),
            'talentTestAspects',
        ])->orderBy('name')->get();

        $talentTest->load('talentTestParticipants');

        return view('coach.talent-tests.edit', [
            'talentTest' => $talentTest,
            'extracurriculars' => $extracurriculars,
        ]);
    }

    public function update(StoreTalentTestRequest $request, Schedule $talentTest): RedirectResponse
    {
        $this->guardTalentTest($talentTest);
        $this->authorize('manageByCoach', $talentTest);

        DB::transaction(function () use ($request, $talentTest): void {
            $talentTest->update([
                'extracurricular_id' => $request->integer('extracurricular_id'),
                'title' => $request->string('title')->toString(),
                'activity_date' => $request->string('activity_date')->toString(),
                'start_time' => $request->string('start_time')->toString(),
                'end_time' => $request->string('end_time')->toString(),
                'location' => $request->string('location')->toString(),
                'description' => $request->string('description')->toString() ?: null,
                'equipment' => $request->string('equipment')->toString() ?: null,
                'instructions' => $request->string('instructions')->toString() ?: null,
            ]);

            $this->syncParticipants($talentTest, $request->input('participant_registration_ids', []));
            $this->notifyTalentParticipants($talentTest, 'Jadwal tes bakat Anda telah diperbarui. Periksa detail terbaru.', 'talent-test-updated-'.$talentTest->id);
        });

        return redirect()->route('coach.talent-tests.index')->with('success', 'Jadwal tes bakat berhasil diperbarui.');
    }

    public function duplicate(Schedule $talentTest): RedirectResponse
    {
        $this->guardTalentTest($talentTest);
        $this->authorize('manageByCoach', $talentTest);

        DB::transaction(function () use ($talentTest): void {
            $duplicate = Schedule::create([
                'extracurricular_id' => $talentTest->extracurricular_id,
                'coach_id' => $talentTest->coach_id,
                'schedule_type' => 'talent_test',
                'title' => $talentTest->title.' (Salinan)',
                'activity_date' => optional($talentTest->activity_date)->toDateString(),
                'start_time' => $talentTest->start_time,
                'end_time' => $talentTest->end_time,
                'location' => $talentTest->location,
                'description' => $talentTest->description,
                'status' => 'scheduled',
                'equipment' => $talentTest->equipment,
                'instructions' => $talentTest->instructions,
                'cancelled_at' => null,
            ]);

            $participants = $talentTest->talentTestParticipants()->get(['registration_id', 'student_id']);
            foreach ($participants as $participant) {
                $duplicate->talentTestParticipants()->create([
                    'registration_id' => $participant->registration_id,
                    'student_id' => $participant->student_id,
                    'assigned_by' => auth()->id(),
                    'attendance_status' => 'pending',
                    'attendance_notes' => null,
                ]);
            }
        });

        return redirect()->route('coach.talent-tests.index')->with('success', 'Tes bakat berhasil diduplikasi.');
    }

    public function manage(Request $request, Schedule $talentTest): View
    {
        $this->guardTalentTest($talentTest);
        $this->authorize('manageByCoach', $talentTest);

        $talentTest->load([
            'extracurricular.talentTestAspects',
            'talentTestParticipants.student.user',
            'talentTestParticipants.registration',
            'talentTestResults.items',
        ]);

        $aspects = $talentTest->extracurricular->talentTestAspects->sortBy('display_order')->values();
        $resultsByStudent = $talentTest->talentTestResults->keyBy('student_id');
        $participants = $talentTest->talentTestParticipants
            ->map(function (TalentTestParticipant $participant) use ($resultsByStudent, $aspects) {
                $result = $resultsByStudent->get($participant->student_id);
                $items = $result?->items ?? collect();
                $filledAspectCount = $items->filter(fn ($item) => $item->score !== null)->count();
                $totalAspectCount = $aspects->count();
                $missingAspectCount = max($totalAspectCount - $filledAspectCount, 0);
                $attendanceStatus = $participant->attendance_status ?? 'pending';
                $isPresent = $attendanceStatus === 'present';
                $isPublished = $result?->status === 'published' || (bool) $result?->published_at;
                $isDraft = $result && ! $isPublished;
                $isAbsent = in_array($attendanceStatus, ['absent', 'sick', 'permission'], true);
                $hasDecision = filled($result?->decision_status);
                $hasRequiredAspectScore = $totalAspectCount === 0 || $filledAspectCount > 0;
                $hasCompleteResult = $isAbsent
                    || ($isPresent && $hasRequiredAspectScore && filled($result?->ability_category) && ($result?->needs_retest || $hasDecision));
                $statusFilter = $isAbsent
                    ? 'absent'
                    : ($isPublished ? 'published' : ($isDraft ? 'draft' : 'pending'));

                $participant->setAttribute('attendance_label', $this->attendanceLabel($attendanceStatus));
                $participant->setAttribute('result_status_filter', $statusFilter);
                $participant->setAttribute('result_status_label', $isAbsent ? 'Tidak hadir' : ($isPublished ? 'Dipublikasikan' : ($isDraft ? 'Draft' : 'Belum dinilai')));
                $participant->setAttribute('result_status_class', $isAbsent ? 'badge-status-danger' : ($isPublished ? 'badge-status-success' : ($isDraft ? 'badge-status-warning' : 'badge-status-secondary')));
                $participant->setAttribute('overall_score_label', $result?->overall_score !== null ? number_format((float) $result->overall_score, 2, ',', '.') : null);
                $participant->setAttribute('filled_aspect_count', $filledAspectCount);
                $participant->setAttribute('missing_aspect_count', $missingAspectCount);
                $participant->setAttribute('total_aspect_count', $totalAspectCount);
                $participant->setAttribute('decision_label', $result?->decisionLabel() ?? 'Belum diputuskan');
                $participant->setAttribute('is_publish_ready', $hasCompleteResult);
                $participant->setAttribute('publish_block_reason', $hasCompleteResult ? null : $this->buildPublishBlockReason(
                    $attendanceStatus,
                    $filledAspectCount,
                    $totalAspectCount,
                    (string) ($result?->ability_category ?? ''),
                    (string) ($result?->decision_status ?? ''),
                    (bool) ($result?->needs_retest ?? false),
                ));

                return $participant;
            })
            ->values();

        $activeParticipantId = (int) ($request->integer('participant_id') ?: old('target_participant_id') ?: optional($participants->first())->id);
        $activeParticipant = $participants->firstWhere('id', $activeParticipantId) ?: $participants->first();

        $scores = $participants
            ->map(fn (TalentTestParticipant $participant) => $resultsByStudent->get($participant->student_id)?->overall_score)
            ->filter(fn ($score) => $score !== null);

        return view('coach.talent-tests.manage', [
            'talentTest' => $talentTest,
            'participants' => $participants,
            'aspects' => $aspects,
            'resultsByStudent' => $resultsByStudent,
            'activeParticipantId' => $activeParticipant?->id,
            'summary' => [
                'total' => $participants->count(),
                'present' => $participants->where('attendance_status', 'present')->count(),
                'pending' => $participants->where('result_status_filter', 'pending')->count(),
                'draft' => $participants->where('result_status_filter', 'draft')->count(),
                'published' => $participants->where('result_status_filter', 'published')->count(),
                'average' => $scores->isNotEmpty() ? number_format((float) $scores->avg(), 2, ',', '.') : null,
            ],
            'retestSchedules' => Schedule::where('schedule_type', 'talent_test')
                ->where('extracurricular_id', $talentTest->extracurricular_id)
                ->whereKeyNot($talentTest->id)
                ->orderByDesc('activity_date')
                ->get(),
        ]);
    }

    public function saveResults(SaveTalentTestResultsRequest $request, Schedule $talentTest): RedirectResponse
    {
        $this->guardTalentTest($talentTest);
        $this->authorize('manageByCoach', $talentTest);

        $aspectMap = $talentTest->extracurricular->talentTestAspects()->get()->keyBy('id');
        $participantIds = $talentTest->talentTestParticipants()->pluck('id')->all();
        $targetParticipantId = $request->integer('target_participant_id');
        $applyBulkDecision = $request->boolean('apply_bulk_decision');
        $selectedParticipantIds = collect($request->input('selected_participant_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => in_array($id, $participantIds, true))
            ->values();

        $submittedParticipants = collect($request->input('participants', []))
            ->filter(function (array $payload) use ($participantIds, $targetParticipantId, $applyBulkDecision, $selectedParticipantIds): bool {
                $participantId = (int) ($payload['participant_id'] ?? 0);
                if (! in_array($participantId, $participantIds, true)) {
                    return false;
                }

                if ($applyBulkDecision) {
                    return $selectedParticipantIds->contains($participantId);
                }

                return $targetParticipantId === 0 || $targetParticipantId === $participantId;
            })
            ->values();

        if ($applyBulkDecision && $selectedParticipantIds->isEmpty()) {
            throw ValidationException::withMessages([
                'selected_participant_ids' => 'Pilih minimal satu peserta untuk aksi massal.',
            ]);
        }

        if ($applyBulkDecision && ! filled($request->input('bulk_decision_status'))) {
            throw ValidationException::withMessages([
                'bulk_decision_status' => 'Pilih keputusan massal yang ingin diterapkan.',
            ]);
        }

        if ($applyBulkDecision) {
            $bulkDecisionStatus = $request->string('bulk_decision_status')->toString();
            $bulkDecisionNotes = trim($request->string('bulk_decision_notes')->toString());

            $submittedParticipants = $submittedParticipants->map(function (array $payload) use ($bulkDecisionStatus, $bulkDecisionNotes): array {
                $payload['decision_status'] = $bulkDecisionStatus;
                if ($bulkDecisionNotes !== '' && ! filled($payload['decision_notes'] ?? null)) {
                    $payload['decision_notes'] = $bulkDecisionNotes;
                }

                return $payload;
            })->values();
        }

        if ($submittedParticipants->isEmpty()) {
            throw ValidationException::withMessages([
                'participants' => 'Peserta tes yang dipilih tidak valid.',
            ]);
        }

        if ($request->boolean('publish')) {
            foreach ($submittedParticipants as $payload) {
                $this->ensureParticipantResultIsReadyForPublish($payload, $aspectMap);
            }
        }

        DB::transaction(function () use ($request, $talentTest, $submittedParticipants, $aspectMap): void {
            foreach ($submittedParticipants as $payload) {
                /** @var TalentTestParticipant $participant */
                $participant = $talentTest->talentTestParticipants()->with('registration')->findOrFail($payload['participant_id']);
                $participant->update([
                    'attendance_status' => $payload['attendance_status'],
                    'attendance_notes' => $payload['attendance_notes'] ?? null,
                ]);

                $scores = collect($payload['scores'] ?? [])
                    ->filter(fn ($value, $key) => $aspectMap->has((int) $key) && $value !== null && $value !== '');
                $overallScore = $scores->isNotEmpty() ? round((float) $scores->avg(), 2) : null;

                $isPublished = $request->boolean('publish');
                $decisionStatus = $payload['decision_status'] ?? null;
                $decisionNotes = isset($payload['decision_notes']) && trim((string) $payload['decision_notes']) !== ''
                    ? trim((string) $payload['decision_notes'])
                    : null;
                $needsRetest = (bool) ($payload['needs_retest'] ?? false);

                $result = TalentTestResult::updateOrCreate(
                    [
                        'schedule_id' => $talentTest->id,
                        'student_id' => $participant->student_id,
                    ],
                    [
                        'registration_id' => $participant->registration_id,
                        'coach_id' => auth()->user()->coach->id,
                        'status' => $isPublished ? 'published' : 'draft',
                        'overall_score' => $overallScore,
                        'ability_category' => $payload['ability_category'] ?? null,
                        'decision_status' => $decisionStatus,
                        'decision_notes' => $decisionNotes,
                        'training_group' => $payload['training_group'] ?? null,
                        'recommended_role' => $payload['recommended_role'] ?? null,
                        'recommendation' => $payload['recommendation'] ?? null,
                        'coach_notes' => $payload['coach_notes'] ?? null,
                        'internal_notes' => $payload['internal_notes'] ?? null,
                        'needs_retest' => $needsRetest,
                        'retest_schedule_id' => $payload['retest_schedule_id'] ?? null,
                        'decided_at' => $decisionStatus ? now() : null,
                        'evaluated_at' => now(),
                        'published_at' => $isPublished ? now() : null,
                    ]
                );

                foreach ($aspectMap as $aspectId => $aspect) {
                    $scoreValue = $payload['scores'][$aspectId] ?? null;
                    $scoreNote = $payload['score_notes'][$aspectId] ?? null;

                    $result->items()->updateOrCreate(
                        ['talent_test_aspect_id' => $aspectId],
                        [
                            'score' => $scoreValue !== null && $scoreValue !== '' ? $scoreValue : null,
                            'notes' => $scoreNote ?: null,
                        ]
                    );
                }

                if ($isPublished) {
                    $this->syncRegistrationDecision(
                        $participant->registration,
                        $decisionStatus,
                        $decisionNotes,
                        (string) ($payload['coach_notes'] ?? ''),
                        $needsRetest
                    );
                }
            }

            if ($request->boolean('publish')) {
                $hasRemainingDraft = $talentTest->talentTestResults()
                    ->where('status', 'draft')
                    ->exists();

                if (! $hasRemainingDraft) {
                    $talentTest->update(['status' => 'completed']);
                }
            }
        });

        $successMessage = $request->boolean('publish')
            ? 'Hasil peserta berhasil dipublikasikan.'
            : 'Draft hasil peserta berhasil disimpan.';

        if ($applyBulkDecision) {
            $successMessage = $request->boolean('publish')
                ? 'Keputusan massal dan hasil peserta berhasil dipublikasikan.'
                : 'Keputusan massal peserta berhasil disimpan.';
        }

        if ($request->boolean('publish')) {
            $participantIds = $submittedParticipants->pluck('participant_id')->all();
            $users = $talentTest->talentTestParticipants()
                ->with('student.user')
                ->whereIn('id', $participantIds)
                ->get()
                ->pluck('student.user')
                ->filter();

            app(NotificationCenter::class)->notifyUsers($users, [
                'title' => 'Hasil tes bakat tersedia',
                'message' => "Hasil tes bakat untuk {$talentTest->extracurricular->name} telah tersedia.",
                'url' => route('student.talent-tests.index'),
                'category' => NotificationPreference::CATEGORY_TALENT_TEST,
                'icon' => 'bi-clipboard2-pulse',
                'tag' => 'talent-result-'.$talentTest->id,
            ]);
        }

        return back()->with('success', $successMessage);
    }

    public function cancel(Request $request, Schedule $talentTest): RedirectResponse
    {
        $this->guardTalentTest($talentTest);
        $this->authorize('manageByCoach', $talentTest);

        $request->validate([
            'reason' => ['nullable', 'string'],
        ]);

        $talentTest->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'description' => trim(($talentTest->description ? $talentTest->description.PHP_EOL.PHP_EOL : '').'Dibatalkan: '.($request->input('reason') ?: 'Tanpa alasan tambahan')),
        ]);

        $this->notifyTalentParticipants($talentTest, 'Jadwal tes bakat dibatalkan. Silakan tunggu pembaruan berikutnya dari pembina.', 'talent-test-cancelled-'.$talentTest->id, NotificationPreference::CATEGORY_SCHEDULE_CHANGES);

        return back()->with('success', 'Jadwal tes bakat berhasil dibatalkan.');
    }

    public function destroy(Schedule $talentTest): RedirectResponse
    {
        $this->guardTalentTest($talentTest);
        $this->authorize('manageByCoach', $talentTest);

        if (! in_array($talentTest->status, ['draft', 'scheduled', 'cancelled'], true)) {
            return back()->with('error', 'Tes bakat yang sudah selesai diproses tidak dapat dihapus.');
        }

        $talentTest->delete();

        return redirect()->route('coach.talent-tests.index')->with('success', 'Tes bakat berhasil dihapus.');
    }

    public function aspectIndex(Request $request): View
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $extracurricularId = $request->string('extracurricular_id')->toString()
            ?: (string) optional($coach->extracurriculars()->orderBy('name')->first())->id;

        $extracurricular = $coach->extracurriculars()
            ->with('talentTestAspects')
            ->findOrFail($extracurricularId);

        return view('coach.talent-tests.aspects', [
            'extracurricular' => $extracurricular,
            'extracurriculars' => $coach->extracurriculars()->orderBy('name')->get(),
        ]);
    }

    public function storeAspect(Request $request, Extracurricular $extracurricular): RedirectResponse
    {
        $this->authorize('viewByCoach', $extracurricular);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_score' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $extracurricular->talentTestAspects()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'max_score' => $validated['max_score'] ?? 100,
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Aspek tes bakat berhasil ditambahkan.');
    }

    public function updateAspect(Request $request, Extracurricular $extracurricular, TalentTestAspect $aspect): RedirectResponse
    {
        $this->authorize('viewByCoach', $extracurricular);
        abort_unless($aspect->extracurricular_id === $extracurricular->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_score' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $aspect->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'max_score' => $validated['max_score'] ?? 100,
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Aspek tes bakat berhasil diperbarui.');
    }

    public function destroyAspect(Extracurricular $extracurricular, TalentTestAspect $aspect): RedirectResponse
    {
        $this->authorize('viewByCoach', $extracurricular);
        abort_unless($aspect->extracurricular_id === $extracurricular->id, 404);

        $aspect->delete();

        return back()->with('success', 'Aspek tes bakat berhasil dihapus.');
    }

    private function syncParticipants(Schedule $schedule, array $registrationIds): void
    {
        $registrations = Registration::with('student')
            ->whereIn('id', $registrationIds)
            ->where('extracurricular_id', $schedule->extracurricular_id)
            ->whereIn('status', ['pending', 'approved'])
            ->get();

        $allowedIds = $registrations->pluck('id')->all();
        abort_unless(count($allowedIds) === count(array_unique(array_map('intval', $registrationIds))), 422, 'Peserta tes tidak valid.');

        $existingStudentIds = [];
        foreach ($registrations as $registration) {
            $existingStudentIds[] = $registration->student_id;
            $schedule->talentTestParticipants()->updateOrCreate(
                ['student_id' => $registration->student_id],
                [
                    'registration_id' => $registration->id,
                    'assigned_by' => auth()->id(),
                ]
            );
        }

        $schedule->talentTestParticipants()
            ->whereNotIn('student_id', $existingStudentIds)
            ->delete();
    }

    private function ensureParticipantResultIsReadyForPublish(array $payload, Collection $aspectMap): void
    {
        $attendanceStatus = (string) ($payload['attendance_status'] ?? 'pending');
        if (in_array($attendanceStatus, ['absent', 'sick', 'permission'], true)) {
            return;
        }

        if ($attendanceStatus !== 'present') {
            throw ValidationException::withMessages([
                'publish' => 'Status kehadiran peserta aktif harus ditentukan sebelum publikasi.',
            ]);
        }

        $scores = collect($payload['scores'] ?? []);
        $filledAspectCount = $aspectMap->filter(function ($aspect, $aspectId) use ($scores): bool {
            $value = $scores->get($aspectId);
            if ($value === null || $value === '') {
                return false;
            }

            return (float) $value >= 0 && (float) $value <= (float) $aspect->max_score;
        })->count();

        if ($aspectMap->isNotEmpty() && $filledAspectCount === 0) {
            throw ValidationException::withMessages([
                'publish' => 'Isi minimal satu aspek penilaian sebelum hasil peserta dipublikasikan.',
            ]);
        }

        if (! filled($payload['ability_category'] ?? null)) {
            throw ValidationException::withMessages([
                'publish' => 'Kategori kemampuan wajib diisi sebelum hasil peserta dipublikasikan.',
            ]);
        }

        if (! ($payload['needs_retest'] ?? false) && ! filled($payload['decision_status'] ?? null)) {
            throw ValidationException::withMessages([
                'publish' => 'Pilih keputusan akhir peserta atau tandai perlu tes ulang sebelum publikasi.',
            ]);
        }
    }

    private function resolveScheduleStatus(Schedule $schedule): array
    {
        if ($schedule->status === 'cancelled') {
            return ['cancelled', 'Dibatalkan'];
        }

        if ($schedule->status === 'completed') {
            return ['completed', 'Selesai'];
        }

        if ($schedule->status === 'draft') {
            return ['draft', 'Draft'];
        }

        if ($schedule->activity_date instanceof Carbon && $schedule->activity_date->isToday()) {
            return ['ongoing', 'Sedang Berlangsung'];
        }

        return ['scheduled', 'Dijadwalkan'];
    }

    private function attendanceLabel(?string $status): string
    {
        return match ($status) {
            'present' => 'Hadir',
            'absent' => 'Tidak Hadir',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            default => 'Belum Diisi',
        };
    }

    private function buildPublishBlockReason(
        string $attendanceStatus,
        int $filledAspectCount,
        int $totalAspectCount,
        string $abilityCategory,
        string $decisionStatus,
        bool $needsRetest
    ): ?string
    {
        if (in_array($attendanceStatus, ['absent', 'sick', 'permission'], true)) {
            return null;
        }

        if ($attendanceStatus !== 'present') {
            return 'Tentukan status kehadiran peserta terlebih dahulu.';
        }

        if ($totalAspectCount > 0 && $filledAspectCount === 0) {
            return 'Isi minimal satu aspek penilaian.';
        }

        if ($abilityCategory === '') {
            return 'Kategori kemampuan belum diisi.';
        }

        if (! $needsRetest && $decisionStatus === '') {
            return 'Keputusan akhir belum dipilih.';
        }

        return null;
    }

    private function syncRegistrationDecision(
        ?Registration $registration,
        ?string $decisionStatus,
        ?string $decisionNotes,
        string $coachNotes,
        bool $needsRetest
    ): void {
        if (! $registration) {
            return;
        }

        if ($needsRetest) {
            $registration->update([
                'status' => Registration::STATUS_PENDING,
                'willing_to_take_test' => true,
                'notes' => $decisionNotes ?: ($coachNotes !== '' ? $coachNotes : $registration->notes),
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            return;
        }

        if (! in_array($decisionStatus, ['accepted', 'rejected'], true)) {
            return;
        }

        $registration->update([
            'status' => $decisionStatus === 'accepted'
                ? Registration::STATUS_APPROVED
                : Registration::STATUS_REJECTED,
            'willing_to_take_test' => false,
            'notes' => $decisionNotes ?: ($coachNotes !== '' ? $coachNotes : $registration->notes),
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);
    }

    private function guardTalentTest(Schedule $schedule): void
    {
        abort_unless($schedule->schedule_type === 'talent_test', 404);
    }

    private function notifyTalentParticipants(
        Schedule $schedule,
        string $message,
        string $tag,
        string $category = NotificationPreference::CATEGORY_TALENT_TEST
    ): void {
        $users = $schedule->talentTestParticipants()
            ->with('student.user')
            ->get()
            ->pluck('student.user')
            ->filter();

        app(NotificationCenter::class)->notifyUsers($users, [
            'title' => $schedule->title ?: 'Pembaruan tes bakat',
            'message' => $message,
            'url' => route('student.talent-tests.index'),
            'category' => $category,
            'icon' => 'bi-clipboard2-pulse',
            'tag' => $tag,
        ]);
    }
}
