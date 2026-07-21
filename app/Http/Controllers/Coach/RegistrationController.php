<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function index(Request $request): View
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([
                Registration::STATUS_PENDING,
                'waiting_test',
                Registration::STATUS_APPROVED,
                Registration::STATUS_REJECTED,
            ])],
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'category' => ['nullable', 'string', Rule::in(['all', ...array_keys(Extracurricular::categoryDefinitions())])],
        ]);

        $filters['class_name'] = Student::normalizeClassName($filters['class_name'] ?? null);
        $filters['category'] = $filters['category'] ?? 'all';

        $search = $filters['search'] ?? '';
        $status = $filters['status'] ?? '';
        $extracurricularId = (string) ($filters['extracurricular_id'] ?? '');
        $ownedExtracurricularIds = $coach->extracurriculars()->pluck('extracurriculars.id');

        $registrations = $this->filteredStudentsQuery($filters, $ownedExtracurricularIds)
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('coach.registrations.index', [
            'registrations' => $registrations,
            'search' => $search,
            'status' => $status,
            'extracurricularId' => $extracurricularId,
            'className' => $filters['class_name'] ?? '',
            'gender' => $filters['gender'] ?? '',
            'category' => $filters['category'] ?? 'all',
            'extracurriculars' => Extracurricular::whereIn('id', $ownedExtracurricularIds)->orderBy('name')->get(),
            'classOptions' => collect(array_keys(Student::registrationClassOptions())),
            'categories' => collect(Extracurricular::categoryDefinitions())
                ->map(fn (array $definition) => ['key' => $definition['key'], 'label' => $definition['label']])
                ->values(),
        ]);
    }

    private function filteredStudentsQuery(array $filters, $ownedExtracurricularIds)
    {
        return Student::with([
            'user',
            'registrations' => function ($query) use ($filters, $ownedExtracurricularIds): void {
                $this->applyRegistrationFilters(
                    $query->with(['extracurricular', 'verifier', 'talentTestResults', 'talentTestParticipants.schedule'])
                        ->whereIn('extracurricular_id', $ownedExtracurricularIds),
                    $filters
                );
            },
        ])
            ->whereHas('registrations', function ($query) use ($filters, $ownedExtracurricularIds): void {
                $this->applyRegistrationFilters(
                    $query->whereIn('extracurricular_id', $ownedExtracurricularIds),
                    $filters
                );
            })
            ->when($filters['search'] ?? null, function ($query, $searchValue) {
                $query->where(function ($studentQuery) use ($searchValue): void {
                    $studentQuery->where('nis', 'like', "%{$searchValue}%")
                        ->orWhere('class_name', 'like', "%{$searchValue}%")
                        ->orWhereHas('user', function ($userQuery) use ($searchValue): void {
                            $userQuery->where('name', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('registrations.extracurricular', function ($activityQuery) use ($searchValue): void {
                            $activityQuery->where('name', 'like', "%{$searchValue}%");
                        });
                });
            })
            ->when($filters['class_name'] ?? null, function ($query, $className): void {
                $query->whereRaw(
                    Student::normalizedClassExpression('class_name').' = ?',
                    [Student::normalizedClassComparable($className)]
                );
            })
            ->when($filters['gender'] ?? null, fn ($query, $gender) => $query->where('gender', $gender));
    }

    private function applyRegistrationFilters($query, array $filters): void
    {
        $query
            ->when($filters['status'] ?? null, function ($query, $statusValue): void {
                if ($statusValue === 'waiting_test') {
                    $query->where('status', Registration::STATUS_APPROVED)
                        ->where('willing_to_take_test', true)
                        ->whereDoesntHave('talentTestResults', fn ($resultQuery) => $resultQuery->where('status', 'published'));

                    return;
                }

                if ($statusValue === Registration::STATUS_APPROVED) {
                    $query->where('status', Registration::STATUS_APPROVED)
                        ->where(function ($approvedQuery): void {
                            $approvedQuery->where('willing_to_take_test', false)
                                ->orWhereHas('talentTestResults', fn ($resultQuery) => $resultQuery->where('status', 'published'));
                        });

                    return;
                }

                $query->where('status', $statusValue);
            })
            ->when($filters['extracurricular_id'] ?? null, fn ($query, $idValue) => $query->where('extracurricular_id', $idValue))
            ->when(($filters['category'] ?? 'all') !== 'all', function ($query) use ($filters): void {
                $ids = Extracurricular::idsForCategory($filters['category']);

                if ($ids === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->whereIn('extracurricular_id', $ids);
            })
            ->latest('registration_date')
            ->latest('id');
    }

    public function show(Registration $registration): View
    {
        $this->authorize('manageByCoach', $registration);

        $registration->load([
            'student.user',
            'extracurricular.coaches.user',
            'talentTestParticipants.schedule',
            'talentTestResults.schedule',
        ]);

        return view('coach.registrations.show', compact('registration'));
    }

    public function redirectStatus(): RedirectResponse
    {
        return redirect()->route('coach.registrations.index');
    }

    public function updateStatus(Request $request, Registration $registration): RedirectResponse
    {
        $this->authorize('manageByCoach', $registration);

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                Registration::STATUS_PENDING,
                Registration::STATUS_APPROVED,
                Registration::STATUS_REJECTED,
            ])],
            'notes' => ['nullable', 'string'],
        ]);

        $registration->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Status pendaftaran berhasil diperbarui.');
    }
}
