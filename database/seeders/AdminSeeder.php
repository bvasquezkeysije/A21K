<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrNew(
            ['email' => 'admin@a21k.com'],
        );

        $person = $admin->person;

        if ($person) {
            $person->update([
                'first_name' => 'Admin',
                'last_name' => 'A21K',
            ]);
        } else {
            $person = Person::create([
                'first_name' => 'Admin',
                'last_name' => 'A21K',
            ]);
        }

        $admin->name = 'Admin A21K';
        $admin->username = 'admin';
        $admin->person_id = $person->id;
        $admin->password = Hash::make('123');
        $admin->email_verified_at = now();
        $admin->status = 1;
        $admin->save();

        $admin->syncRoles(['admin']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
