<?php

namespace Database\Seeders;

use App\Http\Controllers\RegistrationController;
use App\Models\Bull;
use App\Models\Semen;
use App\Models\SemenLot;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Imports the government bull and semen catalogue (BULLS_API_URL) into the bulls, semens and
 * semen_lots tables. Safe to run again: bulls and their semen are matched on the ear tag and updated.
 *
 *   php artisan db:seed --class=GovernmentBullsSeeder
 */
class GovernmentBullsSeeder extends Seeder
{
    public function run(): void
    {
        $response = Http::timeout(15)->acceptJson()->get(config('services.bulls.url'));

        if (! $response->successful() || ! is_array($response->json())) {
            $this->command?->error('Could not read the bulls catalogue from '.config('services.bulls.url'));

            return;
        }

        $imported = 0;
        $skippedCodes = [];

        DB::transaction(function () use ($response, &$imported, &$skippedCodes) {
            foreach ($response->json() as $row) {
                $tag = trim((string) ($row['Eartag'] ?? ''));
                if (! preg_match('/^[A-Za-z0-9]+$/', $tag)) {
                    continue;
                }

                $bull = Bull::updateOrCreate(['Eartagno' => $tag], [
                    'Name' => mb_substr(trim((string) ($row['Name'] ?? $tag)), 0, 20),
                    'Gender' => 'Male',
                    'Breed' => $this->breed((string) ($row['Breed'] ?? '')),
                    'Cross_breed1' => 'N/A',
                    'Cross_breed2' => 'N/A',
                    'Breed_Percentage' => 100,
                    'Dob' => (string) ($row['Dob'] ?? ''),
                    'Number_Calving' => 0,
                    // The catalogue has no owner or location, so these are placeholders to edit later.
                    'Owner_names' => 'RAB (Government)',
                    'National_Id' => 0,
                    'Phone' => 0,
                    'District' => 'Huye',
                    'Sector' => 'Ngoma',
                    'Cell' => '-',
                    'Village' => '-',
                ]);

                $semen = Semen::updateOrCreate(['bull_eartag' => $bull->Eartagno], [
                    'international_code' => $this->text($row['international_code'] ?? null),
                    'aic_no' => $this->text($row['aic_no'] ?? null),
                    'bull_no' => $this->text($row['bull_no'] ?? null),
                ]);

                $semen->lots()->delete();
                foreach ([1, 2, 3, 4, 5] as $n) {
                    // The catalogue spells its keys inconsistently.
                    $code = $this->text($row[$n === 1 ? 'Semen_code_1' : "semen_code_$n"] ?? null);
                    $registration = $this->text($row[$n === 3 ? 'registration__code_3' : "registration_no_$n"] ?? null) ?? (string) $n;
                    $frozen = $this->date($row["freezing_date_$n"] ?? null);

                    if ($code === null || $frozen === null) {
                        continue;
                    }
                    if (SemenLot::where('semen_code', $code)->exists()) {
                        $skippedCodes[] = "$tag:$code";

                        continue;
                    }

                    $semen->lots()->create(['registration_no' => $registration, 'freezing_date' => $frozen, 'semen_code' => $code]);
                }

                $imported++;
            }
        });

        $this->command?->info("Imported {$imported} bulls with their semen batches.");
        if ($skippedCodes) {
            $this->command?->warn('Skipped semen codes already used by another bull: '.implode(', ', $skippedCodes));
        }
    }

    /** Matches the catalogue's free-text breed to the app's breed list ("brown suisss" => "Browns Swiss"). */
    private function breed(string $raw): string
    {
        $raw = trim($raw);
        $best = null;
        $bestScore = 0;

        foreach (RegistrationController::BREEDS as $breed) {
            similar_text(strtolower($raw), strtolower($breed), $percent);
            if ($percent > $bestScore) {
                [$best, $bestScore] = [$breed, $percent];
            }
        }

        return $bestScore >= 75 ? $best : mb_substr($raw, 0, 25);
    }

    private function text(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /** "13/01/2025" => "2025-01-13" */
    private function date(mixed $value): ?string
    {
        try {
            return Carbon::createFromFormat('d/m/Y', trim((string) $value))->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
