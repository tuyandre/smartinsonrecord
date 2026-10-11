<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pd', function (Blueprint $table) {
            $table->increments('pd_id');
            $table->integer('Eartagno')->index();
            $table->foreign('Eartagno')->references('Eartagno')->on('cows');
            $table->string('Observation', 15);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd');
    }
};
