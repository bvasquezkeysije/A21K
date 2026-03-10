<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Tareas</h1>
        @can('tasks.create')
            <button type="button" class="btn btn-primary" wire:click="openCreateModal">Nueva tarea</button>
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
                        <th>Titulo</th>
                        <th>Proyecto</th>
                        <th>Estado</th>
                        <th>Prioridad</th>
                        <th>Creado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr>
                            <td>{{ $task->id }}</td>
                            <td>{{ $task->title }}</td>
                            <td>{{ $task->project?->name ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $statusClass = match ($task->status) {
                                        'completed' => 'success',
                                        'in_progress' => 'warning',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge text-bg-{{ $statusClass }}">{{ str_replace('_', ' ', $task->status) }}</span>
                            </td>
                            <td>
                                @php
                                    $priorityClass = match ($task->priority) {
                                        'high' => 'danger',
                                        'medium' => 'warning',
                                        default => 'info',
                                    };
                                @endphp
                                <span class="badge text-bg-{{ $priorityClass }}">{{ $task->priority }}</span>
                            </td>
                            <td>{{ $task->created_at?->format('Y-m-d') }}</td>
                            <td class="text-end">
                                @can('tasks.complete')
                                    <button type="button" class="btn btn-sm btn-outline-success" wire:click="toggleStatus({{ $task->id }})">Completar</button>
                                @endcan
                                @can('tasks.update')
                                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="edit({{ $task->id }})">Editar</button>
                                @endcan
                                @can('tasks.delete')
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="confirmDelete({{ $task->id }})">Inactivar</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay tareas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $tasks->links() }}
        </div>
    </div>

    @if ($showCreateModal)
        <div
            class="position-fixed top-0 start-0 w-100 h-100 modal-static-backdrop d-flex align-items-center justify-content-center"
            style="z-index: 1050;"
            wire:click.self="closeCreateModal"
        >
            <div class="card shadow border-0" style="width: min(620px, 95vw);">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>Crear tarea</strong>
                    <button type="button" class="btn-close" aria-label="Close" wire:click="closeCreateModal"></button>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label class="form-label">Proyecto</label>
                            <select class="form-select @error('projectId') is-invalid @enderror" wire:model.defer="projectId">
                                <option value="">Selecciona un proyecto</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                            @error('projectId')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Titulo</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" wire:model.defer="title">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Estado</label>
                                <select class="form-select @error('status') is-invalid @enderror" wire:model.defer="status">
                                    <option value="pending">pending</option>
                                    <option value="in_progress">in_progress</option>
                                    <option value="completed">completed</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prioridad</label>
                                <select class="form-select @error('priority') is-invalid @enderror" wire:model.defer="priority">
                                    <option value="low">low</option>
                                    <option value="medium">medium</option>
                                    <option value="high">high</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" wire:click="closeCreateModal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($showEditModal)
        <div
            class="position-fixed top-0 start-0 w-100 h-100 modal-static-backdrop d-flex align-items-center justify-content-center"
            style="z-index: 1050;"
            wire:click.self="closeEditModal"
        >
            <div class="card shadow border-0" style="width: min(620px, 95vw);">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>Editar tarea</strong>
                    <button type="button" class="btn-close" aria-label="Close" wire:click="closeEditModal"></button>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="update">
                        <div class="mb-3">
                            <label class="form-label">Proyecto</label>
                            <select class="form-select @error('projectId') is-invalid @enderror" wire:model.defer="projectId">
                                <option value="">Selecciona un proyecto</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                            @error('projectId')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Titulo</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" wire:model.defer="title">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Estado</label>
                                <select class="form-select @error('status') is-invalid @enderror" wire:model.defer="status">
                                    <option value="pending">pending</option>
                                    <option value="in_progress">in_progress</option>
                                    <option value="completed">completed</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prioridad</label>
                                <select class="form-select @error('priority') is-invalid @enderror" wire:model.defer="priority">
                                    <option value="low">low</option>
                                    <option value="medium">medium</option>
                                    <option value="high">high</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
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
                    <strong>Inactivar tarea</strong>
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
                            <p class="mb-0 text-muted">La tarea quedara inactiva y no aparecera en listados.</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" wire:click="closeDeleteModal">Cancelar</button>
                    <button type="button" class="btn btn-danger" wire:click="delete">Inactivar tarea</button>
                </div>
            </div>
        </div>
    @endif
</div>
