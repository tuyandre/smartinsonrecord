<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;

/**
 * The "Veterinarian" role every self-registered veterinarian receives.
 * Safe to run again: the role is created once, and its permissions are only set when it is first created,
 * so changes made later on the Roles & Permissions page are kept.
 *
 *   php artisan db:seed --class=VeterinarianRoleSeeder
 */
class VeterinarianRoleSeeder extends Seeder
{
    public function run(): void
    {
        PermissionCatalog::sync(); // makes sure every permission exists

        $role = Role::firstOrCreate(
            ['name' => PermissionCatalog::DEFAULT_ROLE],
            ['label' => 'Veterinarian', 'description' => 'Records and reviews insemination data.', 'is_system' => true]
        );

        if ($role->label !== 'Veterinarian') {
            $role->update(['label' => 'Veterinarian']);
        }

        if ($role->wasRecentlyCreated || $role->permissions()->count() === 0) {
            $role->permissions()->sync(Permission::whereIn('name', PermissionCatalog::veterinarianPermissions())->pluck('id'));
        }

        $this->command?->info("Veterinarian role ready with {$role->permissions()->count()} permissions.");
    }
}
