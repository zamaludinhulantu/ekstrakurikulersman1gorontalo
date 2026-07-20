<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RegistrationAchievementProofController extends Controller
{
    public function show(Registration $registration): BinaryFileResponse|Response
    {
        $user = auth()->user();
        abort_unless($user, 403);
        abort_unless($registration->achievement_proof_path, 404);

        $registration->loadMissing('extracurricular.coaches');

        if ($user->hasRole(User::ROLE_STUDENT)) {
            abort_unless($user->student && $user->student->id === $registration->student_id, 403);
        } elseif ($user->hasRole(User::ROLE_COACH)) {
            abort_unless(
                $user->coach
                && $registration->extracurricular
                && $registration->extracurricular->coaches->contains('id', $user->coach->id),
                403
            );
        } elseif (! $user->hasRole(User::ROLE_ADMIN, User::ROLE_PRINCIPAL)) {
            abort(403);
        }

        $absolutePath = $this->resolveAbsolutePath($registration->achievement_proof_path);
        abort_unless($absolutePath !== null && File::exists($absolutePath), 404);

        return response()->file($absolutePath, [
            'Content-Type' => File::mimeType($absolutePath) ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function resolveAbsolutePath(string $path): ?string
    {
        $trimmedPath = ltrim($path, '/\\');

        return match (true) {
            str_starts_with($trimmedPath, 'private/achievement-proofs/')
                => storage_path('app/'.$trimmedPath),
            str_starts_with($trimmedPath, 'uploads/achievement-proofs/')
                => public_path($trimmedPath),
            default => null,
        };
    }
}
