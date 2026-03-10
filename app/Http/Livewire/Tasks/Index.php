<?php

namespace App\Http\Livewire\Tasks;

use App\Models\Task;
use App\Repositories\ProjectRepository;
use App\Repositories\TaskRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showDeleteModal = false;

    public ?int $editingTaskId = null;

    public ?int $deletingTaskId = null;

    public ?int $projectId = null;

    public string $title = '';

    public string $status = Task::STATUS_PENDING;

    public string $priority = Task::PRIORITY_MEDIUM;

    public function mount(): void
    {
        abort_unless(Auth::user()?->can('tasks.view'), 403);
    }

    public function openCreateModal(): void
    {
        abort_unless(Auth::user()?->can('tasks.create'), 403);

        $this->closeAllModals();
        $this->resetValidation();
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->closeAllModals();
        $this->resetForm();
        $this->resetValidation();
    }

    public function save(TaskRepository $taskRepository): void
    {
        abort_unless(Auth::user()?->can('tasks.create'), 403);

        $validated = $this->validate([
            'projectId' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:pending,in_progress,completed'],
            'priority' => ['required', 'in:low,medium,high'],
        ]);

        if (! $taskRepository->projectIsVisible(Auth::user(), (int) $validated['projectId'])) {
            $this->addError('projectId', 'No tienes permiso para usar este proyecto.');

            return;
        }

        Task::create([
            'project_id' => $validated['projectId'],
            'title' => $validated['title'],
            'status' => $validated['status'],
            'priority' => $validated['priority'],
        ]);

        $this->closeCreateModal();
        session()->flash('message', 'Tarea creada correctamente.');
    }

    public function edit(int $taskId, TaskRepository $taskRepository): void
    {
        abort_unless(Auth::user()?->can('tasks.update'), 403);

        $task = $taskRepository->findVisibleById(Auth::user(), $taskId);
        abort_if(! $task, 404);

        $this->closeAllModals();
        $this->editingTaskId = $task->id;
        $this->projectId = $task->project_id;
        $this->title = $task->title;
        $this->status = $task->status;
        $this->priority = $task->priority;
        $this->showEditModal = true;
        $this->resetValidation();
    }

    public function closeEditModal(): void
    {
        $this->closeAllModals();
        $this->resetForm();
        $this->resetValidation();
    }

    public function update(TaskRepository $taskRepository): void
    {
        abort_unless(Auth::user()?->can('tasks.update'), 403);

        if (! $this->editingTaskId) {
            return;
        }

        $validated = $this->validate([
            'projectId' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:pending,in_progress,completed'],
            'priority' => ['required', 'in:low,medium,high'],
        ]);

        $task = $taskRepository->findVisibleById(Auth::user(), $this->editingTaskId);
        abort_if(! $task, 404);

        if (! $taskRepository->projectIsVisible(Auth::user(), (int) $validated['projectId'])) {
            $this->addError('projectId', 'No tienes permiso para usar este proyecto.');

            return;
        }

        $task->update([
            'project_id' => $validated['projectId'],
            'title' => $validated['title'],
            'status' => $validated['status'],
            'priority' => $validated['priority'],
        ]);

        $this->closeEditModal();
        session()->flash('message', 'Tarea actualizada correctamente.');
    }

    public function confirmDelete(int $taskId, TaskRepository $taskRepository): void
    {
        abort_unless(Auth::user()?->can('tasks.delete'), 403);

        $task = $taskRepository->findVisibleById(Auth::user(), $taskId);
        abort_if(! $task, 404);

        $this->closeAllModals();
        $this->deletingTaskId = $task->id;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->closeAllModals();
    }

    public function delete(TaskRepository $taskRepository): void
    {
        abort_unless(Auth::user()?->can('tasks.delete'), 403);

        if (! $this->deletingTaskId) {
            return;
        }

        $task = $taskRepository->findVisibleById(Auth::user(), $this->deletingTaskId);
        abort_if(! $task, 404);

        $task->delete();

        $this->closeDeleteModal();
        session()->flash('message', 'Tarea inactivada correctamente (eliminacion logica).');
    }

    public function toggleStatus(int $taskId, TaskRepository $taskRepository): void
    {
        abort_unless(Auth::user()?->can('tasks.complete'), 403);

        $task = $taskRepository->findVisibleById(Auth::user(), $taskId);
        abort_if(! $task, 404);

        $task->status = $task->status === Task::STATUS_COMPLETED
            ? Task::STATUS_PENDING
            : Task::STATUS_COMPLETED;
        $task->save();
    }

    public function render(TaskRepository $taskRepository, ProjectRepository $projectRepository): View
    {
        return view('livewire.tasks.index', [
            'tasks' => $taskRepository->queryVisibleTo(Auth::user())->latest()->paginate(10),
            'projects' => $projectRepository->listForDropdown(Auth::user()),
        ])
            ->layout('layouts.app')
            ->title('Tareas');
    }

    private function resetForm(): void
    {
        $this->projectId = null;
        $this->title = '';
        $this->status = Task::STATUS_PENDING;
        $this->priority = Task::PRIORITY_MEDIUM;
    }

    private function closeAllModals(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->editingTaskId = null;
        $this->deletingTaskId = null;
    }
}
