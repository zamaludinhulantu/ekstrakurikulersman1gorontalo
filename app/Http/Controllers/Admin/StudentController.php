<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SanitizesCsvExports;
use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\Registration;
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

        $students = $this->applySorting($this->filteredStudentsQuery($filters), $filters)
            ->paginate($filters['per_page'] ?? 20)
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
            'profileStatus' => $filters['profile_status'] ?? '',
            'sort' => $filters['sort'] ?? 'created_at',
            'direction' => $filters['direction'] ?? 'desc',
            'perPage' => $filters['per_page'] ?? 20,
            'studentSummary' => $this->studentSummary(),
            'categories' => collect(Extracurricular::categoryDefinitions())
                ->map(fn (array $definition) => ['key' => $definition['key'], 'label' => $definition['label']])
                ->values(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validateFilters($request, true);
        $students = $this->applySorting($this->filteredStudentsQuery($filters), $filters)->get();
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
            'canManageClassOptions' => $this->canManageClassOptions(),
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
            'canManageClassOptions' => $this->canManageClassOptions(),
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
        $hasHistory = $student->registrations()->exists()
            || $student->attendances()->exists()
            || $student->assessments()->exists()
            || $student->talentTestParticipants()->exists()
            || $student->talentTestResults()->exists();

        if ($hasHistory) {
            return redirect()
                ->route('admin.students.index')
                ->with(
                    'error',
                    'Siswa tidak dapat dihapus karena masih memiliki riwayat kegiatan. Nonaktifkan akun melalui menu Edit agar data laporan tetap utuh.'
                );
        }

        DB::transaction(function () use ($student): void {
            $student->load('user');
            $student->user?->delete();
        });

        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil dihapus.');
    }

    public function studentActivityNames(Student $student, ?int $extracurricularId = null): string
    {
        $names = $student->registrations
            ->where('status', Registration::STATUS_APPROVED)
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
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'password' => [$student ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
            'nis' => ['required', 'string', 'max:50', Rule::unique('students', 'nis')->ignore($student?->id)],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'date_of_birth' => ['nullable', 'date'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
        ];

        if ($this->canManageClassOptions()) {
            $rules['class_name'] = ['nullable', Rule::in(array_keys(Student::registrationClassOptions()))];
            $rules['custom_class_name'] = ['nullable', 'string', 'max:100'];
        } else {
            $rules['class_name'] = ['required', Rule::in(array_keys(Student::registrationClassOptions()))];
        }

        $validated = $request->validate($rules);

        $resolvedClassName = null;
        if ($this->canManageClassOptions() && filled($validated['custom_class_name'] ?? null)) {
            $resolvedClassName = Student::addCustomRegistrationClassOption($validated['custom_class_name']);
        }

        if (! $resolvedClassName) {
            $resolvedClassName = Student::normalizeClassName($validated['class_name'] ?? null);
        }

        if (! $resolvedClassName) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'class_name' => 'Kelas wajib dipilih atau ditambahkan.',
            ]);
        }

        $validated['class_name'] = $resolvedClassName;

        return $validated;
    }

    private function canManageClassOptions(): bool
    {
        return auth()->user()?->hasRole(User::ROLE_ADMIN)
            || auth()->user()?->hasRole(User::ROLE_SUPER_ADMIN);
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
            'profile_status' => ['nullable', Rule::in(['complete', 'incomplete'])],
            'sort' => ['nullable', Rule::in(['name', 'nis', 'class_name', 'status', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 50, 100])],
        ];

        if ($includeFormat) {
            $rules['format'] = ['nullable', Rule::in(['pdf', 'xls'])];
        }

        $validated = $request->validate($rules);
        $validated['class_name'] = Student::normalizeClassName($validated['class_name'] ?? null);
        $validated['category'] = $validated['category'] ?? 'all';
        $validated['sort'] = $validated['sort'] ?? 'created_at';
        $validated['direction'] = $validated['direction'] ?? 'desc';
        $validated['per_page'] = (int) ($validated['per_page'] ?? 20);

        return $validated;
    }

    private function filteredStudentsQuery(array $filters)
    {
        $classComparable = Student::normalizedClassComparable($filters['class_name'] ?? null);

        return Student::query()
            ->with([
            'user:id,name,email,phone,is_active',
            'registrations' => function ($query): void {
                $query->where('status', Registration::STATUS_APPROVED)
                    ->with('extracurricular:id,name');
            },
        ])
            ->withCount([
                'registrations',
                'attendances',
                'assessments',
                'talentTestParticipants',
                'talentTestResults',
            ])
            ->when($classComparable, function ($query, $value): void {
                $query->whereRaw(Student::normalizedClassExpression('class_name').' = ?', [$value]);
            })
            ->when($filters['gender'] ?? null, fn ($query, $value) => $query->where('gender', $value))
            ->when(($filters['status'] ?? '') !== '', function ($query) use ($filters) {
                $query->whereHas('user', fn ($userQuery) => $userQuery->where('is_active', ($filters['status'] ?? '') === 'active'));
            })
            ->when(($filters['extracurricular_id'] ?? null), function ($query, $extracurricularId): void {
                $query->whereHas('registrations', function ($registrationQuery) use ($extracurricularId): void {
                    $registrationQuery
                        ->where('status', Registration::STATUS_APPROVED)
                        ->where('extracurricular_id', $extracurricularId);
                });
            })
            ->when(($filters['category'] ?? 'all') !== 'all', function ($query) use ($filters): void {
                $ids = Extracurricular::idsForCategory($filters['category']);

                if ($ids === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->whereHas('registrations', function ($registrationQuery) use ($ids): void {
                    $registrationQuery
                        ->where('status', Registration::STATUS_APPROVED)
                        ->whereIn('extracurricular_id', $ids);
                });
            })
            ->when(($filters['profile_status'] ?? '') !== '', function ($query) use ($filters): void {
                $method = ($filters['profile_status'] ?? '') === 'incomplete'
                    ? 'where'
                    : 'whereNot';

                $query->{$method}(function ($profileQuery): void {
                    $profileQuery->whereNull('nis')
                        ->orWhere('nis', '')
                        ->orWhereNull('class_name')
                        ->orWhere('class_name', '')
                        ->orWhereNull('gender')
                        ->orWhereDoesntHave('user', function ($userQuery): void {
                            $userQuery->whereNotNull('name')
                                ->where('name', '!=', '')
                                ->whereNotNull('email')
                                ->where('email', '!=', '');
                        });
                });
            })
            ->when($filters['search'] ?? null, function ($query, $searchValue) {
                $query->where(function ($studentQuery) use ($searchValue): void {
                    $studentQuery->where('nis', 'like', "%{$searchValue}%")
                        ->orWhere('class_name', 'like', "%{$searchValue}%")
                        ->orWhereHas('user', function ($userQuery) use ($searchValue): void {
                            $userQuery->where('name', 'like', "%{$searchValue}%")
                                ->orWhere('email', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('registrations', function ($registrationQuery) use ($searchValue): void {
                            $registrationQuery->where('status', Registration::STATUS_APPROVED)
                                ->whereHas('extracurricular', fn ($activityQuery) => $activityQuery->where('name', 'like', "%{$searchValue}%"));
                        });
                });
            });
    }

    private function applySorting($query, array $filters)
    {
        $direction = $filters['direction'] ?? 'desc';

        return match ($filters['sort'] ?? 'created_at') {
            'name' => $query->orderBy(
                User::select('name')->whereColumn('users.id', 'students.user_id')->limit(1),
                $direction
            ),
            'nis' => $query->orderByRaw('CASE WHEN nis IS NULL OR nis = ? THEN 1 ELSE 0 END', [''])
                ->orderBy('nis', $direction),
            'class_name' => $query->orderBy('class_name', $direction),
            'status' => $query->orderBy(
                User::select('is_active')->whereColumn('users.id', 'students.user_id')->limit(1),
                $direction
            ),
            default => $query->orderBy('created_at', $direction),
        };
    }

    private function studentSummary(): array
    {
        $incompleteProfile = fn ($query) => $query
            ->whereNull('nis')
            ->orWhere('nis', '')
            ->orWhereNull('class_name')
            ->orWhere('class_name', '')
            ->orWhereNull('gender')
            ->orWhereDoesntHave('user', function ($userQuery): void {
                $userQuery->whereNotNull('name')
                    ->where('name', '!=', '')
                    ->whereNotNull('email')
                    ->where('email', '!=', '');
            });

        return [
            'total' => Student::query()->count(),
            'active' => Student::query()
                ->whereHas('user', fn ($query) => $query->where('is_active', true))
                ->count(),
            'inactive' => Student::query()
                ->whereHas('user', fn ($query) => $query->where('is_active', false))
                ->count(),
            'incomplete' => Student::query()
                ->where($incompleteProfile)
                ->count(),
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
            'class_name' => $filters['class_name'] ?? 'Semua kelas',
            'gender' => $this->genderLabel($filters['gender'] ?? null) !== '-'
                ? $this->genderLabel($filters['gender'] ?? null)
                : 'Semua jenis kelamin',
            'status' => match ($filters['status'] ?? null) {
                'active' => 'Aktif',
                'inactive' => 'Tidak Aktif',
                default => 'Semua status',
            },
            'profile_status' => match ($filters['profile_status'] ?? null) {
                'complete' => 'Lengkap',
                'incomplete' => 'Belum lengkap',
                default => 'Semua profil',
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

        if (! empty($filters['profile_status']) && $summary['profile_status'] !== 'Semua profil') {
            $segments[] = 'profil-'.$summary['profile_status'];
        }

        return Str::slug(implode('-', $segments));
    }
}
