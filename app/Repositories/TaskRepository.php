<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class TaskRepository
{
    public function queryVisibleTo(User $user): Builder
    {
        $query = Task::query()->with('project:id,name,user_id');

        if (! $this->isAdmin($user)) {
            $query->whereHas('project', fn (Builder $projectQuery) => $projectQuery->where('user_id', $user->id));
        }

        return $query;
    }

    public function findVisibleById(User $user, int $taskId): ?Task
    {
        return $this->queryVisibleTo($user)->whereKey($taskId)->first();
    }

    public function projectIsVisible(User $user, int $projectId): bool
    {
        $query = Project::query()->whereKey($projectId);

        if (! $this->isAdmin($user)) {
            $query->where('user_id', $user->id);
        }

        return $query->exists();
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
