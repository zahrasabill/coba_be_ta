<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        apiPrefix: 'v1',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'v1/*',
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'jwt.auth' => \Tymon\JWTAuth\Http\Middleware\Authenticate::class,
            'jwt.refresh' => \Tymon\JWTAuth\Http\Middleware\RefreshToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Unauthorized (JWT)
        $exceptions->renderable(function (UnauthorizedHttpException $e) {
            return response()->json([
                'responseMessage' => 'Unauthorized access, please authenticate.',
            ], 401);
        });

        // Bad Request
        $exceptions->renderable(function (ValidationException $e, $request) {
            return response()->json([
                'responseMessage' => 'Bad Request',
                'errors' => $e->errors(),
            ], 400);
        });

        // No Permission
        $exceptions->renderable(function (UnauthorizedException $e) {
            return response()->json([
                'responseMessage' => 'You do not have the required authorization.',
            ], 403);
        });

        // Not Found (404)
        $exceptions->renderable(function (NotFoundHttpException $e) {
            return response()->json([
                'responseMessage' => 'The requested resource was not found.',
            ], 404);
        });

        // General HTTP Error Handler
        $exceptions->renderable(function (HttpException $e) {
            return response()->json([
                'responseMessage' => $e->getMessage() ?: 'An unexpected error occurred.',
            ], $e->getStatusCode() ?: 500);
        });
    })->create();
