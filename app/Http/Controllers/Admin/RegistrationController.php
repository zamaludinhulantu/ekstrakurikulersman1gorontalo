<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SanitizesCsvExports;
use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\Student;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationController extends Controller
{
    use SanitizesCsvExports;

    public function index(Request $request): View
    {
        $filters = $this->validateFilters($request);

        $students = $this->filteredStudentsQuery($filters)
            ->paginate(10)
            ->withQueryString();

        $groupedRegistrations = $students->getCollection()
            ->map(function (Student $student): array {
                $studentRegistrations = $student->registrations
                    ->sortByDesc(fn (Registration $registration) => optional($registration->registration_date)->timestamp ?? 0)
                    ->values();

                return [
                    'student' => $student,
                    'registrations' => $studentRegistrations,
                    'latest_registration' => $studentRegistrations->first(),
                ];
            })
            ->values();

        return view('admin.registrations.index', [
            'registrations' => $students,
            'groupedRegistrations' => $groupedRegistrations,
            'search' => $filters['search'] ?? '',
            'status' => $filters['status'] ?? '',
            'extracurricularId' => $filters['extracurricular_id'] ?? '',
            'className' => $filters['class_name'] ?? '',
            'gender' => $filters['gender'] ?? '',
            'category' => $filters['category'] ?? 'all',
            'extracurriculars' => Extracurricular::orderBy('name')->get(),
            'classOptions' => collect(array_keys(Student::registrationClassOptions())),
            'categories' => collect(Extracurricular::categoryDefinitions())
                ->map(fn (array $definition) => ['key' => $definition['key'], 'label' => $definition['label']])
                ->values(),
            'statusMap' => $this->statusLabels(),
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
        $students = $this->filteredStudentsQuery($filters)->get();
        $timestamp = Carbon::now()->format('YmdHis');
        $filterSummary = $this->filterSummary($filters);
        $filenameBase = $this->exportFilenameBase($filters, $filterSummary);

        if ($format === 'pdf') {
            $html = view('admin.registrations.export-pdf', [
                'students' => $students,
                'filters' => $filters,
                'filterSummary' => $filterSummary,
                'statusMap' => $this->statusLabels(),
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
                $filenameBase.'-'.$timestamp.'.pdf',
                ['Content-Type' => 'application/pdf']
            );
        }

        $filename = $filenameBase.'-'.$timestamp.'.xls';
        $html = view('admin.registrations.export-xls', [
            'students' => $students,
            'filters' => $filters,
            'filterSummary' => $filterSummary,
            'statusMap' => $this->statusLabels(),
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
            'class_name' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'status' => ['nullable', Rule::in([
                Registration::STATUS_PENDING,
                'waiting_test',
                'scheduled_test',
                Registration::STATUS_APPROVED,
                Registration::STATUS_REJECTED,
            ])],
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'category' => ['nullable', 'string', Rule::in(['all', ...array_keys(Extracurricular::categoryDefinitions())])],
        ];

        if ($includeFormat) {
            $rules['format'] = ['nullable', Rule::in(['pdf', 'xls'])];
        }

        $validated = $request->validate($rules);
        $validated['class_name'] = Student::normalizeClassName($validated['class_name'] ?? null);
        $validated['category'] = $validated['category'] ?? 'all';

        return $validated;
    }

    private function filteredRegistrationsQuery(array $filters)
    {
        return Registration::with(['student.user', 'extracurricular', 'verifier', 'talentTestResults'])
            ->when($filters['search'] ?? null, function ($query, $searchValue) {
                $query->where(function ($searchQuery) use ($searchValue): void {
                    $searchQuery->whereHas('student.user', function ($userQuery) use ($searchValue): void {
                        $userQuery->where('name', 'like', "%{$searchValue}%")
                            ->orWhere('email', 'like', "%{$searchValue}%");
                    })->orWhereHas('student', function ($studentQuery) use ($searchValue): void {
                        $studentQuery->where('nis', 'like', "%{$searchValue}%")
                            ->orWhere('class_name', 'like', "%{$searchValue}%");
                    })->orWhereHas('extracurricular', function ($activityQuery) use ($searchValue): void {
                        $activityQuery->where('name', 'like', "%{$searchValue}%");
                    })->orWhere('selected_branch', 'like', "%{$searchValue}%");
                });
            })
            ->when($filters['class_name'] ?? null, function ($query, $className): void {
                $query->whereHas('student', function ($studentQuery) use ($className): void {
                    $studentQuery->whereRaw(
                        Student::normalizedClassExpression('class_name').' = ?',
                        [Student::normalizedClassComparable($className)]
                    );
                });
            })
            ->when($filters['gender'] ?? null, function ($query, $gender): void {
                $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('gender', $gender));
            })
            ->with(['talentTestParticipants.schedule'])
            ->when($filters['status'] ?? null, function ($query, $statusValue): void {
                if ($statusValue === 'waiting_test') {
                    $query->where('status', Registration::STATUS_APPROVED)
                        ->where('willing_to_take_test', true)
                        ->whereDoesntHave('talentTestResults', fn ($resultQuery) => $resultQuery->where('status', 'published'));

                    return;
                }

                if ($statusValue === 'scheduled_test') {
                    $query->where('status', Registration::STATUS_APPROVED)
                        ->where('willing_to_take_test', true)
                        ->whereHas('talentTestParticipants')
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
            ->latest();
    }

    private function filteredStudentsQuery(array $filters)
    {
        return Student::with([
            'user',
            'registrations' => function ($query) use ($filters): void {
                $this->applyRegistrationFilters(
                    $query->with(['extracurricular', 'verifier', 'talentTestResults', 'talentTestParticipants.schedule']),
                    $filters
                );
            },
        ])
            ->whereHas('registrations', function ($query) use ($filters): void {
                $this->applyRegistrationFilters($query, $filters);
            })
            ->when($filters['search'] ?? null, function ($query, $searchValue) {
                $query->where(function ($studentQuery) use ($searchValue): void {
                    $studentQuery->where('nis', 'like', "%{$searchValue}%")
                        ->orWhere('class_name', 'like', "%{$searchValue}%")
                        ->orWhereHas('user', function ($userQuery) use ($searchValue): void {
                            $userQuery->where('name', 'like', "%{$searchValue}%")
                                ->orWhere('email', 'like', "%{$searchValue}%");
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
            ->when($filters['gender'] ?? null, fn ($query, $gender) => $query->where('gender', $gender))
            ->latest();
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

                if ($statusValue === 'scheduled_test') {
                    $query->where('status', Registration::STATUS_APPROVED)
                        ->where('willing_to_take_test', true)
                        ->whereHas('talentTestParticipants')
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

    private function statusLabels(): array
    {
        return [
            Registration::STATUS_PENDING => 'Menunggu',
            'waiting_test' => 'Menunggu Tes',
            'scheduled_test' => 'Tes Dijadwalkan',
            Registration::STATUS_APPROVED => 'Diterima',
            Registration::STATUS_REJECTED => 'Ditolak',
        ];
    }

    private function filterSummary(array $filters): array
    {
        $extracurricular = null;
        if (! empty($filters['extracurricular_id'])) {
            $extracurricular = Extracurricular::query()->find($filters['extracurricular_id']);
        }

        $categoryDefinition = ($filters['category'] ?? 'all') !== 'all'
            ? collect(Extracurricular::categoryDefinitions())->firstWhere('key', $filters['category'])
            : null;

        return [
            'search' => filled($filters['search'] ?? null) ? $filters['search'] : 'Semua siswa',
            'status' => $this->statusLabels()[$filters['status'] ?? ''] ?? 'Semua status',
            'extracurricular' => $extracurricular?->name ?? 'Semua kegiatan',
            'class_name' => $filters['class_name'] ?? 'Semua kelas',
            'gender' => match ($filters['gender'] ?? null) {
                'L' => 'Laki-laki',
                'P' => 'Perempuan',
                default => 'Semua jenis kelamin',
            },
            'category' => $categoryDefinition['label'] ?? 'Semua kategori',
        ];
    }

    private function exportFilenameBase(array $filters, array $summary): string
    {
        $segments = ['pendaftar'];

        if (($filters['category'] ?? 'all') !== 'all') {
            $segments[] = $summary['category'];
        }

        if (! empty($filters['extracurricular_id']) && $summary['extracurricular'] !== 'Semua kegiatan') {
            $segments[] = $summary['extracurricular'];
        }

        if (! empty($filters['class_name']) && $summary['class_name'] !== 'Semua kelas') {
            $segments[] = 'kelas-'.$summary['class_name'];
        }

        if (! empty($filters['status']) && $summary['status'] !== 'Semua status') {
            $segments[] = $summary['status'];
        }

        return Str::slug(implode('-', $segments));
    }
}
