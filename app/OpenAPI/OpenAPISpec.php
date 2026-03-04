<?php

declare(strict_types=1);

namespace App\OpenAPI;

/**
 * @OA\Info(
 *     title="RESTful Blog API",
 *     version="1.0.0",
 *     description="Production-ready RESTful Blog API with Laravel Sanctum and Clean Architecture"
 * )
 *
 * @OA\Server(
 *     url="/",
 *     description="Application server"
 * )
 *
 * @OA\Tag(
 *     name="Auth",
 *     description="Authentication endpoints"
 * )
 * @OA\Tag(
 *     name="Categories",
 *     description="Category management endpoints"
 * )
 * @OA\Tag(
 *     name="Articles",
 *     description="Article management endpoints"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token",
 *     description="Use: Bearer {sanctum_token}"
 * )
 *
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     type="object",
 *     required={"success", "message", "data"},
 *
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Register successful."),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         example={
 *             "user": {
 *                 "id": 1,
 *                 "name": "John Doe",
 *                 "email": "john@example.com",
 *                 "role": "user"
 *             },
 *             "token": "1|xxxxxxxxxxxxxxxx"
 *         }
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     required={"success", "message", "errors"},
 *
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Validation failed."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         example={
 *             "email": {
 *                 "The email has already been taken."
 *             }
 *         }
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     type="object",
 *     required={"name", "email", "password"},
 *
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="secret123")
 * )
 *
 * @OA\Schema(
 *     schema="LoginRequest",
 *     type="object",
 *     required={"email", "password"},
 *
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="secret123")
 * )
 *
 * @OA\Schema(
 *     schema="StoreCategoryRequest",
 *     type="object",
 *     required={"name"},
 *
 *     @OA\Property(property="name", type="string", example="Technology")
 * )
 *
 * @OA\Schema(
 *     schema="StoreArticleRequest",
 *     type="object",
 *     required={"title", "content", "author", "category_id"},
 *
 *     @OA\Property(property="title", type="string", example="Laravel API Best Practice"),
 *     @OA\Property(property="content", type="string", example="Long content..."),
 *     @OA\Property(property="author", type="string", example="Jane Doe"),
 *     @OA\Property(property="category_id", type="integer", example=1)
 * )
 *
 * @OA\Schema(
 *     schema="UpdateArticleRequest",
 *     type="object",
 *     required={"title", "content", "author", "category_id"},
 *
 *     @OA\Property(property="title", type="string", example="Updated article title"),
 *     @OA\Property(property="content", type="string", example="Updated content..."),
 *     @OA\Property(property="author", type="string", example="Jane Doe"),
 *     @OA\Property(property="category_id", type="integer", example=1)
 * )
 */
final class OpenAPISpec {}
