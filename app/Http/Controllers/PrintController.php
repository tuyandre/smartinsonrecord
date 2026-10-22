<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** One-page printouts opened from the list actions. */
class PrintController extends Controller
{
    private const ROUNDS = [
        1 => ['mating' => 'Mating_Time_1', 'heat' => 'Heat_Observation_Date_1', 'hours' => 'Hours_Heat_Observation_1', 'date' => 'Insemination_Date_1', 'bull' => 'Bull_Used_1', 'obs' => 'Observation1', 'vet' => 'Veternary_Name1', 'rcvd' => 'Veternary_RCVD_ID1', 'email' => 'Vaternary_Email1'],
        2 => ['mating' => 'Mating_time_2', 'heat' => 'Heat_Observation_Date2', 'hours' => 'Hours_Heat_Observation2', 'date' => 'Insemination_Date2', 'bull' => 'Bull_Used_2', 'obs' => 'Observation_2', 'vet' => 'Veternary_Name_2', 'rcvd' => 'Veternary_RCVD_ID_2', 'email' => 'Vaternary_Email_2'],
        3 => ['mating' => 'Mating_time_3', 'heat' => 'Heat_Observation_Date_3', 'hours' => 'Hours_Heat_Observation_3', 'date' => 'Insemination_Date_3', 'bull' => 'Bull_Used_3', 'obs' => 'Observation_3', 'vet' => 'Veternary_Name_3', 'rcvd' => 'Veternary_RCVD_ID_3', 'email' => 'Vaternary_Email_3'],
    ];

    public function insemination(string $eartagno): View
    {
        $record = DB::table('inseminated_cows')->where('Eartagno', $eartagno)->first();
        abort_unless($record, 404);

        $cow = DB::table('cows')->where('Eartagno', $eartagno)->first();

        $rounds = [];
        foreach (self::ROUNDS as $n => $columns) {
            if (trim((string) $record->{$columns['date']}) === '') {
                continue;
            }
            $rounds[] = ['no' => $n] + array_map(fn ($column) => trim((string) $record->$column) ?: '—', $columns);
        }

        return view('pages.print.insemination', [
            'cow' => $cow,
            'tag' => $eartagno,
            'previousCalving' => trim((string) $record->Previous_Calving_Date) ?: '—',
            'rounds' => $rounds,
            'back' => route('records.inseminations'),
        ]);
    }

    public function calf(string $mother, int $n): View
    {
        abort_unless(in_array($n, [1, 2, 3], true), 404);

        $gen = DB::table('generations')->where('Eartagno', $mother)->first();
        abort_unless($gen && (string) $gen->{"Calf_Eartagno_$n"} !== '0', 404);

        $tag = (string) $gen->{"Calf_Eartagno_$n"};
        $animal = DB::table('cows')->where('Eartagno', $tag)->first() ?? DB::table('bulls')->where('Eartagno', $tag)->first();

        return view('pages.print.calf', [
            'mother' => DB::table('cows')->where('Eartagno', $mother)->first(),
            'motherTag' => $mother,
            'calf' => [
                'no' => $n,
                'tag' => $tag,
                'sex' => $animal->Gender ?? '—',
                'name' => $animal->Name ?? '—',
                'sire' => trim((string) $gen->{"Breed_$n"}) ?: '—',
                'dob' => trim((string) $gen->{"Dob_$n"}) ?: '—',
                'weight' => $gen->{"Weight_at_Birth_$n"},
                'birth_times' => $gen->{"Birth_Times_$n"},
                'observation' => trim((string) $gen->{"Observation_$n"}) ?: '—',
                'disability' => trim((string) $gen->{"Disability_$n"}) ?: '—',
            ],
            'back' => route('records.calves'),
        ]);
    }
}
