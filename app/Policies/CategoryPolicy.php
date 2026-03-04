<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class CategoryPolicy
{
    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }
}
