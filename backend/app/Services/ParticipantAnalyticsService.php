<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ParticipantAnalyticsService
{
    private const TIMEZONE = 'America/Sao_Paulo';

    /**
     * @return array{
     *     task_distribution: array{todo: int, in_progress: int, done: int, total: int},
     *     weekly_completed: list<array{date: string, count: int}>
     * }
     */
    public function analytics(User $user): array
    {
        return [
            'task_distribution' => $this->taskDistribution($user),
            'weekly_completed' => $this->weeklyCompleted($user),
        ];
    }

    /**
     * @return array{todo: int, in_progress: int, done: int, total: int}
     */
    private function taskDistribution(User $user): array
    {
        /** @var Collection<string, int> $counts */
        $counts = $user->tasks()
            ->whereNull('project_id')
            ->whereNull('sprint_id')
            ->whereIn('status', [
                TaskStatus::Todo->value,
                TaskStatus::InProgress->value,
                TaskStatus::Done->value,
            ])
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn (mixed $count): int => (int) $count);

        $todo = $counts->get(TaskStatus::Todo->value, 0);
        $inProgress = $counts->get(TaskStatus::InProgress->value, 0);
        $done = $counts->get(TaskStatus::Done->value, 0);

        return [
            'todo' => $todo,
            'in_progress' => $inProgress,
            'done' => $done,
            'total' => $todo + $inProgress + $done,
        ];
    }

    /**
     * @return list<array{date: string, count: int}>
     */
    private function weeklyCompleted(User $user): array
    {
        $today = CarbonImmutable::now(self::TIMEZONE)->startOfDay();
        $start = $today->subDays(6);
        $end = $today->endOfDay();

        $counts = $user->tasks()
            ->whereBetween('completed_at', [$start, $end])
            ->get(['completed_at'])
            ->countBy(
                fn (Task $task): string => $task->completed_at
                    ->setTimezone(self::TIMEZONE)
                    ->toDateString()
            );

        return collect(range(0, 6))
            ->map(function (int $offset) use ($start, $counts): array {
                $date = $start->addDays($offset)->toDateString();

                return [
                    'date' => $date,
                    'count' => (int) $counts->get($date, 0),
                ];
            })
            ->all();
    }
}
