<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cows', function (Blueprint $table) {
            $table->integer('Eartagno')->primary();
            $table->string('Name', 20);
            $table->string('Gender', 10);
            $table->string('Breed', 25);
            $table->string('Cross_breed1', 20);
            $table->string('Cross_breed2', 20);
            $table->integer('Breed_Percentage');
            $table->string('Dob', 25);
            $table->integer('Number_Calving');
            $table->string('Owner_names', 25);
            $table->bigInteger('National_Id');
            $table->bigInteger('Phone');
            $table->string('District', 25);
            $table->string('Sector', 25);
            $table->string('Cell', 25);
            $table->string('Village', 25);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cows');
    }
};
