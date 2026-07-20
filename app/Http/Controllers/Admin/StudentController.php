<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $className = $request->string('class_name')->toString();
        $gender = $request->string('gender')->toString();
        $status = $request->string('status')->toString();
        $extracurricularId = $request->integer('extracurricular_id');

        $students = Student::with(['user', 'registrations.extracurricular'])
            ->when($className, fn ($query, $value) => $query->where('class_name', $value))
            ->when($gender, fn ($query, $value) => $query->where('gender', $value))
            ->when($status !== '', function ($query) use ($status) {
                $query->whereHas('user', fn ($userQuery) => $userQuery->where('is_active', $status === 'active'));
            })
            ->when($extracurricularId > 0, function ($query) use ($extracurricularId): void {
                $query->whereHas('registrations', fn ($registrationQuery) => $registrationQuery->where('extracurricular_id', $extracurricularId));
            })
            ->when($search, function ($query, $searchValue) {
                $query->where(function ($studentQuery) use ($searchValue): void {
                    $studentQuery->where('nis', 'like', "%{$searchValue}%")
                        ->orWhere('class_name', 'like', "%{$searchValue}%")
                        ->orWhereHas('user', function ($userQuery) use ($searchValue): void {
                            $userQuery->where('name', 'like', "%{$searchValue}%")
                                ->orWhere('email', 'like', "%{$searchValue}%");
                        });
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $classOptions = Student::query()
            ->select('class_name')
            ->distinct()
            ->orderBy('class_name')
            ->pluck('class_name');

        $extracurricularOptions = Extracurricular::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.students.index', [
            'students' => $students,
            'search' => $search,
            'className' => $className,
            'gender' => $gender,
            'status' => $status,
            'extracurricularId' => $extracurricularId > 0 ? $extracurricularId : null,
            'classOptions' => $classOptions,
            'extracurricularOptions' => $extracurricularOptions,
        ]);
    }

    public function create(): View
    {
        return view('admin.students.create');
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
                'class_name' => $validated['class_name'],
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

        return view('admin.students.edit', compact('student'));
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
                'class_name' => $validated['class_name'],
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
            'class_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'date_of_birth' => ['nullable', 'date'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
        ]);
    }
}
