<?php

namespace App\Http\Controllers;

use App\Enums\SprintStatus;
use App\Http\Requests\StoreSprintRequest;
use App\Http\Requests\UpdateSprintRequest;
use App\Http\Requests\UpdateSprintStatusRequest;
use App\Http\Resources\SprintResource;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\User;
use App\Services\SprintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SprintController extends Controller
{
    public function __construct(private readonly SprintService $sprintService) {}

    public function index(Request $request, int $project): AnonymousResourceCollection
    {
        $user = $this->user($request);
        $this->sprintService->autoCloseExpired($user);
        $projectModel = $this->project($user, $project);
        $this->authorize('view', $projectModel);

        return SprintResource::collection(
            $projectModel->sprints()->withCount('tasks')->latest('started_at')->get()
        );
    }

    public function store(StoreSprintRequest $request, int $project): JsonResponse
    {
        $projectModel = $this->project($this->user($request), $project);
        $this->authorize('create', [Sprint::class, $projectModel]);
        $sprint = $this->sprintService->create($projectModel, $request->validated());

        return (new SprintResource($sprint))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $sprint): SprintResource
    {
        $model = $this->sprint($this->user($request), $sprint);
        $this->authorize('view', $model);

        return new SprintResource($model->loadCount('tasks'));
    }

    public function update(UpdateSprintRequest $request, int $sprint): SprintResource
    {
        $model = $this->sprint($this->user($request), $sprint);
        $this->authorize('update', $model);

        return new SprintResource(
            $this->sprintService->update($model, $request->validated())
        );
    }

    public function destroy(Request $request, int $sprint): Response
    {
        $model = $this->sprint($this->user($request), $sprint);
        $this->authorize('delete', $model);
        $this->sprintService->delete($model);

        return response()->noContent();
    }

    public function updateStatus(UpdateSprintStatusRequest $request, int $sprint): SprintResource
    {
        $model = $this->sprint($this->user($request), $sprint);
        $this->authorize('changeStatus', $model);
        $status = SprintStatus::from((string) $request->validated('status'));

        return new SprintResource($this->sprintService->changeStatus($model, $status));
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

    private function user(Request $request): User
    {
        /** @var User $user */
        $user = $request->user();

        return $user;
    }
}
