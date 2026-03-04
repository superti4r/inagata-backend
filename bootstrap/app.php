<?php

declare(strict_types=1);

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        unset($middleware);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $apiPattern = 'api/*';

        $exceptions->render(function (AuthenticationException $exception, Request $request) use ($apiPattern) {
            report($exception);

            if (! $request->is($apiPattern)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => (object) [],
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) use ($apiPattern) {
            report($exception);

            if (! $request->is($apiPattern)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
                'errors' => (object) [],
            ], 403);
        });

        $exceptions->render(function (AccessDeniedHttpException $exception, Request $request) use ($apiPattern) {
            report($exception);

            if (! $request->is($apiPattern)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
                'errors' => (object) [],
            ], 403);
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) use ($apiPattern) {
            report($exception);

            if (! $request->is($apiPattern)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Resource not found.',
                'errors' => (object) [],
            ], 404);
        });

        $exceptions->render(function (MethodNotAllowedHttpException $exception, Request $request) use ($apiPattern) {
            report($exception);

            if (! $request->is($apiPattern)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Method not allowed.',
                'errors' => (object) [],
            ], 405);
        });

        $exceptions->render(function (TokenMismatchException $exception, Request $request) use ($apiPattern) {
            report($exception);

            if (! $request->is($apiPattern)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'CSRF token mismatch.',
                'errors' => (object) [],
            ], 419);
        });
    })->create();
