<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'users.manage',
            'projects.view',
            'projects.create',
            'projects.update',
            'projects.delete',
            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.delete',
            'tasks.complete',
            'portal.home.view',
            'portal.ai.view',
            'portal.forms.view',
            'portal.rooms.view',
            'portal.schedules.view',
            'portal.stats.view',
            'portal.help.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $adminRole = Role::findOrCreate('admin', 'web');
        $userRole = Role::findOrCreate('user', 'web');

        $adminRole->syncPermissions(Permission::all());

        $userRole->syncPermissions([
            'portal.home.view',
            'portal.ai.view',
            'portal.forms.view',
            'portal.rooms.view',
            'portal.schedules.view',
            'portal.stats.view',
            'portal.help.view',
        ]);

        User::query()
            ->whereDoesntHave('roles')
            ->each(fn (User $user) => $user->assignRole('user'));
    }
}
