<?php

namespace Tests\Feature;

use App\Enums\SprintStatus;
use App\Enums\TaskStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAndAutoCloseTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_automatically_closes_expired_active_sprints(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);
        $project = $user->projects()->create(['name' => 'Projeto']);
        $sprint = $project->sprints()->create([
            'name' => 'Expirada',
            'started_at' => today()->subWeeks(2),
            'ended_at' => today()->subDay(),
            'status' => SprintStatus::Active,
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ])->assertOk();

        $this->assertSame(SprintStatus::Finished, $sprint->refresh()->status);
    }

    public function test_admin_export_is_pseudonymized(): void
    {
        $admin = User::factory()->admin()->create();
        $participant = User::factory()->create([
            'nickname' => 'IdentidadeSecreta',
            'email' => 'secreto@example.com',
        ]);
        $participant->tasks()->create([
            'title' => 'Concluída',
            'status' => TaskStatus::Done,
            'started_at' => now()->subHour(),
            'completed_at' => now(),
        ]);
        $token = auth('api')->login($admin);

        $this->withToken($token)
            ->getJson('/api/admin/analytics')
            ->assertOk()
            ->assertJsonPath('data.cycle_time_hours.global_average', 1);

        $response = $this->withToken($token)
            ->get('/api/admin/export')
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringNotContainsString('IdentidadeSecreta', $content);
        $this->assertStringNotContainsString('secreto@example.com', $content);
        $this->assertStringContainsString('user_hash,task_type', $content);
    }
}
