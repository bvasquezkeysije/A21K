<?php

namespace App\Http\Livewire\Users;

use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showDeleteModal = false;

    public ?int $editingUserId = null;

    public ?int $deletingUserId = null;

    public string $firstName = '';

    public string $lastName = '';

    public string $username = '';

    public string $email = '';

    public string $password = '';

    public string $role = 'user';

    public function mount(): void
    {
        abort_unless(Auth::user()?->can('users.manage'), 403);
    }

    public function openCreateModal(): void
    {
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

    public function save(): void
    {
        $availableRoles = $this->availableRoles();

        $this->validate([
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:255', Rule::unique('users', 'username')],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:3'],
            'role' => ['required', Rule::in($availableRoles)],
        ]);

        $person = Person::create([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
        ]);

        $user = User::create([
            'name' => $this->fullName(),
            'username' => $this->username,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'email_verified_at' => now(),
            'person_id' => $person->id,
            'status' => 1,
        ]);

        $user->syncRoles([$this->role]);

        $this->closeCreateModal();
        session()->flash('message', 'Usuario creado correctamente.');
    }

    public function edit(int $userId): void
    {
        $user = $this->usersQuery()->whereKey($userId)->first();
        abort_if(! $user, 404);

        $this->closeAllModals();
        $this->editingUserId = $user->id;
        $this->firstName = $user->person?->first_name ?? $user->name;
        $this->lastName = $user->person?->last_name ?? '';
        $this->username = $user->username ?? '';
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->roles()->value('name') ?? 'user';
        $this->showEditModal = true;
        $this->resetValidation();
    }

    public function closeEditModal(): void
    {
        $this->closeAllModals();
        $this->resetForm();
        $this->resetValidation();
    }

    public function update(): void
    {
        $availableRoles = $this->availableRoles();

        if (! $this->editingUserId) {
            return;
        }

        $this->validate([
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:255', Rule::unique('users', 'username')->ignore($this->editingUserId)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingUserId)],
            'password' => ['nullable', 'string', 'min:3'],
            'role' => ['required', Rule::in($availableRoles)],
        ]);

        $user = $this->usersQuery()->whereKey($this->editingUserId)->first();
        abort_if(! $user, 404);

        $person = $user->person;

        if ($person) {
            $person->update([
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
            ]);
        } else {
            $person = Person::create([
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
            ]);
        }

        $attributes = [
            'name' => $this->fullName(),
            'username' => $this->username,
            'email' => $this->email,
            'person_id' => $person->id,
        ];

        if ($this->password !== '') {
            $attributes['password'] = Hash::make($this->password);
        }

        $user->update($attributes);
        $user->syncRoles([$this->role]);

        $this->closeEditModal();
        session()->flash('message', 'Usuario actualizado correctamente.');
    }

    public function confirmDelete(int $userId): void
    {
        $user = $this->usersQuery()->whereKey($userId)->first();
        abort_if(! $user, 404);

        $this->closeAllModals();
        $this->deletingUserId = $user->id;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->closeAllModals();
    }

    private function closeAllModals(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->editingUserId = null;
        $this->deletingUserId = null;
    }

    public function delete(): void
    {
        if (! $this->deletingUserId) {
            return;
        }

        if ((int) Auth::id() === $this->deletingUserId) {
            $this->addError('delete', 'No puedes eliminar tu propio usuario.');

            return;
        }

        $user = $this->usersQuery()->whereKey($this->deletingUserId)->first();
        abort_if(! $user, 404);

        if ((int) $user->status === 0) {
            $this->closeDeleteModal();
            session()->flash('message', 'El usuario ya esta inactivo.');

            return;
        }

        $user->update(['status' => 0]);

        $this->closeDeleteModal();
        session()->flash('message', 'Usuario inactivado correctamente.');
    }

    public function activate(int $userId): void
    {
        $user = $this->usersQuery()->whereKey($userId)->first();
        abort_if(! $user, 404);

        $user->update(['status' => 1]);

        session()->flash('message', 'Usuario activado correctamente.');
    }

    public function render(): View
    {
        return view('livewire.users.index', [
            'users' => $this->usersQuery()->paginate(10),
            'roles' => $this->availableRoles(),
        ])
            ->layout('layouts.app')
            ->title('Usuarios');
    }

    private function resetForm(): void
    {
        $this->firstName = '';
        $this->lastName = '';
        $this->username = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'user';
    }

    private function fullName(): string
    {
        return trim($this->firstName.' '.$this->lastName);
    }

    private function availableRoles(): array
    {
        return Role::query()
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    private function usersQuery(): Builder
    {
        return User::query()
            ->with(['roles:id,name', 'person:id,first_name,last_name'])
            ->latest();
    }
}
