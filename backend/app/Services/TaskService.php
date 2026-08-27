<?php

namespace App\Services;

use App\Enums\SprintStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskService
{
    private const WIP_LIMIT = 3;

    /**
     * @param  array<string, mixed>  $data
     */
    public function createKanban(User $user, array $data): Task
    {
        return $user->tasks()->create([
            ...$data,
            'project_id' => null,
            'sprint_id' => null,
            'status' => TaskStatus::Todo,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createBacklog(User $user, Project $project, array $data): Task
    {
        return $user->tasks()->create([
            ...$data,
            'project_id' => $project->getKey(),
            'sprint_id' => null,
            'status' => TaskStatus::Backlog,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Task $task, array $data): Task
    {
        return DB::transaction(function () use ($task, $data): Task {
            $lockedTask = Task::query()->lockForUpdate()->findOrFail($task->getKey());
            $currentStatus = $lockedTask->status;
            $targetStatus = TaskStatus::from((string) $data['status']);

            $this->validateStatusForContext($lockedTask, $currentStatus, $targetStatus);

            if ($targetStatus === TaskStatus::InProgress && $currentStatus !== TaskStatus::InProgress) {
                $this->ensureWipLimit($lockedTask);
            }

            $lockedTask->fill([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => $targetStatus,
            ]);

            if ($targetStatus === TaskStatus::InProgress && $lockedTask->started_at === null) {
                $lockedTask->started_at = now();
            }

            if ($targetStatus === TaskStatus::Done && $currentStatus !== TaskStatus::Done) {
                $lockedTask->completed_at = now();
            } elseif ($targetStatus !== TaskStatus::Done && $currentStatus === TaskStatus::Done) {
                $lockedTask->completed_at = null;
            }

            $lockedTask->save();

            return $lockedTask->refresh();
        });
    }

    public function migrate(Task $task, Sprint $targetSprint, User $user): Task
    {
        return DB::transaction(function () use ($task, $targetSprint, $user): Task {
            $lockedTask = Task::query()->lockForUpdate()->findOrFail($task->getKey());
            $lockedTarget = Sprint::query()
                ->whereHas('project', fn ($query) => $query->where('user_id', $user->getKey()))
                ->lockForUpdate()
                ->findOrFail($targetSprint->getKey());

            if ($lockedTask->project_id === null || $lockedTask->status === TaskStatus::Done) {
                throw ValidationException::withMessages([
                    'current_sprint_id' => 'Apenas tarefas pendentes de projetos podem ser migradas.',
                ]);
            }

            if ((int) $lockedTask->project_id !== (int) $lockedTarget->project_id) {
                throw ValidationException::withMessages([
                    'current_sprint_id' => 'A Sprint de destino deve pertencer ao mesmo projeto da tarefa.',
                ]);
            }

            if ((int) $lockedTask->sprint_id === (int) $lockedTarget->getKey()) {
                throw ValidationException::withMessages([
                    'current_sprint_id' => 'A tarefa já pertence à Sprint informada.',
                ]);
            }

            $this->validateMigrationTarget($lockedTask, $lockedTarget);

            $lockedTask->sprint_id = $lockedTarget->getKey();
            $lockedTask->status = TaskStatus::Todo;
            $lockedTask->completed_at = null;
            $lockedTask->save();

            return $lockedTask->refresh();
        });
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }

    private function validateStatusForContext(
        Task $task,
        TaskStatus $currentStatus,
        TaskStatus $targetStatus,
    ): void {
        if ($task->project_id === null && $targetStatus === TaskStatus::Backlog) {
            throw ValidationException::withMessages([
                'status' => 'Tarefas do Kanban Geral não podem utilizar o status BACKLOG.',
            ]);
        }

        if ($task->project_id !== null && $task->sprint_id === null && $targetStatus !== TaskStatus::Backlog) {
            throw ValidationException::withMessages([
                'status' => 'A tarefa deve ser vinculada a uma Sprint antes de sair do BACKLOG.',
            ]);
        }

        if ($task->sprint_id !== null && $targetStatus === TaskStatus::Backlog) {
            throw ValidationException::withMessages([
                'status' => 'Tarefas vinculadas a uma Sprint não podem retornar diretamente ao BACKLOG.',
            ]);
        }

        if ($task->sprint_id !== null && $currentStatus !== $targetStatus) {
            $isActive = Sprint::query()
                ->whereKey($task->sprint_id)
                ->where('status', SprintStatus::Active->value)
                ->exists();

            if (! $isActive) {
                throw ValidationException::withMessages([
                    'status' => 'O status só pode ser alterado em tarefas de uma Sprint ativa.',
                ]);
            }
        }
    }

    private function ensureWipLimit(Task $task): void
    {
        $query = Task::query()
            ->where('user_id', $task->user_id)
            ->where('status', TaskStatus::InProgress->value);

        if ($task->project_id === null) {
            $query->whereNull('project_id');
        } else {
            $query->where('sprint_id', $task->sprint_id);
        }

        $inProgressCount = $query->lockForUpdate()->count();

        if ($inProgressCount >= self::WIP_LIMIT) {
            throw ValidationException::withMessages([
                'status' => 'Para evitar sobrecarga mental e multitarefa ineficiente, finalize uma de suas tarefas em progresso antes de assumir um novo compromisso.',
            ]);
        }
    }

    private function validateMigrationTarget(Task $task, Sprint $targetSprint): void
    {
        if ($task->sprint_id === null) {
            if (! in_array($targetSprint->status, [SprintStatus::Planning, SprintStatus::Active], true)) {
                throw ValidationException::withMessages([
                    'current_sprint_id' => 'Tarefas do backlog só podem ser alocadas em Sprints planejadas ou ativas.',
                ]);
            }

            return;
        }

        $sourceIsFinished = Sprint::query()
            ->whereKey($task->sprint_id)
            ->where('status', SprintStatus::Finished->value)
            ->exists();

        if (! $sourceIsFinished || $targetSprint->status !== SprintStatus::Active) {
            throw ValidationException::withMessages([
                'current_sprint_id' => 'A migração exige uma tarefa pendente de Sprint finalizada e uma Sprint ativa como destino.',
            ]);
        }
    }
}
