<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProjectRepository
{
    public function queryVisibleTo(User $user): Builder
    {
        $query = Project::query()
            ->with('user:id,name,email')
            ->withCount('tasks');

        if (! $this->isAdmin($user)) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    public function listForDropdown(User $user): Collection
    {
        return $this->queryVisibleTo($user)
            ->orderBy('name')
            ->get(['id', 'name', 'user_id']);
    }

    public function createForUser(User $user, array $attributes): Project
    {
        $attributes['user_id'] = $attributes['user_id'] ?? $user->id;

        if (! $this->isAdmin($user)) {
            $attributes['user_id'] = $user->id;
        }

        return Project::create($attributes);
    }

    public function findVisibleById(User $user, int $projectId): ?Project
    {
        return $this->queryVisibleTo($user)->whereKey($projectId)->first();
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
