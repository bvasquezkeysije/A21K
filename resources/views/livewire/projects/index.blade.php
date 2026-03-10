<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Proyectos</h1>
        @can('projects.create')
            <livewire:projects.create />
        @endcan
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripcion</th>
                        @if ($isAdmin)
                            <th>Responsable</th>
                        @endif
                        <th>Tareas</th>
                        <th>Creado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                        <tr>
                            <td>{{ $project->id }}</td>
                            <td>{{ $project->name }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($project->description, 70) ?: 'Sin descripcion' }}</td>
                            @if ($isAdmin)
                                <td>{{ $project->user?->name ?? 'N/A' }}</td>
                            @endif
                            <td>{{ $project->tasks_count }}</td>
                            <td>{{ $project->created_at?->format('Y-m-d') }}</td>
                            <td class="text-end">
                                @can('projects.update')
                                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="edit({{ $project->id }})">Editar</button>
                                @endcan
                                @can('projects.delete')
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="confirmDelete({{ $project->id }})">Inactivar</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 7 : 6 }}" class="text-center text-muted py-4">No hay proyectos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $projects->links() }}
        </div>
    </div>

    @if ($showEditModal)
        <div
            class="position-fixed top-0 start-0 w-100 h-100 modal-static-backdrop d-flex align-items-center justify-content-center"
            style="z-index: 1050;"
            wire:click.self="closeEditModal"
        >
            <div class="card shadow border-0" style="width: min(560px, 95vw);">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>Editar proyecto</strong>
                    <button type="button" class="btn-close" aria-label="Close" wire:click="closeEditModal"></button>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="update">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control @error('editName') is-invalid @enderror" wire:model.defer="editName">
                            @error('editName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripcion</label>
                            <textarea class="form-control @error('editDescription') is-invalid @enderror" rows="4" wire:model.defer="editDescription"></textarea>
                            @error('editDescription')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" wire:click="closeEditModal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($showDeleteModal)
        <div
            class="position-fixed top-0 start-0 w-100 h-100 modal-static-backdrop d-flex align-items-center justify-content-center"
            style="z-index: 1050;"
            wire:click.self="closeDeleteModal"
        >
            <div class="card shadow border-0" style="width: min(500px, 95vw); border-radius: 0.9rem;">
                <div class="card-header bg-white d-flex justify-content-between align-items-center" style="border-top-left-radius: 0.9rem; border-top-right-radius: 0.9rem;">
                    <strong>Inactivar proyecto</strong>
                    <button type="button" class="btn-close" aria-label="Close" wire:click="closeDeleteModal"></button>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <span class="delete-modal-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10.3 3.1 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.1a2 2 0 0 0-3.4 0z"></path>
                                <path d="M12 9v5"></path>
                                <circle cx="12" cy="17" r=".8" fill="currentColor"></circle>
                            </svg>
                        </span>
                        <div>
                            <p class="mb-1 fw-semibold">Se aplicara eliminacion logica.</p>
                            <p class="mb-0 text-muted">El proyecto quedara inactivo y no aparecera en listados.</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" wire:click="closeDeleteModal">Cancelar</button>
                    <button type="button" class="btn btn-danger" wire:click="delete">Inactivar proyecto</button>
                </div>
            </div>
        </div>
    @endif
</div>
