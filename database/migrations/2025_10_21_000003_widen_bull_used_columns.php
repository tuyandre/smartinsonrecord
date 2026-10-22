<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Bull_Used now holds "<bull tag> (<semen code>)", which no longer fits the old 20/30 character columns. */
return new class extends Migration
{
    public function up(): void
    {
        foreach ([1, 2, 3] as $n) {
            DB::statement("ALTER TABLE inseminated_cows MODIFY Bull_Used_$n VARCHAR(60) NOT NULL");
        }
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE inseminated_cows MODIFY Bull_Used_1 VARCHAR(30) NOT NULL');
        DB::statement('ALTER TABLE inseminated_cows MODIFY Bull_Used_2 VARCHAR(20) NOT NULL');
        DB::statement('ALTER TABLE inseminated_cows MODIFY Bull_Used_3 VARCHAR(20) NOT NULL');
    }
};
