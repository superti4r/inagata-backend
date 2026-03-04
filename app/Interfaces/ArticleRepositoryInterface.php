<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ArticleRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Article;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateById(int $id, array $attributes): ?Article;

    public function findById(int $id): ?Article;

    public function deleteById(int $id): bool;

    public function paginate(int $page = 1, int $limit = 10): LengthAwarePaginator;

    public function search(?int $categoryId, ?string $keyword, int $page = 1, int $limit = 10): LengthAwarePaginator;
}
