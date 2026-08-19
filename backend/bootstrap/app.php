<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $exception): bool => true
        );

        $exceptions->render(
            fn (AuthenticationException $exception): JsonResponse => response()->json([
                'message' => 'Não autenticado.',
            ], 401)
        );

        $exceptions->render(
            fn (AuthorizationException $exception): JsonResponse => response()->json([
                'message' => 'Acesso negado.',
            ], 403)
        );

        $exceptions->render(
            fn (ValidationException $exception): JsonResponse => response()->json([
                'message' => 'Os dados informados são inválidos.',
                'errors' => $exception->errors(),
            ], 422)
        );

        $exceptions->render(
            fn (ModelNotFoundException $exception): JsonResponse => response()->json([
                'message' => 'Recurso não encontrado.',
            ], 404)
        );

        $exceptions->render(
            fn (NotFoundHttpException $exception): JsonResponse => response()->json([
                'message' => 'Rota não encontrada.',
            ], 404)
        );
    })->create();
