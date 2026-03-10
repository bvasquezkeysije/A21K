<?php

namespace App\Http\Livewire\Projects;

use App\Models\Project;
use App\Repositories\ProjectRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public bool $showEditModal = false;

    public bool $showDeleteModal = false;

    public ?int $editingProjectId = null;

    public ?int $deletingProjectId = null;

    public string $editName = '';

    public string $editDescription = '';

    public function mount(): void
    {
        abort_unless(Auth::user()?->can('projects.view'), 403);
    }

    #[On('project-created')]
    public function refreshProjects(): void
    {
        $this->resetPage();
    }

    public function edit(int $projectId, ProjectRepository $projectRepository): void
    {
        $project = $projectRepository->findVisibleById(Auth::user(), $projectId);
        abort_if(! $project, 404);

        $this->authorize('update', $project);

        $this->closeAllModals();
        $this->editingProjectId = $project->id;
        $this->editName = $project->name;
        $this->editDescription = $project->description ?? '';
        $this->showEditModal = true;
        $this->resetValidation();
    }

    public function closeEditModal(): void
    {
        $this->closeAllModals();
        $this->reset(['editName', 'editDescription']);
        $this->resetValidation();
    }

    public function update(ProjectRepository $projectRepository): void
    {
        if (! $this->editingProjectId) {
            return;
        }

        $validated = $this->validate([
            'editName' => ['required', 'string', 'max:255'],
            'editDescription' => ['nullable', 'string', 'max:3000'],
        ]);

        $project = $projectRepository->findVisibleById(Auth::user(), $this->editingProjectId);
        abort_if(! $project, 404);

        $this->authorize('update', $project);

        $project->update([
            'name' => $validated['editName'],
            'description' => $validated['editDescription'],
        ]);

        $this->closeEditModal();
        session()->flash('message', 'Proyecto actualizado correctamente.');
    }

    public function confirmDelete(int $projectId, ProjectRepository $projectRepository): void
    {
        $project = $projectRepository->findVisibleById(Auth::user(), $projectId);
        abort_if(! $project, 404);

        $this->authorize('delete', $project);

        $this->closeAllModals();
        $this->deletingProjectId = $project->id;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->closeAllModals();
    }

    public function delete(ProjectRepository $projectRepository): void
    {
        if (! $this->deletingProjectId) {
            return;
        }

        $project = $projectRepository->findVisibleById(Auth::user(), $this->deletingProjectId);
        abort_if(! $project, 404);

        $this->authorize('delete', $project);
        $project->delete();

        $this->closeDeleteModal();
        session()->flash('message', 'Proyecto inactivado correctamente (eliminacion logica).');
    }

    public function render(ProjectRepository $projectRepository): View
    {
        return view('livewire.projects.index', [
            'projects' => $projectRepository->queryVisibleTo(Auth::user())->latest()->paginate(10),
            'isAdmin' => Auth::user()->hasRole('admin'),
        ])
            ->layout('layouts.app')
            ->title('Proyectos');
    }

    private function closeAllModals(): void
    {
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->editingProjectId = null;
        $this->deletingProjectId = null;
    }
}
