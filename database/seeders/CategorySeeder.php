<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $defaultCategories = [
            'Technology',
            'Business',
            'Health',
            'Education',
            'Travel',
            'Lifestyle',
            'Food',
            'Sports',
            'Finance',
            'Entertainment',
        ];

        foreach ($defaultCategories as $categoryName) {
            Category::query()->firstOrCreate([
                'name' => $categoryName,
            ]);
        }

        $targetCategoryCount = 25;
        $currentCategoryCount = Category::query()->count();
        $categoriesToCreate = max(0, $targetCategoryCount - $currentCategoryCount);

        if ($categoriesToCreate > 0) {
            Category::factory()->count($categoriesToCreate)->create();
        }
    }
}
