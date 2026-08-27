<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_login_read_profile_and_logout(): void
    {
        $register = $this->postJson('/api/auth/register', [
            'nickname' => 'Participante',
            'email' => 'PARTICIPANTE@example.com',
            'password' => 'password123',
        ]);

        $register
            ->assertCreated()
            ->assertJsonPath('user.role', RoleEnum::User->value)
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'user']);

        $token = (string) $register->json('access_token');

        $this->withToken($token)
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'participante@example.com');

        $this->withToken($token)
            ->postJson('/api/auth/logout')
            ->assertOk();

        $this->postJson('/api/auth/login', [
            'email' => 'participante@example.com',
            'password' => 'password123',
        ])->assertOk();
    }

    public function test_roles_are_strictly_separated(): void
    {
        $user = User::factory()->user()->create();
        $admin = User::factory()->admin()->create();

        $this->withToken(auth('api')->login($user))
            ->getJson('/api/admin/summary')
            ->assertForbidden();

        $this->withToken(auth('api')->login($admin))
            ->getJson('/api/projects')
            ->assertForbidden();

        $this->withToken(auth('api')->login($admin))
            ->getJson('/api/admin/summary')
            ->assertOk();
    }
}
