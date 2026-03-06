<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminData = User::factory()->make([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ])->toArray();

        $defaultUserData = User::factory()->make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
        ])->toArray();

        User::query()->updateOrCreate(['email' => 'admin@example.com'], $adminData);
        User::query()->updateOrCreate(['email' => 'test@example.com'], $defaultUserData);

        $targetUserCount = 50;
        $currentUserCount = User::query()->where('role', 'user')->count();
        $usersToCreate = max(0, $targetUserCount - $currentUserCount);

        if ($usersToCreate > 0) {
            User::factory()->count($usersToCreate)->create();
        }
    }
}
