<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\DTOs\CreateArticleDTO;
use App\DTOs\UpdateArticleDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\User;
use App\Services\ArticleService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    private const ARTICLE_NOT_FOUND_MESSAGE = 'Article not found.';

    public function __construct(
        private readonly ArticleService $articleService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/articles",
     *     tags={"Articles"},
     *     summary="Get paginated articles",
     *
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", default=10)),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Article list",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $articles = $this->articleService->getPaginated(
            page: $this->resolvePage($request),
            limit: $this->resolveLimit($request),
        );

        return $this->successResponse('Articles retrieved successfully.', $this->toPaginatedPayload($articles));
    }

    /**
     * @OA\Get(
     *     path="/api/articles/{id}",
     *     tags={"Articles"},
     *     summary="Get article detail",
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Article detail",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $article = $this->articleService->getById($id);

        if (! $article instanceof Article) {
            return $this->errorResponse(self::ARTICLE_NOT_FOUND_MESSAGE, status: 404);
        }

        return $this->successResponse('Article retrieved successfully.', (new ArticleResource($article))->resolve());
    }

    /**
     * @OA\Post(
     *     path="/api/articles",
     *     tags={"Articles"},
     *     summary="Create article (admin only)",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/StoreArticleRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Article created",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function store(StoreArticleRequest $request): JsonResponse
    {
        $this->authorize('create', Article::class);

        $user = $request->user();

        if (! $user instanceof User) {
            return $this->errorResponse('Unauthenticated.', status: 401);
        }

        $article = $this->articleService->create(
            dto: CreateArticleDTO::fromValidated($request->validated()),
            user: $user,
        );

        return $this->successResponse('Article created successfully.', (new ArticleResource($article))->resolve(), 201);
    }

    /**
     * @OA\Put(
     *     path="/api/articles/{id}",
     *     tags={"Articles"},
     *     summary="Update article (admin only)",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/UpdateArticleRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Article updated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function update(UpdateArticleRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', Article::class);

        $article = $this->articleService->update($id, UpdateArticleDTO::fromValidated($request->validated()));

        if (! $article instanceof Article) {
            return $this->errorResponse(self::ARTICLE_NOT_FOUND_MESSAGE, status: 404);
        }

        return $this->successResponse('Article updated successfully.', (new ArticleResource($article))->resolve());
    }

    /**
     * @OA\Delete(
     *     path="/api/articles/{id}",
     *     tags={"Articles"},
     *     summary="Delete article (admin only)",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Article deleted",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', Article::class);

        $isDeleted = $this->articleService->delete($id);

        if (! $isDeleted) {
            return $this->errorResponse(self::ARTICLE_NOT_FOUND_MESSAGE, status: 404);
        }

        return $this->successResponse('Article deleted successfully.', (object) []);
    }

    /**
     * @OA\Get(
     *     path="/api/articles/search",
     *     tags={"Articles"},
     *     summary="Search articles by category and keyword",
     *
     *     @OA\Parameter(name="category_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="keyword", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", default=10)),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Search result",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        $articles = $this->articleService->search(
            categoryId: $this->resolveCategoryId($request),
            keyword: $this->resolveKeyword($request),
            page: $this->resolvePage($request),
            limit: $this->resolveLimit($request),
        );

        return $this->successResponse('Articles retrieved successfully.', $this->toPaginatedPayload($articles));
    }

    private function resolvePage(Request $request): int
    {
        return max(1, (int) $request->query('page', '1'));
    }

    private function resolveLimit(Request $request): int
    {
        return max(1, (int) $request->query('limit', '10'));
    }

    private function resolveCategoryId(Request $request): ?int
    {
        $categoryId = $request->query('category_id');

        if ($categoryId === null || $categoryId === '') {
            return null;
        }

        $value = (int) $categoryId;

        return $value > 0 ? $value : null;
    }

    private function resolveKeyword(Request $request): ?string
    {
        $keyword = trim((string) $request->query('keyword', ''));

        return $keyword !== '' ? $keyword : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function toPaginatedPayload(LengthAwarePaginator $paginator): array
    {
        $items = collect($paginator->items());

        return [
            'items' => ArticleResource::collection($items)->resolve(),
            'pagination' => [
                'page' => $paginator->currentPage(),
                'limit' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }
}
