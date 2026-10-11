<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newborn', function (Blueprint $table) {
            $table->integer('New_born_Tagno')->primary();
            $table->integer('Mother_Eartag');
            $table->string('Semen', 20);
            $table->date('DOB');
            $table->string('Breed', 20);
            $table->string('Disabilty', 70);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newborn');
    }
};
