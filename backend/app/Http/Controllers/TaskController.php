<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\MigrateTaskRequest;
use App\Http\Requests\StoreBacklogTaskRequest;
use App\Http\Requests\StoreKanbanTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $taskService) {}

    public function kanbanIndex(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Task::class);
        $tasks = $this->user($request)->tasks()
            ->whereNull('project_id')
            ->orderBy('created_at')
            ->get();

        return TaskResource::collection($tasks);
    }

    public function kanbanStore(StoreKanbanTaskRequest $request): JsonResponse
    {
        $this->authorize('create', Task::class);
        $task = $this->taskService->createKanban($this->user($request), $request->validated());

        return (new TaskResource($task))->response()->setStatusCode(201);
    }

    public function backlogIndex(Request $request, int $project): AnonymousResourceCollection
    {
        $projectModel = $this->project($this->user($request), $project);
        $this->authorize('view', $projectModel);

        return TaskResource::collection(
            $projectModel->tasks()
                ->whereNull('sprint_id')
                ->where('status', TaskStatus::Backlog->value)
                ->orderBy('created_at')
                ->get()
        );
    }

    public function backlogStore(StoreBacklogTaskRequest $request, int $project): JsonResponse
    {
        $projectModel = $this->project($this->user($request), $project);
        $this->authorize('update', $projectModel);
        $task = $this->taskService->createBacklog(
            $this->user($request),
            $projectModel,
            $request->validated(),
        );

        return (new TaskResource($task))->response()->setStatusCode(201);
    }

    public function sprintTasks(Request $request, int $sprint): AnonymousResourceCollection
    {
        $sprintModel = $this->sprint($this->user($request), $sprint);
        $this->authorize('view', $sprintModel);

        return TaskResource::collection(
            $sprintModel->tasks()->orderBy('created_at')->get()
        );
    }

    public function update(UpdateTaskRequest $request, int $task): TaskResource
    {
        $model = $this->task($this->user($request), $task);
        $this->authorize('update', $model);

        return new TaskResource(
            $this->taskService->update($model, $request->validated())
        );
    }

    public function destroy(Request $request, int $task): Response
    {
        $model = $this->task($this->user($request), $task);
        $this->authorize('delete', $model);
        $this->taskService->delete($model);

        return response()->noContent();
    }

    public function migrate(MigrateTaskRequest $request, int $task): TaskResource
    {
        $user = $this->user($request);
        $model = $this->task($user, $task);
        $this->authorize('migrate', $model);
        $target = $this->sprint($user, (int) $request->validated('current_sprint_id'));

        return new TaskResource($this->taskService->migrate($model, $target, $user));
    }

    private function project(User $user, int $id): Project
    {
        return $user->projects()->findOrFail($id);
    }

    private function sprint(User $user, int $id): Sprint
    {
        return Sprint::query()
            ->whereHas('project', fn ($query) => $query->where('user_id', $user->getKey()))
            ->findOrFail($id);
    }

    private function task(User $user, int $id): Task
    {
        return $user->tasks()->findOrFail($id);
    }

    private function user(Request $request): User
    {
        /** @var User $user */
        $user = $request->user();

        return $user;
    }
}
