<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\TalentTestResult;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class RegistrationProfilePreviewController extends Controller
{
    public function show(Registration $registration): JsonResponse
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $registration->loadMissing(['student.user', 'extracurricular.coaches.user']);

        if ($user->hasRole(User::ROLE_STUDENT)) {
            abort_unless($user->student && $user->student->id === $registration->student_id, 403);
        } elseif ($user->hasRole(User::ROLE_COACH)) {
            abort_unless(
                $user->coach && $registration->extracurricular->coaches()->whereKey($user->coach->id)->exists(),
                403
            );
        } elseif (! $user->hasRole(User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN, User::ROLE_PRINCIPAL)) {
            abort(403);
        }

        $latestPublishedResult = TalentTestResult::with('schedule')
            ->where('registration_id', $registration->id)
            ->where('status', 'published')
            ->latest('published_at')
            ->first();

        return response()->json([
            'name' => $registration->student->user->name ?? '-',
            'class_name' => $registration->student->class_name ?? '-',
            'extracurricular' => $registration->extracurricular->name ?? '-',
            'primary_talent' => $registration->primary_talent ?: ($registration->current_skills ?: '-'),
            'experience' => $registration->prior_experience ?: 'Belum ada pengalaman yang ditulis.',
            'achievements' => $registration->achievement_history ?: 'Belum ada prestasi yang ditulis.',
            'preferred_position' => $registration->preferred_position ?: '-',
            'training_group' => $latestPublishedResult?->training_group ?: '-',
            'recommended_role' => $latestPublishedResult?->recommended_role ?: '-',
            'recommendation' => $latestPublishedResult?->recommendation ?: 'Belum ada rekomendasi pembina yang dipublikasikan.',
            'initial' => strtoupper(substr($registration->student->user->name ?? 'S', 0, 1)),
        ]);
    }
}
