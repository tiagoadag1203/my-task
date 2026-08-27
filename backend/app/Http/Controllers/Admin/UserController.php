<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListUsersRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function index(ListUsersRequest $request): AnonymousResourceCollection
    {
        $search = $request->validated('search');

        $users = User::query()
            ->where('role', RoleEnum::User->value)
            ->when($search, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('nickname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return UserResource::collection($users);
    }

    public function resetPassword(int $user): JsonResponse
    {
        $participant = User::query()
            ->where('role', RoleEnum::User->value)
            ->findOrFail($user);

        $participant->password = '123456';
        $participant->save();

        return response()->json([
            'message' => 'Senha do usuário redefinida com sucesso para o padrão: 123456',
        ]);
    }
}
