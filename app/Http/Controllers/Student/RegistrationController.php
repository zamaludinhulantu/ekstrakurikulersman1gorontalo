<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function index(): View
    {
        $student = auth()->user()->student;

        abort_unless($student, 404, 'Data siswa tidak ditemukan.');

        $registrations = Registration::with('extracurricular')
            ->where('student_id', $student->id)
            ->latest()
            ->paginate(10);

        return view('student.registrations.index', compact('registrations'));
    }

    public function store(Request $request, Extracurricular $extracurricular): RedirectResponse
    {
        $student = auth()->user()->student;

        abort_unless($student, 404, 'Data siswa tidak ditemukan.');
        abort_unless($extracurricular->is_active, 404);

        $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        $registration = Registration::where('student_id', $student->id)
            ->where('extracurricular_id', $extracurricular->id)
            ->first();

        if ($registration && $registration->status !== Registration::STATUS_REJECTED) {
            return back()->with('error', 'Anda sudah mendaftar di ekstrakurikuler ini.');
        }

        Registration::updateOrCreate(
            [
                'student_id' => $student->id,
                'extracurricular_id' => $extracurricular->id,
            ],
            [
                'registration_date' => now()->toDateString(),
                'status' => Registration::STATUS_PENDING,
                'notes' => $request->input('notes'),
                'verified_by' => null,
                'verified_at' => null,
            ]
        );

        return redirect()
            ->route('student.extracurriculars.show', $extracurricular)
            ->with('success', 'Pendaftaran berhasil dikirim dan menunggu verifikasi.');
    }
}
