<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Panel de administracion</h1>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total proyectos</p>
                    <h2 class="mb-0">{{ $totalProjects }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total tareas</p>
                    <h2 class="mb-0">{{ $totalTasks }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Tareas completadas</p>
                    <h2 class="mb-0">{{ $completedTasks }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <strong>Actividad reciente</strong>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tarea</th>
                        <th>Proyecto</th>
                        <th>Estado</th>
                        <th>Prioridad</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentActivity as $task)
                        <tr>
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
                            <td>{{ $task->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No hay actividad reciente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
