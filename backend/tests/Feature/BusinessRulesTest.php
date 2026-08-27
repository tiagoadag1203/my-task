<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Enums\SprintStatus;
use App\Enums\TaskStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_kanban_wip_limit_is_three_tasks(): void
    {
        $user = User::factory()->create(['role' => RoleEnum::User]);
        $token = auth('api')->login($user);
        $taskIds = [];

        foreach (range(1, 4) as $number) {
            $taskIds[] = $this->withToken($token)
                ->postJson('/api/kanban/tasks', [
                    'title' => "Tarefa {$number}",
                    'description' => null,
                ])
                ->assertCreated()
                ->json('data.id');
        }

        foreach (array_slice($taskIds, 0, 3) as $index => $taskId) {
            $this->withToken($token)
                ->putJson("/api/tasks/{$taskId}", [
                    'title' => 'Tarefa '.($index + 1),
                    'description' => null,
                    'status' => TaskStatus::InProgress->value,
                ])
                ->assertOk();
        }

        $this->withToken($token)
            ->putJson("/api/tasks/{$taskIds[3]}", [
                'title' => 'Tarefa 4',
                'description' => null,
                'status' => TaskStatus::InProgress->value,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_only_one_sprint_can_be_active_per_project(): void
    {
        $user = User::factory()->create();
        $project = $user->projects()->create(['name' => 'Projeto']);
        $first = $project->sprints()->create([
            'name' => 'Sprint 1',
            'started_at' => today(),
            'ended_at' => today()->addWeek(),
        ]);
        $second = $project->sprints()->create([
            'name' => 'Sprint 2',
            'started_at' => today(),
            'ended_at' => today()->addWeek(),
        ]);
        $token = auth('api')->login($user);

        $this->withToken($token)
            ->putJson("/api/sprints/{$first->id}/status", [
                'status' => SprintStatus::Active->value,
            ])
            ->assertOk();

        $this->withToken($token)
            ->putJson("/api/sprints/{$second->id}/status", [
                'status' => SprintStatus::Active->value,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_user_cannot_access_another_users_project(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $project = $owner->projects()->create(['name' => 'Privado']);

        $this->withToken(auth('api')->login($intruder))
            ->getJson("/api/projects/{$project->id}")
            ->assertNotFound();
    }

    public function test_pending_task_migration_resets_status_to_todo(): void
    {
        $user = User::factory()->create();
        $project = $user->projects()->create(['name' => 'Projeto']);
        $finished = $project->sprints()->create([
            'name' => 'Finalizada',
            'started_at' => today()->subWeeks(2),
            'ended_at' => today()->subWeek(),
            'status' => SprintStatus::Finished,
        ]);
        $active = $project->sprints()->create([
            'name' => 'Atual',
            'started_at' => today(),
            'ended_at' => today()->addWeek(),
            'status' => SprintStatus::Active,
        ]);
        $task = $user->tasks()->create([
            'project_id' => $project->id,
            'sprint_id' => $finished->id,
            'title' => 'Pendente',
            'status' => TaskStatus::InProgress,
            'started_at' => now()->subDay(),
        ]);

        $this->withToken(auth('api')->login($user))
            ->putJson("/api/tasks/{$task->id}/migrate", [
                'current_sprint_id' => $active->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.status', TaskStatus::Todo->value)
            ->assertJsonPath('data.sprint_id', $active->id);
    }
}
