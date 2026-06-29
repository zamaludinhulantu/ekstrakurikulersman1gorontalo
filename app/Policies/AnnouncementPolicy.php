<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    public function manageByCoach(User $user, Announcement $announcement): bool
    {
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return true;
        }

        return $user->hasRole(User::ROLE_COACH)
            && $user->coach
            && $announcement->published_by === $user->id
            && (! $announcement->extracurricular || $announcement->extracurricular->coaches()->whereKey($user->coach->id)->exists());
    }
}
