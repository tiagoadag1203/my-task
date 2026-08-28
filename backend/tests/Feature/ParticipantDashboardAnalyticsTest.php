<?php

namespace Tests\Feature;

use App\Enums\SprintStatus;
use App\Enums\TaskStatus;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantDashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_endpoint_requires_authentication_and_participant_role(): void
    {
        $this->getJson('/api/dashboard/analytics')
            ->assertUnauthorized();

        $admin = User::factory()->admin()->create();

        $this->withToken(auth('api')->login($admin))
            ->getJson('/api/dashboard/analytics')
            ->assertForbidden();

        $participant = User::factory()->user()->create();

        $this->withToken(auth('api')->login($participant))
            ->getJson('/api/dashboard/analytics')
            ->assertOk();
    }

    public function test_task_distribution_uses_only_authenticated_users_general_kanban(): void
    {
        CarbonImmutable::setTestNow(
            CarbonImmutable::parse('2026-08-27 12:00:00', 'America/Sao_Paulo')
        );

        $participant = User::factory()->user()->create();
        $otherParticipant = User::factory()->user()->create();

        $this->createTask($participant, TaskStatus::Todo);
        $this->createTask($participant, TaskStatus::Todo);
        $this->createTask($participant, TaskStatus::InProgress);
        $this->createTask($participant, TaskStatus::Done, now());
        $this->createTask($participant, TaskStatus::Backlog);
        $this->createTask($otherParticipant, TaskStatus::Done, now());

        $project = $participant->projects()->create(['name' => 'Projeto']);
        $this->createTask($participant, TaskStatus::Done, now(), $project->getKey());

        $sprint = $project->sprints()->create([
            'name' => 'Sprint',
            'started_at' => today(),
            'ended_at' => today()->addWeek(),
            'status' => SprintStatus::Active,
        ]);
        $this->createTask(
            $participant,
            TaskStatus::Done,
            now(),
            null,
            $sprint->getKey(),
        );

        $this->withToken(auth('api')->login($participant))
            ->getJson('/api/dashboard/analytics')
            ->assertOk()
            ->assertJsonPath('data.task_distribution.todo', 2)
            ->assertJsonPath('data.task_distribution.in_progress', 1)
            ->assertJsonPath('data.task_distribution.done', 1)
            ->assertJsonPath('data.task_distribution.total', 4);
    }

    public function test_weekly_completed_covers_seven_days_with_zeros_and_all_task_contexts(): void
    {
        CarbonImmutable::setTestNow(
            CarbonImmutable::parse('2026-08-27 12:00:00', 'America/Sao_Paulo')
        );

        $participant = User::factory()->user()->create();
        $otherParticipant = User::factory()->user()->create();
        $project = $participant->projects()->create(['name' => 'Projeto']);

        $this->createTask(
            $participant,
            TaskStatus::Done,
            CarbonImmutable::parse('2026-08-21 00:00:00', 'America/Sao_Paulo'),
        );
        $this->createTask(
            $participant,
            TaskStatus::Done,
            CarbonImmutable::parse('2026-08-24 15:00:00', 'America/Sao_Paulo'),
            $project->getKey(),
        );
        $this->createTask(
            $participant,
            TaskStatus::Done,
            CarbonImmutable::parse('2026-08-27 11:59:59', 'America/Sao_Paulo'),
        );
        $this->createTask(
            $participant,
            TaskStatus::Done,
            CarbonImmutable::parse('2026-08-20 23:59:59', 'America/Sao_Paulo'),
        );
        $this->createTask(
            $otherParticipant,
            TaskStatus::Done,
            CarbonImmutable::parse('2026-08-24 10:00:00', 'America/Sao_Paulo'),
        );
        $this->createTask(
            $participant,
            TaskStatus::Todo,
            CarbonImmutable::parse('2026-08-25 10:00:00', 'America/Sao_Paulo'),
        );

        $this->withToken(auth('api')->login($participant))
            ->getJson('/api/dashboard/analytics')
            ->assertOk()
            ->assertJsonPath('data.weekly_completed', [
                ['date' => '2026-08-21', 'count' => 1],
                ['date' => '2026-08-22', 'count' => 0],
                ['date' => '2026-08-23', 'count' => 0],
                ['date' => '2026-08-24', 'count' => 1],
                ['date' => '2026-08-25', 'count' => 1],
                ['date' => '2026-08-26', 'count' => 0],
                ['date' => '2026-08-27', 'count' => 1],
            ]);
    }

    public function test_weekly_dates_follow_sao_paulo_timezone_at_utc_day_rollover(): void
    {
        CarbonImmutable::setTestNow(
            CarbonImmutable::parse('2026-08-28 01:30:00', 'UTC')
        );

        $participant = User::factory()->user()->create();
        $this->createTask(
            $participant,
            TaskStatus::Done,
            CarbonImmutable::parse('2026-08-27 22:15:00', 'America/Sao_Paulo'),
        );

        $response = $this->withToken(auth('api')->login($participant))
            ->getJson('/api/dashboard/analytics')
            ->assertOk();

        $response
            ->assertJsonPath('data.weekly_completed.0.date', '2026-08-21')
            ->assertJsonPath('data.weekly_completed.6.date', '2026-08-27')
            ->assertJsonPath('data.weekly_completed.6.count', 1);
    }

    private function createTask(
        User $user,
        TaskStatus $status,
        mixed $completedAt = null,
        ?int $projectId = null,
        ?int $sprintId = null,
    ): void {
        $user->tasks()->create([
            'title' => 'Tarefa '.fake()->unique()->word(),
            'project_id' => $projectId,
            'sprint_id' => $sprintId,
            'status' => $status,
            'completed_at' => $completedAt,
        ]);
    }
}
