<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_login_and_register_are_public(): void
    {
        $this->getJson('/api/health')->assertUnauthorized();
        $this->getJson('/api/projects')->assertUnauthorized();

        $user = User::factory()->create(['role' => RoleEnum::User]);
        $token = auth('api')->login($user);

        $this->withToken($token)
            ->getJson('/api/health')
            ->assertOk()
            ->assertExactJson(['status' => 'ok']);
    }
}
