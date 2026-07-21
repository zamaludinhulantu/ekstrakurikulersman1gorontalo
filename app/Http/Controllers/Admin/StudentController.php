<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SanitizesCsvExports;
use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\Student;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentController extends Controller
{
    use SanitizesCsvExports;

    public function index(Request $request): View
    {
        $filters = $this->validateFilters($request);

        $students = $this->filteredStudentsQuery($filters)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $classOptions = collect(array_keys(Student::registrationClassOptions()));

        $extracurricularOptions = Extracurricular::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.students.index', [
            'students' => $students,
            'search' => $filters['search'] ?? '',
            'className' => $filters['class_name'] ?? '',
            'gender' => $filters['gender'] ?? '',
            'status' => $filters['status'] ?? '',
            'extracurricularId' => $filters['extracurricular_id'] ?? null,
            'classOptions' => $classOptions,
            'extracurricularOptions' => $extracurricularOptions,
            'category' => $filters['category'] ?? 'all',
            'categories' => collect(Extracurricular::categoryDefinitions())
                ->map(fn (array $definition) => ['key' => $definition['key'], 'label' => $definition['label']])
                ->values(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validateFilters($request, true);
        $students = $this->filteredStudentsQuery($filters)->latest()->get();
        $timestamp = Carbon::now()->format('YmdHis');
        $filterSummary = $this->filterSummary($filters);
        $filenameBase = $this->exportFilenameBase($filters, $filterSummary);
        $format = $filters['format'] ?? 'xls';

        if ($format === 'pdf') {
            $html = view('admin.students.export-pdf', [
                'students' => $students,
                'filterSummary' => $filterSummary,
                'controller' => $this,
                'extracurricularId' => $filters['extracurricular_id'] ?? null,
            ])->render();

            $options = new Options();
            $options->set('isRemoteEnabled', false);
            $options->set('defaultFont', 'DejaVu Sans');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'landscape');
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
        $html = view('admin.students.export-xls', [
            'students' => $students,
            'filterSummary' => $filterSummary,
            'controller' => $this,
            'extracurricularId' => $filters['extracurricular_id'] ?? null,
        ])->render();

        return response()->streamDownload(function () use ($html): void {
            echo "\xEF\xBB\xBF";
            echo $html;
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function create(): View
    {
        return view('admin.students.create', [
            'classOptions' => Student::registrationClassOptions(),
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
                'role' => User::ROLE_STUDENT,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            Student::create([
                'user_id' => $user->id,
                'nis' => $validated['nis'],
                'class_name' => Student::normalizeClassName($validated['class_name']),
                'gender' => $validated['gender'],
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'address' => $validated['address'] ?? null,
                'parent_name' => $validated['parent_name'] ?? null,
                'parent_phone' => $validated['parent_phone'] ?? null,
            ]);
        });

        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function show(Student $student): View
    {
        $student->load('user', 'registrations.extracurricular');

        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        $student->load('user');

        return view('admin.students.edit', [
            'student' => $student,
            'classOptions' => Student::registrationClassOptions(),
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $student->load('user');
        $validated = $this->validatePayload($request, $student);

        DB::transaction(function () use ($validated, $request, $student): void {
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

            $student->user->update($userData);

            $student->update([
                'nis' => $validated['nis'],
                'class_name' => Student::normalizeClassName($validated['class_name']),
                'gender' => $validated['gender'],
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'address' => $validated['address'] ?? null,
                'parent_name' => $validated['parent_name'] ?? null,
                'parent_phone' => $validated['parent_phone'] ?? null,
            ]);
        });

        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->load('user');
        $student->user?->delete();

        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil dihapus.');
    }

    public function studentActivityNames(Student $student, ?int $extracurricularId = null): string
    {
        $names = $student->registrations
            ->when($extracurricularId, fn ($items) => $items->where('extracurricular_id', $extracurricularId))
            ->map(fn ($registration) => $registration->extracurricular?->name)
            ->filter()
            ->unique()
            ->values();

        return $names->isNotEmpty() ? $names->implode(', ') : 'Belum mengikuti kegiatan';
    }

    public function genderLabel(?string $gender): string
    {
        return match ($gender) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => '-',
        };
    }

    public function studentStatusLabel(Student $student): string
    {
        return $student->user?->is_active ? 'Aktif' : 'Tidak Aktif';
    }

    public function exportValue(mixed $value): string
    {
        return $this->sanitizeExportValue($value);
    }

    private function validatePayload(Request $request, ?Student $student = null): array
    {
        $userId = $student?->user_id;

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'password' => [$student ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
            'nis' => ['required', 'string', 'max:50', Rule::unique('students', 'nis')->ignore($student?->id)],
            'class_name' => ['required', Rule::in(array_keys(Student::registrationClassOptions()))],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'date_of_birth' => ['nullable', 'date'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
        ]);
    }

    private function validateFilters(Request $request, bool $includeFormat = false): array
    {
        $rules = [
            'search' => ['nullable', 'string', 'max:255'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
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

    private function filteredStudentsQuery(array $filters)
    {
        $classComparable = Student::normalizedClassComparable($filters['class_name'] ?? null);

        return Student::with(['user', 'registrations.extracurricular'])
            ->when($classComparable, function ($query, $value): void {
                $query->whereRaw(Student::normalizedClassExpression('class_name').' = ?', [$value]);
            })
            ->when($filters['gender'] ?? null, fn ($query, $value) => $query->where('gender', $value))
            ->when(($filters['status'] ?? '') !== '', function ($query) use ($filters) {
                $query->whereHas('user', fn ($userQuery) => $userQuery->where('is_active', ($filters['status'] ?? '') === 'active'));
            })
            ->when(($filters['extracurricular_id'] ?? null), function ($query, $extracurricularId): void {
                $query->whereHas('registrations', fn ($registrationQuery) => $registrationQuery->where('extracurricular_id', $extracurricularId));
            })
            ->when(($filters['category'] ?? 'all') !== 'all', function ($query) use ($filters): void {
                $ids = Extracurricular::idsForCategory($filters['category']);

                if ($ids === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->whereHas('registrations', fn ($registrationQuery) => $registrationQuery->whereIn('extracurricular_id', $ids));
            })
            ->when($filters['search'] ?? null, function ($query, $searchValue) {
                $query->where(function ($studentQuery) use ($searchValue): void {
                    $studentQuery->where('nis', 'like', "%{$searchValue}%")
                        ->orWhere('class_name', 'like', "%{$searchValue}%")
                        ->orWhereHas('user', function ($userQuery) use ($searchValue): void {
                            $userQuery->where('name', 'like', "%{$searchValue}%")
                                ->orWhere('email', 'like', "%{$searchValue}%");
                        });
                });
            });
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
            'class_name' => $filters['class_name'] ?? 'Semua kelas',
            'gender' => $this->genderLabel($filters['gender'] ?? null) !== '-'
                ? $this->genderLabel($filters['gender'] ?? null)
                : 'Semua jenis kelamin',
            'status' => match ($filters['status'] ?? null) {
                'active' => 'Aktif',
                'inactive' => 'Tidak Aktif',
                default => 'Semua status',
            },
            'extracurricular' => $extracurricular?->name ?? 'Semua kegiatan',
            'category' => $categoryDefinition['label'] ?? 'Semua kategori',
        ];
    }

    private function exportFilenameBase(array $filters, array $summary): string
    {
        $segments = ['data-siswa'];

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
