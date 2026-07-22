<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\User;

class AssessmentPolicy
{
    public function manageByCoach(User $user, Assessment $assessment): bool
    {
        if ($user->hasRole(User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN)) {
            return true;
        }

        return $user->hasRole(User::ROLE_COACH)
            && $user->coach
            && $assessment->extracurricular
            && $assessment->extracurricular->coaches()->whereKey($user->coach->id)->exists();
    }
}
