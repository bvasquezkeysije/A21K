<?php

namespace App\Providers;

use App\Http\Livewire\Dashboard;
use App\Http\Livewire\Projects\Create as ProjectsCreate;
use App\Http\Livewire\Projects\Index as ProjectsIndex;
use App\Http\Livewire\Tasks\Index as TasksIndex;
use App\Http\Livewire\Users\Index as UsersIndex;
use App\Models\Project;
use App\Policies\ProjectPolicy;
use App\Repositories\ProjectRepository;
use App\Repositories\TaskRepository;
use App\Repositories\UserRepository;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ProjectRepository::class);
        $this->app->singleton(TaskRepository::class);
        $this->app->singleton(UserRepository::class);
        $this->app->singleton(DashboardService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceRootUrl((string) config('app.url'));
            URL::forceScheme('https');
        }

        Gate::before(static function ($user, string $ability): ?bool {
            return method_exists($user, 'hasRole') && $user->hasRole('admin') ? true : null;
        });

        Gate::policy(Project::class, ProjectPolicy::class);

        Livewire::component('dashboard', Dashboard::class);
        Livewire::component('projects.index', ProjectsIndex::class);
        Livewire::component('projects.create', ProjectsCreate::class);
        Livewire::component('tasks.index', TasksIndex::class);
        Livewire::component('users.index', UsersIndex::class);
    }
}
