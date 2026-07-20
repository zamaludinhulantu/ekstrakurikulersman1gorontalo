<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SanitizesCsvExports;
use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\Registration;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationController extends Controller
{
    use SanitizesCsvExports;

    public function index(Request $request): View
    {
        $filters = $this->validateFilters($request);

        $registrations = $this->filteredRegistrationsQuery($filters)
            ->paginate(10)
            ->withQueryString();

        return view('admin.registrations.index', [
            'registrations' => $registrations,
            'search' => $filters['search'] ?? '',
            'status' => $filters['status'] ?? '',
            'extracurricularId' => $filters['extracurricular_id'] ?? '',
            'extracurriculars' => Extracurricular::orderBy('name')->get(),
        ]);
    }

    public function show(Registration $registration): View
    {
        $registration->load([
            'student.user',
            'extracurricular.coaches.user',
            'talentTestParticipants.schedule',
            'talentTestResults.schedule',
        ]);

        return view('admin.registrations.show', compact('registration'));
    }

    public function redirectStatus(): RedirectResponse
    {
        return redirect()->route('admin.registrations.index');
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validateFilters($request, true);
        $format = $filters['format'] ?? 'xls';
        $registrations = $this->filteredRegistrationsQuery($filters)->get();
        $timestamp = Carbon::now()->format('YmdHis');

        if ($format === 'pdf') {
            $html = view('admin.registrations.export-pdf', [
                'registrations' => $registrations,
                'filters' => $filters,
            ])->render();

            $options = new Options();
            $options->set('isRemoteEnabled', false);
            $options->set('defaultFont', 'DejaVu Sans');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A3', 'landscape');
            $dompdf->render();

            return response()->streamDownload(
                static function () use ($dompdf): void {
                    echo $dompdf->output();
                },
                'pendaftar-ekstrakurikuler-'.$timestamp.'.pdf',
                ['Content-Type' => 'application/pdf']
            );
        }

        $filename = 'pendaftar-ekstrakurikuler-'.$timestamp.'.xls';
        $html = view('admin.registrations.export-xls', [
            'registrations' => $registrations,
            'filters' => $filters,
        ])->render();

        return response()->streamDownload(function () use ($html): void {
            echo "\xEF\xBB\xBF";
            echo $html;
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function updateStatus(Request $request, Registration $registration): RedirectResponse
    {
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

    private function validateFilters(Request $request, bool $includeFormat = false): array
    {
        $rules = [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([
                Registration::STATUS_PENDING,
                'waiting_test',
                Registration::STATUS_APPROVED,
                Registration::STATUS_REJECTED,
            ])],
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
        ];

        if ($includeFormat) {
            $rules['format'] = ['nullable', Rule::in(['pdf', 'xls'])];
        }

        return $request->validate($rules);
    }

    private function filteredRegistrationsQuery(array $filters)
    {
        return Registration::with(['student.user', 'extracurricular', 'verifier', 'talentTestResults'])
            ->when($filters['search'] ?? null, function ($query, $searchValue) {
                $query->where(function ($searchQuery) use ($searchValue): void {
                    $searchQuery->whereHas('student.user', function ($userQuery) use ($searchValue): void {
                        $userQuery->where('name', 'like', "%{$searchValue}%");
                    })->orWhereHas('student', function ($studentQuery) use ($searchValue): void {
                        $studentQuery->where('nis', 'like', "%{$searchValue}%");
                    });
                });
            })
            ->with(['talentTestParticipants.schedule'])
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
            ->latest();
    }
}
