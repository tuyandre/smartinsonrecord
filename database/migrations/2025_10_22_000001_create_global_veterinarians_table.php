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
        // The national (RCVD) register of veterinarians, imported from the council's spreadsheet.
        Schema::create('global_veterinarians', function (Blueprint $table) {
            $table->id();
            $table->string('rcvd_id', 30)->unique();
            $table->string('national_id', 20)->nullable()->unique();
            $table->string('names', 120);
            $table->date('date_of_birth')->nullable();
            $table->string('district', 60)->nullable();
            $table->string('sector', 60)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        PermissionCatalog::sync();
        $ids = Permission::where('name', 'like', 'veterinarians.%')->pluck('id');
        Role::where('name', 'admin')->get()->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($ids));
    }

    public function down(): void
    {
        Schema::dropIfExists('global_veterinarians');
        Permission::where('name', 'like', 'veterinarians.%')->delete();
    }
};
