<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\Registration;
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

        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $extracurricularId = $request->string('extracurricular_id')->toString();
        $ownedExtracurricularIds = $coach->extracurriculars()->pluck('extracurriculars.id');

        $registrations = Registration::with(['student.user', 'extracurricular', 'verifier'])
            ->whereIn('extracurricular_id', $ownedExtracurricularIds)
            ->when($search, function ($query, $searchValue) {
                $query->whereHas('student.user', function ($userQuery) use ($searchValue): void {
                    $userQuery->where('name', 'like', "%{$searchValue}%");
                });
            })
            ->when($status, fn ($query, $statusValue) => $query->where('status', $statusValue))
            ->when($extracurricularId, fn ($query, $idValue) => $query->where('extracurricular_id', $idValue))
            ->latest('registration_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('coach.registrations.index', [
            'registrations' => $registrations,
            'search' => $search,
            'status' => $status,
            'extracurricularId' => $extracurricularId,
            'extracurriculars' => Extracurricular::whereIn('id', $ownedExtracurricularIds)->orderBy('name')->get(),
        ]);
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
