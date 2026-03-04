<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\CreateCategoryDTO;
use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function create(CreateCategoryDTO $dto): Category
    {
        return $this->categoryRepository->create($dto->toArray());
    }

    /**
     * @return Collection<int, Category>
     */
    public function getAll(): Collection
    {
        return $this->categoryRepository->all();
    }
}
