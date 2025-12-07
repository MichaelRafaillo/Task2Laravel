<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Helper function to check if request is for API
        $isApiRequest = function (Request $request): bool {
            return $request->is('api/*') 
                || $request->expectsJson() 
                || str_starts_with($request->path(), 'api')
                || $request->header('Accept') === 'application/json';
        };

        // Ensure JSON responses for API routes and disable debug output
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) use ($isApiRequest) {
            return $isApiRequest($request);
        });

        // Handle AccessDeniedHttpException first (converted from AuthorizationException)
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'This action is unauthorized.',
                ], 403, [], JSON_UNESCAPED_SLASHES);
            }
        });

        // Handle authorization exceptions for API routes
        $exceptions->render(function (AuthorizationException $e, Request $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'This action is unauthorized.',
                ], 403, [], JSON_UNESCAPED_SLASHES);
            }
        });

        // Handle authentication exceptions for API routes
        $exceptions->render(function (AuthenticationException $e, Request $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }
        });

        // Handle validation exceptions for API routes
        $exceptions->render(function (ValidationException $e, Request $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Handle route not found exceptions for API routes (prevents redirect to login)
        $exceptions->render(function (RouteNotFoundException $e, Request $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                // If it's trying to redirect to login, return unauthenticated
                if (str_contains($e->getMessage(), 'login')) {
                    return response()->json([
                        'message' => 'Unauthenticated.',
                    ], 401);
                }
                return response()->json([
                    'message' => 'Route not found.',
                ], 404);
            }
        });

        // Handle RuntimeException from services (database errors, etc.)
        $exceptions->render(function (\RuntimeException $e, Request $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'An error occurred. Please try again.',
                ], 500, [], JSON_UNESCAPED_SLASHES);
            }
        });

        // Handle QueryException (database errors)
        $exceptions->render(function (\Illuminate\Database\QueryException $e, Request $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                return response()->json([
                    'message' => 'A database error occurred. Please try again.',
                ], 500, [], JSON_UNESCAPED_SLASHES);
            }
        });
    })->create();
