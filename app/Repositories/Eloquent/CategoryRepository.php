<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        private readonly Category $model,
    ) {}

    public function create(array $attributes): Category
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function all(): Collection
    {
        return $this->model
            ->newQuery()
            ->latest('id')
            ->get();
    }
}
