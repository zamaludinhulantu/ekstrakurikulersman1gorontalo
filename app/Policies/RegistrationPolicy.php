<?php

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;

class RegistrationPolicy
{
    public function manageByCoach(User $user, Registration $registration): bool
    {
        if ($user->hasRole(User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN)) {
            return true;
        }

        return $user->hasRole(User::ROLE_COACH)
            && $user->coach
            && $registration->extracurricular
            && $registration->extracurricular->coaches()->whereKey($user->coach->id)->exists();
    }
}
