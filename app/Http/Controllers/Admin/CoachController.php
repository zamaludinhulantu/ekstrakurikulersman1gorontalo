<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\Extracurricular;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CoachController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'extracurricular_id' => ['nullable', 'integer', 'exists:extracurriculars,id'],
            'assignment' => ['nullable', Rule::in(['assigned', 'unassigned'])],
            'profile_status' => ['nullable', Rule::in(['complete', 'incomplete'])],
            'sort' => ['nullable', Rule::in(['name', 'nip', 'status', 'activities_count', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 50, 100])],
        ]);

        $query = Coach::query()
            ->with([
                'user' => fn ($query) => $query->withCount(['announcements', 'articles', 'generatedReports']),
                'extracurriculars' => fn ($query) => $query->orderBy('name'),
            ])
            ->withCount(['extracurriculars', 'schedules', 'assessments', 'talentTestResults'])
            ->when($filters['search'] ?? null, function ($query, $searchValue) {
                $query->where(function ($coachQuery) use ($searchValue): void {
                    $coachQuery->where('nip', 'like', "%{$searchValue}%")
                        ->orWhereHas('user', function ($userQuery) use ($searchValue): void {
                            $userQuery->where('name', 'like', "%{$searchValue}%")
                                ->orWhere('email', 'like', "%{$searchValue}%");
                        });
                });
            })
            ->when($filters['status'] ?? null, function ($query, $status): void {
                $query->whereHas('user', fn ($userQuery) => $userQuery->where('is_active', $status === 'active'));
            })
            ->when($filters['extracurricular_id'] ?? null, function ($query, $extracurricularId): void {
                $query->whereHas('extracurriculars', fn ($activityQuery) => $activityQuery->whereKey($extracurricularId));
            })
            ->when($filters['assignment'] ?? null, function ($query, $assignment): void {
                $assignment === 'assigned'
                    ? $query->has('extracurriculars')
                    : $query->doesntHave('extracurriculars');
            })
            ->when($filters['profile_status'] ?? null, function ($query, $profileStatus): void {
                $incomplete = function ($profileQuery): void {
                    $profileQuery->whereNull('nip')
                        ->orWhere('nip', '')
                        ->orWhereDoesntHave('user', function ($userQuery): void {
                            $userQuery->whereNotNull('name')
                                ->where('name', '!=', '')
                                ->whereNotNull('email')
                                ->where('email', '!=', '');
                        });
                };

                $profileStatus === 'incomplete'
                    ? $query->where($incomplete)
                    : $query->whereNot($incomplete);
            });

        $sort = $filters['sort'] ?? 'created_at';
        $direction = $filters['direction'] ?? 'desc';
        $query = match ($sort) {
            'name' => $query->orderBy(
                User::select('name')->whereColumn('users.id', 'coaches.user_id'),
                $direction
            ),
            'status' => $query->orderBy(
                User::select('is_active')->whereColumn('users.id', 'coaches.user_id'),
                $direction
            ),
            'nip' => $query->orderBy('nip', $direction),
            'activities_count' => $query->orderBy('extracurriculars_count', $direction),
            default => $query->orderBy('created_at', $direction),
        };

        $coaches = $query
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();

        return view('admin.coaches.index', [
            'coaches' => $coaches,
            'search' => $filters['search'] ?? '',
            'status' => $filters['status'] ?? '',
            'extracurricularId' => $filters['extracurricular_id'] ?? null,
            'assignment' => $filters['assignment'] ?? '',
            'profileStatus' => $filters['profile_status'] ?? '',
            'sort' => $sort,
            'direction' => $direction,
            'perPage' => $filters['per_page'] ?? 20,
            'extracurricularOptions' => Extracurricular::query()->orderBy('name')->get(['id', 'name']),
            'coachSummary' => [
                'total' => Coach::query()->count(),
                'active' => Coach::query()->whereHas('user', fn ($query) => $query->where('is_active', true))->count(),
                'inactive' => Coach::query()->whereHas('user', fn ($query) => $query->where('is_active', false))->count(),
                'unassigned' => Coach::query()->doesntHave('extracurriculars')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.coaches.create', [
            'extracurriculars' => Extracurricular::with(['coach.user', 'coaches.user'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        DB::transaction(function () use ($validated, $request): void {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => User::ROLE_COACH,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            Coach::create([
                'user_id' => $user->id,
                'nip' => $validated['nip'],
                'bio' => $validated['bio'] ?? null,
            ])->fresh();

            $coach = Coach::with('extracurriculars')->where('user_id', $user->id)->firstOrFail();
            $this->syncExtracurricularAssignments($coach, $validated['extracurricular_ids'] ?? []);
        });

        return redirect()->route('admin.coaches.index')->with('success', 'Data pembina berhasil ditambahkan.');
    }

    public function show(Coach $coach): View
    {
        $coach->loadCount(['schedules', 'assessments', 'talentTestResults'])
            ->load('user', 'extracurriculars.coaches.user');

        return view('admin.coaches.show', compact('coach'));
    }

    public function edit(Coach $coach): View
    {
        $coach->load('user', 'extracurriculars');

        return view('admin.coaches.edit', [
            'coach' => $coach,
            'extracurriculars' => Extracurricular::with(['coach.user', 'coaches.user'])->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Coach $coach): RedirectResponse
    {
        $coach->load('user');
        $validated = $this->validatePayload($request, $coach);

        DB::transaction(function () use ($validated, $request, $coach): void {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'is_active' => $request->boolean('is_active'),
            ];

            if (filled($validated['password'] ?? null)) {
                $userData['password'] = $validated['password'];
            }

            $coach->user->update($userData);

            $coach->update([
                'nip' => $validated['nip'],
                'bio' => $validated['bio'] ?? null,
            ]);

            $this->syncExtracurricularAssignments($coach, $validated['extracurricular_ids'] ?? []);
        });

        return redirect()->route('admin.coaches.index')->with('success', 'Data pembina berhasil diperbarui.');
    }

    public function destroy(Coach $coach): RedirectResponse
    {
        $coach->load('user');
        $hasHistory = $coach->extracurriculars()->exists()
            || Extracurricular::query()->where('coach_id', $coach->id)->exists()
            || $coach->schedules()->exists()
            || $coach->assessments()->exists()
            || $coach->talentTestResults()->exists()
            || $coach->user?->announcements()->exists()
            || $coach->user?->articles()->exists()
            || $coach->user?->generatedReports()->exists();

        if ($hasHistory) {
            return redirect()
                ->route('admin.coaches.index')
                ->with(
                    'error',
                    'Pembina tidak dapat dihapus karena masih memiliki penugasan atau riwayat operasional. Lepas penugasan atau nonaktifkan akun melalui menu Edit agar laporan tetap utuh.'
                );
        }

        DB::transaction(function () use ($coach): void {
            $coach->user?->delete();
        });

        return redirect()->route('admin.coaches.index')->with('success', 'Data pembina berhasil dihapus.');
    }

    private function validatePayload(Request $request, ?Coach $coach = null): array
    {
        $userId = $coach?->user_id;

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'password' => [$coach ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
            'nip' => ['required', 'string', 'max:50', Rule::unique('coaches', 'nip')->ignore($coach?->id)],
            'bio' => ['nullable', 'string'],
            'extracurricular_ids' => ['nullable', 'array'],
            'extracurricular_ids.*' => ['integer', 'exists:extracurriculars,id'],
        ]);
    }

    private function syncExtracurricularAssignments(Coach $coach, array $selectedExtracurricularIds): void
    {
        $previousIds = $coach->extracurriculars()->pluck('extracurriculars.id')->all();
        $selectedIds = array_map('intval', $selectedExtracurricularIds);
        $coach->extracurriculars()->sync($selectedIds);

        if ($selectedIds !== []) {
            Extracurricular::whereIn('id', $selectedIds)
                ->whereNull('coach_id')
                ->update(['coach_id' => $coach->id]);
        }

        $this->syncLegacyCoachColumn(array_values(array_unique([...$previousIds, ...$selectedIds])));
    }

    private function syncLegacyCoachColumn(array $extracurricularIds): void
    {
        foreach ($extracurricularIds as $extracurricularId) {
            $extracurricular = Extracurricular::with('coaches')->find($extracurricularId);
            if (! $extracurricular) {
                continue;
            }

            $fallbackCoachId = $extracurricular->coaches->pluck('id')->first();
            if ($extracurricular->coach_id !== $fallbackCoachId) {
                $extracurricular->update(['coach_id' => $fallbackCoachId]);
            }
        }
    }
}
