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
        $search = $request->string('search')->toString();

        $coaches = Coach::with(['user', 'extracurriculars'])
            ->when($search, function ($query, $searchValue) {
                $query->where(function ($coachQuery) use ($searchValue): void {
                    $coachQuery->where('nip', 'like', "%{$searchValue}%")
                        ->orWhereHas('user', function ($userQuery) use ($searchValue): void {
                            $userQuery->where('name', 'like', "%{$searchValue}%")
                                ->orWhere('email', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('extracurriculars', function ($extracurricularQuery) use ($searchValue): void {
                            $extracurricularQuery->where('name', 'like', "%{$searchValue}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.coaches.index', compact('coaches', 'search'));
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
        $coach->load('user', 'extracurriculars.coaches.user');

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
        $affectedExtracurricularIds = $coach->extracurriculars()->pluck('extracurriculars.id')->all();
        $coach->extracurriculars()->detach();
        Extracurricular::where('coach_id', $coach->id)->update(['coach_id' => null]);
        $this->syncLegacyCoachColumn($affectedExtracurricularIds);
        $coach->user?->delete();

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
