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

class SprintService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Project $project, array $data): Sprint
    {
        return $project->sprints()->create([
            ...$data,
            'status' => SprintStatus::Planning,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Sprint $sprint, array $data): Sprint
    {
        return DB::transaction(function () use ($sprint, $data): Sprint {
            $lockedSprint = Sprint::query()->lockForUpdate()->findOrFail($sprint->getKey());

            if ($lockedSprint->status !== SprintStatus::Planning) {
                throw ValidationException::withMessages([
                    'status' => 'Apenas Sprints em planejamento podem ser editadas.',
                ]);
            }

            $lockedSprint->update($data);

            return $lockedSprint->refresh();
        });
    }

    public function changeStatus(Sprint $sprint, SprintStatus $targetStatus): Sprint
    {
        return DB::transaction(function () use ($sprint, $targetStatus): Sprint {
            $lockedSprint = Sprint::query()->lockForUpdate()->findOrFail($sprint->getKey());

            if ($lockedSprint->status === $targetStatus) {
                return $lockedSprint;
            }

            if ($targetStatus === SprintStatus::Active) {
                $this->activate($lockedSprint);
            } elseif (
                $targetStatus === SprintStatus::Finished
                && $lockedSprint->status === SprintStatus::Active
            ) {
                $lockedSprint->status = SprintStatus::Finished;
                $lockedSprint->save();
            } else {
                throw ValidationException::withMessages([
                    'status' => 'Transição de status da Sprint inválida.',
                ]);
            }

            return $lockedSprint->refresh();
        });
    }

    public function delete(Sprint $sprint): void
    {
        DB::transaction(function () use ($sprint): void {
            $lockedSprint = Sprint::query()->lockForUpdate()->findOrFail($sprint->getKey());

            Task::query()
                ->where('sprint_id', $lockedSprint->getKey())
                ->update([
                    'sprint_id' => null,
                    'status' => TaskStatus::Backlog->value,
                    'started_at' => null,
                    'completed_at' => null,
                    'updated_at' => now(),
                ]);

            $lockedSprint->delete();
        });
    }

    public function autoCloseExpired(User $user): int
    {
        return Sprint::query()
            ->whereHas('project', fn ($query) => $query->where('user_id', $user->getKey()))
            ->where('status', SprintStatus::Active->value)
            ->whereDate('ended_at', '<', today())
            ->update([
                'status' => SprintStatus::Finished->value,
                'updated_at' => now(),
            ]);
    }

    private function activate(Sprint $sprint): void
    {
        if ($sprint->status !== SprintStatus::Planning) {
            throw ValidationException::withMessages([
                'status' => 'Apenas Sprints em planejamento podem ser ativadas.',
            ]);
        }

        $hasActiveSprint = Sprint::query()
            ->where('project_id', $sprint->project_id)
            ->whereKeyNot($sprint->getKey())
            ->where('status', SprintStatus::Active->value)
            ->lockForUpdate()
            ->exists();

        if ($hasActiveSprint) {
            throw ValidationException::withMessages([
                'status' => 'Já existe uma Sprint ativa neste projeto.',
            ]);
        }

        $sprint->status = SprintStatus::Active;
        $sprint->save();
    }
}
