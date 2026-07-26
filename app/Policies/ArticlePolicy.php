<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function manageByCoach(User $user, Article $article): bool
    {
        if (! $user->hasRole(User::ROLE_COACH) || ! $user->coach) {
            return false;
        }

        return $article->published_by === $user->id
            && (! $article->extracurricular || $article->extracurricular->coaches()->whereKey($user->coach->id)->exists());
    }
}
