<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Category;

    /**
     * @return Collection<int, Category>
     */
    public function all(): Collection;
}
