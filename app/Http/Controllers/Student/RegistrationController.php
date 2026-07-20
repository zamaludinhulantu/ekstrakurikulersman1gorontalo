<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRegistrationRequest;
use App\Models\Extracurricular;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(Extracurricular $extracurricular): View
    {
        $student = auth()->user()->student;

        abort_unless($student, 404, 'Data siswa tidak ditemukan.');

        $registration = Registration::where('student_id', $student->id)
            ->where('extracurricular_id', $extracurricular->id)
            ->first();

        $extracurricular->loadCount([
            'registrations as participants_count' => fn ($query) => $query->where('status', Registration::STATUS_APPROVED),
        ])->load([
            'coach.user',
            'coaches.user',
            'schedules' => fn ($query) => $query->orderByDesc('activity_date')->limit(5),
        ]);

        return view('student.registrations.create', [
            'extracurricular' => $extracurricular,
            'registration' => $registration,
            'student' => $student,
        ]);
    }

    public function index(): View
    {
        $student = auth()->user()->student;

        abort_unless($student, 404, 'Data siswa tidak ditemukan.');

        $registrations = Registration::with(['extracurricular', 'talentTestResults.schedule'])
            ->where('student_id', $student->id)
            ->latest()
            ->paginate(10);

        return view('student.registrations.index', compact('registrations'));
    }

    public function store(StoreStudentRegistrationRequest $request, Extracurricular $extracurricular): RedirectResponse
    {
        $student = auth()->user()->student;

        abort_unless($student, 404, 'Data siswa tidak ditemukan.');
        abort_unless($extracurricular->is_active, 404);

        $registration = Registration::where('student_id', $student->id)
            ->where('extracurricular_id', $extracurricular->id)
            ->first();

        if ($registration && $registration->status !== Registration::STATUS_REJECTED) {
            return back()->with('error', 'Anda sudah mendaftar di ekstrakurikuler ini.');
        }

        DB::transaction(function () use ($request, $student, $extracurricular): void {
            Registration::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'extracurricular_id' => $extracurricular->id,
                ],
                [
                    'selected_branch' => $request->input('selected_branch'),
                    'registration_date' => now()->toDateString(),
                    'status' => Registration::STATUS_PENDING,
                    'notes' => null,
                    'motivation_reason' => $request->input('motivation_reason'),
                    'goal_statement' => $request->input('goal_statement'),
                    'prior_experience' => $request->input('prior_experience'),
                    'current_skills' => $request->input('current_skills'),
                    'primary_talent' => $request->input('primary_talent'),
                    'preferred_position' => $request->input('preferred_position'),
                    'achievement_history' => $request->input('achievement_history'),
                    'achievement_proof_path' => $this->storeAchievementProof($request),
                    'willing_to_take_test' => $request->boolean('willing_to_take_test'),
                    'student_notes' => $request->input('student_notes'),
                    'allow_public_profile' => $request->boolean('allow_public_profile'),
                    'verified_by' => null,
                    'verified_at' => null,
                ]
            );
        });

        return redirect()
            ->route('student.extracurriculars.show', $extracurricular)
            ->with('success', 'Pendaftaran berhasil dikirim dan menunggu verifikasi.')
            ->with('success_modal', [
                'title' => 'Pendaftaran Berhasil',
                'message' => 'Pendaftaran berhasil dikirim dan menunggu verifikasi.',
            ]);
    }

    public function edit(Registration $registration): View
    {
        $student = auth()->user()->student;
        abort_unless($student && $student->id === $registration->student_id, 403);
        abort_unless(in_array($registration->status, [Registration::STATUS_PENDING, Registration::STATUS_REJECTED], true), 403);

        $registration->load('extracurricular');

        return view('student.registrations.edit', compact('registration'));
    }

    public function update(StoreStudentRegistrationRequest $request, Registration $registration): RedirectResponse
    {
        $student = auth()->user()->student;
        abort_unless($student && $student->id === $registration->student_id, 403);
        abort_unless(in_array($registration->status, [Registration::STATUS_PENDING, Registration::STATUS_REJECTED], true), 403);

        DB::transaction(function () use ($request, $registration): void {
            $proofPath = $registration->achievement_proof_path;
            if ($request->hasFile('achievement_proof')) {
                $this->deleteProof($proofPath);
                $proofPath = $this->storeAchievementProof($request);
            }

            $registration->update([
                'selected_branch' => $request->input('selected_branch'),
                'status' => Registration::STATUS_PENDING,
                'notes' => null,
                'motivation_reason' => $request->input('motivation_reason'),
                'goal_statement' => $request->input('goal_statement'),
                'prior_experience' => $request->input('prior_experience'),
                'current_skills' => $request->input('current_skills'),
                'primary_talent' => $request->input('primary_talent'),
                'preferred_position' => $request->input('preferred_position'),
                'achievement_history' => $request->input('achievement_history'),
                'achievement_proof_path' => $proofPath,
                'willing_to_take_test' => $request->boolean('willing_to_take_test'),
                'student_notes' => $request->input('student_notes'),
                'allow_public_profile' => $request->boolean('allow_public_profile'),
                'verified_by' => null,
                'verified_at' => null,
            ]);
        });

        return redirect()->route('student.registrations.index')->with('success', 'Data pendaftaran berhasil diperbarui.');
    }

    private function storeAchievementProof(StoreStudentRegistrationRequest $request): ?string
    {
        if (! $request->hasFile('achievement_proof')) {
            return null;
        }

        $directory = storage_path('app/private/achievement-proofs');
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $file = $request->file('achievement_proof');
        $filename = Str::uuid()->toString().'.'.$this->resolveAchievementProofExtension($file);
        $file->move($directory, $filename);

        return 'private/achievement-proofs/'.$filename;
    }

    private function deleteProof(?string $path): void
    {
        if (! $path) {
            return;
        }

        $trimmedPath = ltrim($path, '/\\');
        $absolutePath = match (true) {
            str_starts_with($trimmedPath, 'private/achievement-proofs/') => storage_path('app/'.$trimmedPath),
            str_starts_with($trimmedPath, 'uploads/achievement-proofs/') => public_path($trimmedPath),
            default => null,
        };

        if ($absolutePath && File::exists($absolutePath)) {
            File::delete($absolutePath);
        }
    }

    private function resolveAchievementProofExtension(UploadedFile $file): string
    {
        return match ($file->getMimeType()) {
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => $file->extension() ?: 'bin',
        };
    }
}
