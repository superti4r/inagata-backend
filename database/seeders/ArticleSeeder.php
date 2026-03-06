<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->select(['id', 'name'])->get();
        $categoryIds = Category::query()->pluck('id');

        if ($users->isEmpty() || $categoryIds->isEmpty()) {
            return;
        }

        $targetArticleCount = 300;
        $currentArticleCount = Article::query()->count();
        $articlesToCreate = max(0, $targetArticleCount - $currentArticleCount);

        if ($articlesToCreate === 0) {
            return;
        }

        Article::factory()
            ->count($articlesToCreate)
            ->state(function () use ($users, $categoryIds): array {
                $selectedUser = $users->random();

                return [
                    'user_id' => $selectedUser->id,
                    'category_id' => $categoryIds->random(),
                    'author' => $selectedUser->name,
                ];
            })
            ->create();
    }
}
