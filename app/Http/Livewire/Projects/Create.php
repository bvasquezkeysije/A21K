<?php

namespace App\Http\Livewire\Projects;

use App\Models\Project;
use App\Repositories\ProjectRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

    public bool $showModal = false;

    public string $name = '';

    public string $description = '';

    public function openModal(): void
    {
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->reset(['showModal', 'name', 'description']);
        $this->resetValidation();
    }

    public function save(ProjectRepository $projectRepository): void
    {
        $this->authorize('create', Project::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
        ]);

        $projectRepository->createForUser(auth()->user(), $validated);

        $this->closeModal();
        $this->dispatch('project-created');
        session()->flash('message', 'Proyecto creado correctamente.');
    }

    public function render(): View
    {
        return view('livewire.projects.create');
    }
}
