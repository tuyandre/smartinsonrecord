<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Creates the super admin once. Credentials come from .env
     * (SUPERADMIN_PASSWORD, SUPERADMIN_EMAIL, SUPERADMIN_USERNAME); re-running never resets an existing password.
     */
    public function run(): void
    {
        $password = env('SUPERADMIN_PASSWORD');

        if (! $password) {
            $this->command?->error('Set SUPERADMIN_PASSWORD in .env before seeding the super admin.');

            return;
        }

        $user = User::firstOrNew(['rcvd_id' => 'superadmin']);
        $created = ! $user->exists;

        $user->fill([
            'name' => 'Super Admin',
            'username' => env('SUPERADMIN_USERNAME', 'superadmin'),
            'email' => env('SUPERADMIN_EMAIL', 'superadmin@insemination.test'),
            'role_id' => Role::where('name', PermissionCatalog::SUPERADMIN)->value('id'),
        ]);

        if ($created) {
            $user->password = $password;
        }

        $user->save();

        $this->command?->info($created ? 'Super admin created.' : 'Super admin already existed; password left unchanged.');
    }
}
