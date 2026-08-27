<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\User;

class SprintPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Sprint $sprint): bool
    {
        return $this->owns($user, $sprint);
    }

    public function create(User $user, Project $project): bool
    {
        return (int) $project->user_id === (int) $user->getKey();
    }

    public function update(User $user, Sprint $sprint): bool
    {
        return $this->owns($user, $sprint);
    }

    public function delete(User $user, Sprint $sprint): bool
    {
        return $this->owns($user, $sprint);
    }

    public function changeStatus(User $user, Sprint $sprint): bool
    {
        return $this->owns($user, $sprint);
    }

    private function owns(User $user, Sprint $sprint): bool
    {
        return Project::query()
            ->whereKey($sprint->project_id)
            ->where('user_id', $user->getKey())
            ->exists();
    }
}
