<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generations', function (Blueprint $table) {
            $table->integer('Eartagno')->index();
            $table->foreign('Eartagno')->references('Eartagno')->on('inseminated_cows');

            foreach ([1, 2, 3] as $n) {
                $table->integer("Calf_Eartagno_$n");
                $table->string("Breed_$n", 20);
                $table->string("Dob_$n", 20);
                $table->integer("Weight_at_Birth_$n");
                $table->integer("Birth_Times_$n");
                $table->string("Observation_$n", 20);
                $table->string("Disability_$n", 70);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generations');
    }
};
