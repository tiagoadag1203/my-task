<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\SprintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LogicException;
use Tymon\JWTAuth\JWTGuard;

class AuthController extends Controller
{
    public function __construct(private readonly SprintService $sprintService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::query()->create([
            ...$request->validated(),
            'role' => RoleEnum::User,
        ]);

        $token = $this->guard()->login($user);

        return $this->tokenResponse($token, $user, 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $guard = $this->guard();
        $token = $guard->attempt($request->validated());

        if ($token === false) {
            return response()->json(['message' => 'Credenciais inválidas.'], 401);
        }

        $user = $guard->user();

        if (! $user instanceof User) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        $this->sprintService->autoCloseExpired($user);

        return $this->tokenResponse($token, $user);
    }

    public function logout(): JsonResponse
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return (new UserResource($user))->response()->setStatusCode(200);
    }

    private function guard(): JWTGuard
    {
        $guard = Auth::guard('api');

        if (! $guard instanceof JWTGuard) {
            throw new LogicException('O guard api deve utilizar o driver JWT.');
        }

        return $guard;
    }

    private function tokenResponse(string $token, User $user, int $status = 200): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => (int) config('jwt.ttl', 60) * 60,
            'user' => new UserResource($user),
        ], $status);
    }
}
