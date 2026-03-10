<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Usuarios</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">Nuevo usuario</button>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    @error('delete')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Creado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->person?->first_name ?? $user->name }}</td>
                            <td>{{ $user->person?->last_name ?? '-' }}</td>
                            <td>{{ $user->username ?? '-' }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge text-bg-secondary">{{ $user->roles->pluck('name')->join(', ') ?: 'Sin rol' }}</span>
                            </td>
                            <td>
                                @if ((int) $user->status === 1)
                                    <span class="badge text-bg-success">ACTIVO (1)</span>
                                @else
                                    <span class="badge text-bg-danger">INACTIVO (0)</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at?->format('Y-m-d') }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-2">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary action-icon-btn d-inline-flex align-items-center justify-content-center"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editUserModal"
                                        data-user-id="{{ $user->id }}"
                                        data-first-name="{{ $user->person?->first_name ?? $user->name }}"
                                        data-last-name="{{ $user->person?->last_name ?? '' }}"
                                        data-username="{{ $user->username ?? '' }}"
                                        data-email="{{ $user->email }}"
                                        data-role="{{ $user->roles->pluck('name')->first() ?? 'user' }}"
                                        data-update-url="{{ route('users.update', $user) }}"
                                        title="Editar"
                                        aria-label="Editar"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                                            <path d="M15.502 1.94a.5.5 0 0 1 0 .706l-1.793 1.793-2.147-2.147L13.355.5a.5.5 0 0 1 .707 0l1.44 1.44z"/>
                                            <path d="M11.354 3.146 2 12.5V15h2.5l9.354-9.354-2.5-2.5z"/>
                                        </svg>
                                    </button>
                                    @if ((int) $user->status === 1)
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger action-icon-btn d-inline-flex align-items-center justify-content-center"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deactivateUserModal"
                                            data-deactivate-url="{{ route('users.deactivate', $user) }}"
                                            data-user-name="{{ $user->person?->first_name ?? $user->name }}"
                                            data-user-username="{{ $user->username ?? '-' }}"
                                            title="Inactivar"
                                            aria-label="Inactivar"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm4 0A.5.5 0 0 1 10 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Z"/>
                                                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 1 1 0-2H5V1a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1h2.5a1 1 0 0 1 1 1ZM6 1v1h4V1H6Z"/>
                                            </svg>
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('users.activate', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-success action-icon-btn d-inline-flex align-items-center justify-content-center"
                                                title="Activar"
                                                aria-label="Activar"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                                                    <path d="M8 15A7 7 0 1 1 8 1a.5.5 0 0 1 0 1 6 6 0 1 0 6 6 .5.5 0 0 1 1 0A7 7 0 0 1 8 15Z"/>
                                                    <path d="M8 4a.5.5 0 0 1 .5.5V8h2.5a.5.5 0 0 1 0 1H8A.5.5 0 0 1 7.5 8V4.5A.5.5 0 0 1 8 4Z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No hay usuarios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $users->links() }}
        </div>
    </div>

    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">Crear usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('users.store') }}">
                        @csrf
                        <input type="hidden" name="_form" value="create">
                        <div class="mb-3">
                            <label class="form-label">Nombres</label>
                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            <small class="text-muted">Minimo 3 caracteres.</small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                @foreach ($roles as $roleName)
                                    <option value="{{ $roleName }}" @selected(old('role', 'user') === $roleName)>{{ $roleName }}</option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Editar usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="" id="editUserForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form" value="edit">
                        <input type="hidden" name="user_id" id="edit_user_id" value="{{ old('user_id') }}">

                        <div class="mb-3">
                            <label class="form-label">Nombres</label>
                            <input type="text" name="first_name" id="edit_first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" name="last_name" id="edit_last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" name="username" id="edit_username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password (opcional)</label>
                            <input type="password" name="password" id="edit_password" class="form-control @error('password') is-invalid @enderror">
                            <small class="text-muted">Minimo 3 caracteres si deseas cambiarla.</small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select name="role" id="edit_role" class="form-select @error('role') is-invalid @enderror" required>
                                @foreach ($roles as $roleName)
                                    <option value="{{ $roleName }}" @selected(old('role', 'user') === $roleName)>{{ $roleName }}</option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deactivateUserModal" tabindex="-1" aria-labelledby="deactivateUserModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="deactivateUserModalLabel">Inactivar usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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
                            <p class="mb-1 text-muted">
                                Esta accion cambiara el estado a <strong>INACTIVO (0)</strong>.
                            </p>
                            <p class="mb-0 text-muted">
                                Usuario:
                                <strong id="deactivate_user_name">-</strong>
                                (<span id="deactivate_user_username">-</span>)
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="" id="deactivateUserForm" class="m-0">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-danger">Inactivar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (!window.bootstrap) {
                    return;
                }

                const createModalElement = document.getElementById('createUserModal');
                const editModalElement = document.getElementById('editUserModal');
                const deactivateModalElement = document.getElementById('deactivateUserModal');
                const editForm = document.getElementById('editUserForm');
                const editUserId = document.getElementById('edit_user_id');
                const editFirstName = document.getElementById('edit_first_name');
                const editLastName = document.getElementById('edit_last_name');
                const editUsername = document.getElementById('edit_username');
                const editEmail = document.getElementById('edit_email');
                const editRole = document.getElementById('edit_role');
                const editPassword = document.getElementById('edit_password');
                const deactivateForm = document.getElementById('deactivateUserForm');
                const deactivateUserName = document.getElementById('deactivate_user_name');
                const deactivateUserUsername = document.getElementById('deactivate_user_username');

                if (editModalElement) {
                    editModalElement.addEventListener('show.bs.modal', function (event) {
                        const trigger = event.relatedTarget;
                        if (!trigger) {
                            return;
                        }

                        if (editForm) editForm.action = trigger.getAttribute('data-update-url') || '';
                        if (editUserId) editUserId.value = trigger.getAttribute('data-user-id') || '';
                        if (editFirstName) editFirstName.value = trigger.getAttribute('data-first-name') || '';
                        if (editLastName) editLastName.value = trigger.getAttribute('data-last-name') || '';
                        if (editUsername) editUsername.value = trigger.getAttribute('data-username') || '';
                        if (editEmail) editEmail.value = trigger.getAttribute('data-email') || '';
                        if (editRole) editRole.value = trigger.getAttribute('data-role') || 'user';
                        if (editPassword) editPassword.value = '';
                    });
                }

                if (deactivateModalElement) {
                    deactivateModalElement.addEventListener('show.bs.modal', function (event) {
                        const trigger = event.relatedTarget;
                        if (!trigger) {
                            return;
                        }

                        if (deactivateForm) deactivateForm.action = trigger.getAttribute('data-deactivate-url') || '';
                        if (deactivateUserName) deactivateUserName.textContent = trigger.getAttribute('data-user-name') || '-';
                        if (deactivateUserUsername) deactivateUserUsername.textContent = trigger.getAttribute('data-user-username') || '-';
                    });
                }

                const formType = @json(old('_form'));
                if (formType === 'create' && createModalElement) {
                    new bootstrap.Modal(createModalElement).show();
                }

                if (formType === 'edit' && editModalElement) {
                    const userId = @json(old('user_id'));
                    const updateTemplate = @json(route('users.update', ['user' => '__USER__']));

                    if (userId && editForm) {
                        editForm.action = updateTemplate.replace('__USER__', String(userId));
                    }

                    new bootstrap.Modal(editModalElement).show();
                }
            });
        </script>
    @endpush

</div>
