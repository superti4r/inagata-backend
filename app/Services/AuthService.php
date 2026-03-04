<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\LoginUserDTO;
use App\DTOs\RegisterUserDTO;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Hashing\Hasher;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly Hasher $hasher,
    ) {}

    /**
     * @return array{user: User, token: string}
     */
    public function register(RegisterUserDTO $dto): array
    {
        $user = $this->userRepository->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $this->hasher->make($dto->password),
            'role' => 'user',
        ]);

        $token = $this->userRepository->issueToken($user, 'auth_token');

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * @return array{user: User, token: string}|null
     */
    public function login(LoginUserDTO $dto): ?array
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if (! $user instanceof User) {
            return null;
        }

        if (! $this->hasher->check($dto->password, $user->password)) {
            return null;
        }

        $token = $this->userRepository->issueToken($user, 'auth_token');

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        $this->userRepository->revokeCurrentToken($user);
    }
}
