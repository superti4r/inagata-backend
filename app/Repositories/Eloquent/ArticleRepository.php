<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Interfaces\ArticleRepositoryInterface;
use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ArticleRepository implements ArticleRepositoryInterface
{
    public function __construct(
        private readonly Article $model,
    ) {}

    public function create(array $attributes): Article
    {
        $article = $this->model->newQuery()->create($attributes);

        return $this->findById((int) $article->id) ?? $article;
    }

    public function updateById(int $id, array $attributes): ?Article
    {
        $article = $this->model->newQuery()->find($id);

        if (! $article instanceof Article) {
            return null;
        }

        $article->fill($attributes);
        $article->save();

        return $this->findById($id);
    }

    public function findById(int $id): ?Article
    {
        $article = $this->model
            ->newQuery()
            ->with(['category', 'user'])
            ->find($id);

        return $article instanceof Article ? $article : null;
    }

    public function deleteById(int $id): bool
    {
        return $this->model->newQuery()->whereKey($id)->delete() > 0;
    }

    public function paginate(int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        return $this->model
            ->newQuery()
            ->with(['category', 'user'])
            ->latest('id')
            ->paginate($limit, ['*'], 'page', $page);
    }

    public function search(?int $categoryId, ?string $keyword, int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        return $this->model
            ->newQuery()
            ->with(['category', 'user'])
            ->when($categoryId !== null, function ($query) use ($categoryId): void {
                $query->where('category_id', $categoryId);
            })
            ->when($keyword !== null, function ($query) use ($keyword): void {
                $query->where('title', 'like', '%'.$keyword.'%');
            })
            ->latest('id')
            ->paginate($limit, ['*'], 'page', $page);
    }
}
