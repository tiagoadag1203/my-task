<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Enums\SprintStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsService
{
    /**
     * @return array<string, int>
     */
    public function summary(): array
    {
        return [
            'users' => User::query()->where('role', RoleEnum::User->value)->count(),
            'projects' => Project::query()->count(),
            'tasks' => Task::query()->count(),
            'completed_tasks' => Task::query()->where('status', TaskStatus::Done->value)->count(),
            'sprints' => Sprint::query()->count(),
            'active_sprints' => Sprint::query()->where('status', SprintStatus::Active->value)->count(),
            'finished_sprints' => Sprint::query()->where('status', SprintStatus::Finished->value)->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function analytics(): array
    {
        $completedTasks = Task::query()
            ->where('status', TaskStatus::Done->value)
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->get(['project_id', 'started_at', 'completed_at']);

        $kanbanCycleTimes = $this->cycleTimes($completedTasks->whereNull('project_id'));
        $scrumCycleTimes = $this->cycleTimes($completedTasks->whereNotNull('project_id'));
        $allCycleTimes = $kanbanCycleTimes->concat($scrumCycleTimes);

        $finishedSprints = Sprint::query()
            ->where('status', SprintStatus::Finished->value)
            ->withCount([
                'tasks',
                'tasks as done_tasks_count' => fn (Builder $query) => $query
                    ->where('status', TaskStatus::Done->value),
            ])
            ->get();

        $completionRates = $finishedSprints
            ->filter(fn (Sprint $sprint): bool => $sprint->tasks_count > 0)
            ->map(fn (Sprint $sprint): float => ($sprint->done_tasks_count / $sprint->tasks_count) * 100);

        return [
            'cycle_time_hours' => [
                'global_average' => $this->averageHours($allCycleTimes),
                'kanban_average' => $this->averageHours($kanbanCycleTimes),
                'scrum_average' => $this->averageHours($scrumCycleTimes),
                'sample_size' => $allCycleTimes->count(),
            ],
            'sprints' => [
                'finished_count' => $finishedSprints->count(),
                'average_completion_rate' => round((float) ($completionRates->average() ?? 0), 2),
            ],
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        $salt = Str::random(64);
        $filename = 'mytask-export-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($salt): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                throw new RuntimeException('Não foi possível iniciar a exportação.');
            }

            fputcsv($handle, [
                'user_hash',
                'task_type',
                'created_at',
                'started_at',
                'completed_at',
                'cycle_time_seconds',
                'status_final',
            ], ',', '"', '\\');

            Task::query()
                ->orderBy('id')
                ->chunkById(500, function ($tasks) use ($handle, $salt): void {
                    foreach ($tasks as $task) {
                        $cycleTime = null;

                        if ($task->started_at !== null && $task->completed_at !== null) {
                            $cycleTime = (int) $task->started_at->diffInSeconds($task->completed_at);
                        }

                        fputcsv($handle, [
                            hash_hmac('sha256', (string) $task->user_id, $salt),
                            $task->project_id === null ? 'KANBAN' : 'SCRUM',
                            $task->created_at?->toIso8601String(),
                            $task->started_at?->toIso8601String(),
                            $task->completed_at?->toIso8601String(),
                            $cycleTime,
                            $task->status->value,
                        ], ',', '"', '\\');
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  Collection<int, Task>  $tasks
     * @return Collection<int, int>
     */
    private function cycleTimes(Collection $tasks): Collection
    {
        return $tasks
            ->map(
                fn (Task $task): int => (int) $task->started_at?->diffInSeconds($task->completed_at)
            )
            ->values();
    }

    /**
     * @param  Collection<int, int>  $values
     */
    private function averageHours(Collection $values): float
    {
        return round((float) ($values->average() ?? 0) / 3600, 2);
    }
}
