<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\User;
use App\Services\SprintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    public function __construct(private readonly SprintService $sprintService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Project::class);
        $user = $this->user($request);

        $projects = $user->projects()
            ->withCount(['sprints', 'tasks'])
            ->latest()
            ->paginate(15);

        return ProjectResource::collection($projects);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $this->authorize('create', Project::class);
        $project = $this->user($request)->projects()->create($request->validated());

        return (new ProjectResource($project))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $project): ProjectResource
    {
        $user = $this->user($request);
        $this->sprintService->autoCloseExpired($user);
        $model = $user->projects()->withCount(['sprints', 'tasks'])->findOrFail($project);
        $this->authorize('view', $model);

        return new ProjectResource($model);
    }

    public function update(UpdateProjectRequest $request, int $project): ProjectResource
    {
        $model = $this->user($request)->projects()->findOrFail($project);
        $this->authorize('update', $model);
        $model->update($request->validated());

        return new ProjectResource($model->refresh());
    }

    public function destroy(Request $request, int $project): Response
    {
        $model = $this->user($request)->projects()->findOrFail($project);
        $this->authorize('delete', $model);
        $model->delete();

        return response()->noContent();
    }

    private function user(Request $request): User
    {
        /** @var User $user */
        $user = $request->user();

        return $user;
    }
}
