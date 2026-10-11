<?php

namespace Database\Seeders;

use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        PermissionCatalog::sync();

        $this->call(SuperAdminSeeder::class);
    }
}
