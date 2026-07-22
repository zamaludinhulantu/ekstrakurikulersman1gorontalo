<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{
    public function manageByCoach(User $user, Schedule $schedule): bool
    {
        if ($user->hasRole(User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN)) {
            return true;
        }

        return $user->hasRole(User::ROLE_COACH)
            && $user->coach
            && $schedule->extracurricular
            && $schedule->extracurricular->coaches()->whereKey($user->coach->id)->exists();
    }
}
