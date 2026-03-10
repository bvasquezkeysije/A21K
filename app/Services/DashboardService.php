<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class DashboardService
{
    public function getStatsFor(User $user): array
    {
        $projects = Project::query();
        $tasks = Task::query()->with('project:id,name,user_id');

        if (! $this->isAdmin($user)) {
            $projects->where('user_id', $user->id);
            $tasks->whereHas('project', fn (Builder $projectQuery) => $projectQuery->where('user_id', $user->id));
        }

        $totalProjects = (clone $projects)->count();
        $totalTasks = (clone $tasks)->count();
        $completedTasks = (clone $tasks)->where('status', Task::STATUS_COMPLETED)->count();
        $recentActivity = (clone $tasks)->latest()->take(8)->get();

        return compact('totalProjects', 'totalTasks', 'completedTasks', 'recentActivity');
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
