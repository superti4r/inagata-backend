<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\DTOs\LoginUserDTO;
use App\DTOs\RegisterUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="Register a new user",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Register success",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register(RegisterUserDTO::fromValidated($request->validated()));

        return $this->successResponse('Register successful.', [
            'user' => (new UserResource($result['user']))->resolve(),
            'token' => $result['token'],
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Login and get Sanctum token",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login success",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(LoginUserDTO::fromValidated($request->validated()));

        if ($result === null) {
            return $this->errorResponse('Invalid credentials.', [
                'credentials' => ['Email or password is incorrect.'],
            ], 401);
        }

        return $this->successResponse('Login successful.', [
            'user' => (new UserResource($result['user']))->resolve(),
            'token' => $result['token'],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Logout and revoke current token",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Logout success",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $this->errorResponse('Unauthenticated.', status: 401);
        }

        $this->authService->logout($user);

        return $this->successResponse('Logout successful.', (object) []);
    }
}
