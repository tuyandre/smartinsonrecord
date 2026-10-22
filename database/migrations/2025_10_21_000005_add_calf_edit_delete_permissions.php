<?php

use App\Models\Permission;
use App\Models\Role;
use App\Support\PermissionCatalog;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const NAMES = ['calves.edit', 'calves.delete'];

    public function up(): void
    {
        PermissionCatalog::sync();
        $ids = Permission::whereIn('name', self::NAMES)->pluck('id');
        Role::whereIn('name', ['admin', PermissionCatalog::DEFAULT_ROLE])->get()
            ->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($ids));
    }

    public function down(): void
    {
        Permission::whereIn('name', self::NAMES)->delete();
    }
};
