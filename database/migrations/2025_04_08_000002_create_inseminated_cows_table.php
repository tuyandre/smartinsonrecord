<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inseminated_cows', function (Blueprint $table) {
            $table->integer('Eartagno')->index();
            $table->foreign('Eartagno')->references('Eartagno')->on('cows');

            $table->integer('Mating_Time_1');
            $table->string('Previous_Calving_Date', 20);
            $table->string('Heat_Observation_Date_1', 10);
            $table->string('Hours_Heat_Observation_1', 8);
            $table->string('Insemination_Date_1', 20);
            $table->string('Bull_Used_1', 30);
            $table->string('Observation1', 70);
            $table->string('Veternary_Name1', 30);
            $table->integer('Veternary_RCVD_ID1');
            $table->string('Vaternary_Email1', 30);

            $table->integer('Mating_time_2');
            $table->string('Heat_Observation_Date2', 20);
            $table->string('Hours_Heat_Observation2', 10);
            $table->string('Insemination_Date2', 20);
            $table->string('Bull_Used_2', 20);
            $table->string('Observation_2', 70);
            $table->string('Veternary_Name_2', 20);
            $table->string('Veternary_RCVD_ID_2', 10);
            $table->string('Vaternary_Email_2', 20);

            $table->integer('Mating_time_3');
            $table->string('Heat_Observation_Date_3', 20);
            $table->string('Hours_Heat_Observation_3', 20);
            $table->string('Insemination_Date_3', 15);
            $table->string('Bull_Used_3', 20);
            $table->string('Observation_3', 70);
            $table->string('Veternary_Name_3', 20);
            $table->string('Veternary_RCVD_ID_3', 10);
            $table->string('Vaternary_Email_3', 20);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inseminated_cows');
    }
};
