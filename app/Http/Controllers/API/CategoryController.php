<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\DTOs\CreateCategoryDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/categories",
     *     tags={"Categories"},
     *     summary="Get all categories",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Category list",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $categories = $this->categoryService->getAll();

        return $this->successResponse(
            'Categories retrieved successfully.',
            CategoryResource::collection($categories)->resolve(),
        );
    }

    /**
     * @OA\Post(
     *     path="/api/categories",
     *     tags={"Categories"},
     *     summary="Create a category (admin only)",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/StoreCategoryRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Category created",
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
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', Category::class);

        $category = $this->categoryService->create(CreateCategoryDTO::fromValidated($request->validated()));

        return $this->successResponse('Category created successfully.', (new CategoryResource($category))->resolve(), 201);
    }
}
