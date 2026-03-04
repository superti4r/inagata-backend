<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class LoginUserDTO
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            email: (string) $validated['email'],
            password: (string) $validated['password'],
        );
    }
}
