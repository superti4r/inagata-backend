<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\CreateArticleDTO;
use App\DTOs\UpdateArticleDTO;
use App\Interfaces\ArticleRepositoryInterface;
use App\Models\Article;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\DatabaseManager;

class ArticleService
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository,
        private readonly DatabaseManager $database,
    ) {}

    public function create(CreateArticleDTO $dto, User $user): Article
    {
        return $this->database->transaction(function () use ($dto, $user): Article {
            $attributes = $dto->toArray();
            $attributes['user_id'] = $user->id;

            return $this->articleRepository->create($attributes);
        });
    }

    public function update(int $id, UpdateArticleDTO $dto): ?Article
    {
        return $this->database->transaction(
            fn (): ?Article => $this->articleRepository->updateById($id, $dto->toArray())
        );
    }

    public function delete(int $id): bool
    {
        return $this->database->transaction(
            fn (): bool => $this->articleRepository->deleteById($id)
        );
    }

    public function getById(int $id): ?Article
    {
        return $this->articleRepository->findById($id);
    }

    public function getPaginated(int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        return $this->articleRepository->paginate(
            page: $this->normalizePage($page),
            limit: $this->normalizeLimit($limit),
        );
    }

    public function search(?int $categoryId, ?string $keyword, int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        return $this->articleRepository->search(
            categoryId: $categoryId,
            keyword: $keyword,
            page: $this->normalizePage($page),
            limit: $this->normalizeLimit($limit),
        );
    }

    private function normalizePage(int $page): int
    {
        return max(1, $page);
    }

    private function normalizeLimit(int $limit): int
    {
        return max(1, min($limit, 100));
    }
}
