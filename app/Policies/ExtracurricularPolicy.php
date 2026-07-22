<?php

namespace App\Policies;

use App\Models\Extracurricular;
use App\Models\User;

class ExtracurricularPolicy
{
    public function viewByCoach(User $user, Extracurricular $extracurricular): bool
    {
        if ($user->hasRole(User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN)) {
            return true;
        }

        return $user->hasRole(User::ROLE_COACH)
            && $user->coach
            && $extracurricular->coaches()->whereKey($user->coach->id)->exists();
    }
}
