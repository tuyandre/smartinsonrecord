<?php

use App\Models\Permission;
use App\Models\Role;
use App\Support\PermissionCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulls', function (Blueprint $table) {
            $table->string('Eartagno', 30)->primary();
            $table->string('Name', 20);
            $table->string('Gender', 10);
            $table->string('Breed', 25);
            $table->string('Cross_breed1', 150);
            $table->string('Cross_breed2', 150);
            $table->integer('Breed_Percentage');
            $table->string('Dob', 25);
            $table->integer('Number_Calving')->default(0);
            $table->string('Owner_names', 25);
            $table->bigInteger('National_Id');
            $table->bigInteger('Phone');
            $table->string('District', 25);
            $table->string('Sector', 25);
            $table->string('Cell', 25);
            $table->string('Village', 25);
        });

        // Register the new bulls.* permissions and give them to the roles that ship with full record access.
        PermissionCatalog::sync();
        $ids = Permission::where('name', 'like', 'bulls.%')->pluck('id');
        Role::whereIn('name', ['admin', PermissionCatalog::DEFAULT_ROLE])->get()
            ->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($ids));
    }

    public function down(): void
    {
        Schema::dropIfExists('bulls');
        Permission::where('name', 'like', 'bulls.%')->delete();
    }
};
