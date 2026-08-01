<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRegistrationRequest;
use App\Models\Extracurricular;
use App\Models\NotificationPreference;
use App\Models\Registration;
use App\Models\User;
use App\Support\NotificationCenter;
use App\Support\RegistrationCancellationManager;
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

        $student->loadMissing('registrations');

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
            'activeRegistrationCount' => $student->activeRegistrationCount(),
            'hasReachedRegistrationLimit' => $student->hasReachedRegistrationLimit(),
            'hasLegacyRegistrationOverflow' => $student->hasLegacyRegistrationOverflow(),
        ]);
    }

    public function index(): View
    {
        $student = auth()->user()->student;

        abort_unless($student, 404, 'Data siswa tidak ditemukan.');

        $registrations = Registration::with(['extracurricular', 'talentTestResults.schedule', 'talentTestParticipants.schedule'])
            ->where('student_id', $student->id)
            ->where('status', '!=', Registration::STATUS_CANCELLED)
            ->latest()
            ->paginate(10);

        $student->loadMissing('registrations');

        return view('student.registrations.index', [
            'registrations' => $registrations,
            'student' => $student,
            'activeRegistrationCount' => $student->activeRegistrationCount(),
            'hasLegacyRegistrationOverflow' => $student->hasLegacyRegistrationOverflow(),
        ]);
    }

    public function store(StoreStudentRegistrationRequest $request, Extracurricular $extracurricular): RedirectResponse
    {
        $student = auth()->user()->student;

        abort_unless($student, 404, 'Data siswa tidak ditemukan.');
        abort_unless($extracurricular->is_active, 404);

        $registration = Registration::where('student_id', $student->id)
            ->where('extracurricular_id', $extracurricular->id)
            ->first();

        if ($registration && ! in_array($registration->status, [Registration::STATUS_REJECTED, Registration::STATUS_CANCELLED], true)) {
            return back()->with('error', 'Anda sudah mendaftar di ekstrakurikuler ini.');
        }

        $student->loadMissing('registrations');
        if ($student->hasReachedRegistrationLimit()) {
            return back()->with('error', $student->registrationLimitReachedMessage());
        }

        DB::transaction(function () use ($request, $student, $extracurricular): void {
            $registration = Registration::updateOrCreate(
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

            app(NotificationCenter::class)->notifyUser($student->user, [
                'title' => 'Pendaftaran berhasil dikirim',
                'message' => "Pendaftaran Anda untuk {$extracurricular->name} berhasil dikirim dan menunggu verifikasi.",
                'url' => route('student.registrations.index'),
                'category' => NotificationPreference::CATEGORY_REGISTRATION_STATUS,
                'icon' => 'bi-clipboard-check',
                'tag' => 'registration-submitted-'.$registration->id,
            ], false);

            $reviewers = User::query()
                ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])
                ->get()
                ->merge($extracurricular->coaches()->with('user')->get()->pluck('user'))
                ->filter();

            app(NotificationCenter::class)->notifyUsers($reviewers, [
                'title' => 'Pendaftaran baru memerlukan perhatian',
                'message' => "{$student->user->name} mengajukan pendaftaran baru ke {$extracurricular->name}.",
                'url' => route('admin.registrations.index'),
                'category' => NotificationPreference::CATEGORY_ADMIN_ALERT,
                'icon' => 'bi-person-plus',
                'tag' => 'registration-review-'.$registration->id,
            ]);
        });

        return redirect()
            ->route('student.extracurriculars.show', $extracurricular)
            ->with('success', 'Pendaftaran berhasil dikirim dan menunggu verifikasi.')
            ->with('success_modal', [
                'title' => 'Pendaftaran Berhasil',
                'message' => 'Pendaftaran berhasil dikirim dan menunggu verifikasi.',
            ]);
    }

    public function edit(Registration $registration): View|RedirectResponse
    {
        $student = auth()->user()->student;
        abort_unless($student && $student->id === $registration->student_id, 403);
        if (! $registration->canStudentEdit()) {
            return redirect()
                ->route('student.registrations.index')
                ->with('error', 'Pendaftaran ini sudah tidak dapat diubah dari akun siswa.');
        }
        $student->loadMissing('registrations');

        $registration->load('extracurricular');

        return view('student.registrations.edit', [
            'registration' => $registration,
            'student' => $student,
            'limitReachedForReactivation' => $registration->status === Registration::STATUS_REJECTED && $student->hasReachedRegistrationLimit(),
            'hasLegacyRegistrationOverflow' => $student->hasLegacyRegistrationOverflow(),
        ]);
    }

    public function update(StoreStudentRegistrationRequest $request, Registration $registration): RedirectResponse
    {
        $student = auth()->user()->student;
        abort_unless($student && $student->id === $registration->student_id, 403);
        if (! $registration->canStudentEdit()) {
            return redirect()
                ->route('student.registrations.index')
                ->with('error', 'Pendaftaran ini sudah tidak dapat diubah dari akun siswa.');
        }
        $student->loadMissing('registrations');

        if ($registration->status === Registration::STATUS_REJECTED && $student->hasReachedRegistrationLimit()) {
            return back()->with('error', $student->registrationLimitReachedMessage());
        }

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

            $registration->loadMissing(['student.user', 'extracurricular']);

            app(NotificationCenter::class)->notifyUser($registration->student->user, [
                'title' => 'Pendaftaran diperbarui',
                'message' => "Perubahan pendaftaran untuk {$registration->extracurricular->name} berhasil disimpan.",
                'url' => route('student.registrations.index'),
                'category' => NotificationPreference::CATEGORY_REGISTRATION_STATUS,
                'icon' => 'bi-pencil-square',
                'tag' => 'registration-updated-'.$registration->id,
            ], false);
        });

        return redirect()->route('student.registrations.index')->with('success', 'Data pendaftaran berhasil diperbarui.');
    }

    public function destroy(Registration $registration): RedirectResponse
    {
        $student = auth()->user()->student;
        abort_unless($student && $student->id === $registration->student_id, 403);

        if (! $registration->canStudentCancel()) {
            return redirect()
                ->route('student.registrations.index')
                ->with('error', 'Pendaftaran ini sudah tidak dapat dibatalkan.');
        }

        $registration->loadMissing(['student.user', 'extracurricular.coaches.user']);
        $adminReviewers = User::query()
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])
            ->get();
        $coachReviewers = $registration->extracurricular->coaches
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->values();

        if ($registration->requiresCancellationApproval()) {
            $registration = app(RegistrationCancellationManager::class)->request($registration);
            $message = "{$registration->student->user->name} meminta pembatalan keikutsertaan di {$registration->extracurricular->name}.";
            $payload = [
                'title' => 'Permintaan pembatalan keikutsertaan',
                'message' => $message,
                'category' => NotificationPreference::CATEGORY_ADMIN_ALERT,
                'icon' => 'bi-person-x',
                'tag' => 'registration-cancellation-request-'.$registration->id,
            ];

            app(NotificationCenter::class)->notifyUsers(
                $adminReviewers,
                [...$payload, 'url' => route('admin.registrations.index', ['status' => Registration::DISPLAY_STATUS_CANCELLATION_REQUESTED])],
                false
            );
            app(NotificationCenter::class)->notifyUsers(
                $coachReviewers,
                [...$payload, 'url' => route('coach.registrations.index', ['status' => Registration::DISPLAY_STATUS_CANCELLATION_REQUESTED])],
                false
            );

            return redirect()
                ->route('student.registrations.index')
                ->with('success', 'Permintaan pembatalan berhasil dikirim dan menunggu konfirmasi Admin atau Pembina.');
        }

        $registration = app(RegistrationCancellationManager::class)->approve(
            $registration,
            null,
            'Dibatalkan oleh siswa pada '.now()->format('d-m-Y H:i'),
            false
        );

        if ($adminReviewers->isNotEmpty()) {
            app(NotificationCenter::class)->notifyUsers($adminReviewers, [
                'title' => 'Siswa membatalkan keikutsertaan',
                'message' => "{$registration->student->user->name} membatalkan keikutsertaan di {$registration->extracurricular->name}.",
                'url' => route('admin.registrations.index'),
                'category' => NotificationPreference::CATEGORY_ADMIN_ALERT,
                'icon' => 'bi-person-dash',
                'tag' => 'registration-cancelled-review-'.$registration->id,
            ], false);
        }
        if ($coachReviewers->isNotEmpty()) {
            app(NotificationCenter::class)->notifyUsers($coachReviewers, [
                'title' => 'Siswa membatalkan keikutsertaan',
                'message' => "{$registration->student->user->name} membatalkan keikutsertaan di {$registration->extracurricular->name}.",
                'url' => route('coach.registrations.index'),
                'category' => NotificationPreference::CATEGORY_ADMIN_ALERT,
                'icon' => 'bi-person-dash',
                'tag' => 'registration-cancelled-review-'.$registration->id,
            ], false);
        }

        return redirect()
            ->route('student.registrations.index')
            ->with('success', 'Keikutsertaan ekstrakurikuler berhasil dibatalkan dan telah dihapus dari daftar pendaftaran Anda.');
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
