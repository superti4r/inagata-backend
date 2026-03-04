<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly User $model,
    ) {}

    public function create(array $attributes): User
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function findByEmail(string $email): ?User
    {
        $user = $this->model->newQuery()->where('email', $email)->first();

        return $user instanceof User ? $user : null;
    }

    public function issueToken(User $user, string $tokenName): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }

    public function revokeCurrentToken(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token !== null) {
            $token->delete();
        }
    }
}
