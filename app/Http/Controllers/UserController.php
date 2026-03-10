<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function edit(Request $request, User $user): View
    {
        abort_unless($request->user()?->can('users.manage'), 403);

        return view('users.edit', [
            'user' => $user->load(['person', 'roles:id,name']),
            'roles' => $this->availableRoles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('users.manage'), 403);

        $availableRoles = $this->availableRoles();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:255', Rule::unique('users', 'username')],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:3'],
            'role' => ['required', Rule::in($availableRoles)],
        ]);

        $person = Person::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
        ]);

        $user = User::create([
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
            'person_id' => $person->id,
            'status' => 1,
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()
            ->route('users.index')
            ->with('message', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->can('users.manage'), 403);

        $availableRoles = $this->availableRoles();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:3'],
            'role' => ['required', Rule::in($availableRoles)],
        ]);

        $person = $user->person;

        if ($person) {
            $person->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
            ]);
        } else {
            $person = Person::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
            ]);
        }

        $attributes = [
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'username' => $validated['username'],
            'email' => $validated['email'],
            'person_id' => $person->id,
        ];

        if (! empty($validated['password'])) {
            $attributes['password'] = Hash::make($validated['password']);
        }

        $user->update($attributes);
        $user->syncRoles([$validated['role']]);

        return redirect()
            ->route('users.index')
            ->with('message', 'Usuario actualizado correctamente.');
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->can('users.manage'), 403);

        if ((int) $request->user()->id === (int) $user->id) {
            return back()->withErrors([
                'delete' => 'No puedes inactivar tu propio usuario.',
            ]);
        }

        if ((int) $user->status === 0) {
            return back()->with('message', 'El usuario ya esta inactivo.');
        }

        $user->update(['status' => 0]);

        return back()->with('message', 'Usuario inactivado correctamente.');
    }

    public function activate(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->can('users.manage'), 403);

        if ((int) $user->status === 1) {
            return back()->with('message', 'El usuario ya esta activo.');
        }

        $user->update(['status' => 1]);

        return back()->with('message', 'Usuario activado correctamente.');
    }

    private function availableRoles(): array
    {
        return Role::query()
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }
}
