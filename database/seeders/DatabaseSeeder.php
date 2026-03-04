<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->make([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ])->toArray();

        $user = User::factory()->make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
        ])->toArray();

        User::query()->updateOrCreate(['email' => 'admin@example.com'], $admin);
        User::query()->updateOrCreate(['email' => 'test@example.com'], $user);
    }
}
