<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class ArticlePolicy
{
    public function create(User $user): bool
    {
        return $this->canCreate($user);
    }

    public function update(User $user, mixed $article = null): bool
    {
        unset($article);

        return $this->canUpdate($user);
    }

    public function delete(User $user, mixed $article = null): bool
    {
        unset($article);

        return $this->canDelete($user);
    }

    private function canCreate(User $user): bool
    {
        return $this->isAdmin($user);
    }

    private function canUpdate(User $user): bool
    {
        return $this->isAdmin($user);
    }

    private function canDelete(User $user): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }
}
