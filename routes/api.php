<?php

declare(strict_types=1);

use App\Http\Controllers\API\ArticleController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/categories', [CategoryController::class, 'index']);

Route::get('/articles/search', [ArticleController::class, 'search']);
Route::apiResource('articles', ArticleController::class)
    ->only(['index', 'show'])
    ->parameters(['articles' => 'id'])
    ->whereNumber('id');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/categories', [CategoryController::class, 'store']);

    Route::apiResource('articles', ArticleController::class)
        ->only(['store', 'update', 'destroy'])
        ->parameters(['articles' => 'id'])
        ->whereNumber('id');
});
