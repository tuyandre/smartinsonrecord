<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Ear tags may contain letters and digits, so every ear-tag column becomes text. */
return new class extends Migration
{
    private const FOREIGN_KEYS = [
        ['generations', 'generations_eartagno_foreign', 'inseminated_cows'],
        ['inseminated_cows', 'inseminated_cows_eartagno_foreign', 'cows'],
        ['pd', 'pd_eartagno_foreign', 'cows'],
    ];

    private function convert(string $type, string $cross): void
    {
        foreach (self::FOREIGN_KEYS as [$table, $name]) {
            Schema::table($table, fn ($t) => $t->dropForeign($name));
        }

        DB::statement("ALTER TABLE cows MODIFY Eartagno $type NOT NULL");
        DB::statement("ALTER TABLE inseminated_cows MODIFY Eartagno $type NOT NULL");
        DB::statement("ALTER TABLE pd MODIFY Eartagno $type NOT NULL");
        DB::statement("ALTER TABLE generations MODIFY Eartagno $type NOT NULL");
        foreach ([1, 2, 3] as $n) {
            DB::statement("ALTER TABLE generations MODIFY Calf_Eartagno_$n $type NOT NULL");
        }
        DB::statement("ALTER TABLE newborn MODIFY New_born_Tagno $type NOT NULL");
        DB::statement("ALTER TABLE newborn MODIFY Mother_Eartag $type NOT NULL");

        DB::statement("ALTER TABLE cows MODIFY Cross_breed1 $cross NOT NULL, MODIFY Cross_breed2 $cross NOT NULL");

        foreach (self::FOREIGN_KEYS as [$table, $name, $references]) {
            Schema::table($table, fn ($t) => $t->foreign('Eartagno', $name)->references('Eartagno')->on($references));
        }
    }

    public function up(): void
    {
        $this->convert('VARCHAR(30)', 'VARCHAR(150)');
    }

    public function down(): void
    {
        $this->convert('INT', 'VARCHAR(20)');
    }
};
