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
        // One record per registered bull: the catalogue numbers shown on the Semen Info report.
        Schema::create('semens', function (Blueprint $table) {
            $table->id();
            $table->string('bull_eartag', 30)->unique();
            $table->foreign('bull_eartag')->references('Eartagno')->on('bulls')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('international_code', 30)->nullable();
            $table->string('aic_no', 30)->nullable();
            $table->string('bull_no', 30)->nullable();
            $table->timestamps();
        });

        // Each frozen batch (up to five per bull) with its semen code.
        Schema::create('semen_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semen_id')->constrained('semens')->cascadeOnDelete();
            $table->string('registration_no', 30);
            $table->date('freezing_date');
            $table->string('semen_code', 20)->unique();
            $table->timestamps();
        });

        PermissionCatalog::sync();
        $ids = Permission::where('name', 'like', 'semen.%')->pluck('id');
        Role::whereIn('name', ['admin', PermissionCatalog::DEFAULT_ROLE])->get()
            ->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($ids));
    }

    public function down(): void
    {
        Schema::dropIfExists('semen_lots');
        Schema::dropIfExists('semens');
        Permission::where('name', 'like', 'semen.%')->delete();
    }
};
