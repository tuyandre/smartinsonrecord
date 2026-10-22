<?php

namespace App\Http\Controllers;

use App\Models\Cow;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CowController extends Controller
{
    public function show(string $eartagno): View
    {
        $cow = Cow::findOrFail($eartagno);
        $insemination = DB::table('inseminated_cows')->where('Eartagno', $eartagno)->first();
        $generation = DB::table('generations')->where('Eartagno', $eartagno)->first();

        $calves = [];
        foreach ([1, 2, 3] as $n) {
            $tag = $generation->{"Calf_Eartagno_$n"} ?? '0';
            if ($generation && $tag !== '' && $tag !== '0') {
                $calves[] = [
                    'tag' => $tag,
                    'breed' => $generation->{"Breed_$n"},
                    'dob' => $generation->{"Dob_$n"},
                    'weight' => $generation->{"Weight_at_Birth_$n"},
                    'observation' => $generation->{"Observation_$n"},
                    'disability' => $generation->{"Disability_$n"},
                ];
            }
        }

        $rounds = [];
        foreach ([1, 2, 3] as $n) {
            $dateKey = [1 => 'Insemination_Date_1', 2 => 'Insemination_Date2', 3 => 'Insemination_Date_3'][$n];
            $bullKey = "Bull_Used_$n";
            $obsKey = [1 => 'Observation1', 2 => 'Observation_2', 3 => 'Observation_3'][$n];
            $vetKey = [1 => 'Veternary_Name1', 2 => 'Veternary_Name_2', 3 => 'Veternary_Name_3'][$n];
            if ($insemination && trim((string) $insemination->$dateKey) !== '') {
                $rounds[] = [
                    'no' => $n,
                    'date' => $insemination->$dateKey,
                    'bull' => $insemination->$bullKey,
                    'observation' => $insemination->$obsKey,
                    'vet' => $insemination->$vetKey,
                ];
            }
        }

        return view('pages.cows.show', compact('cow', 'calves', 'rounds'));
    }

    public function edit(string $eartagno): View
    {
        return view('pages.cows.edit', [
            'cow' => Cow::findOrFail($eartagno),
            'breeds' => RegistrationController::BREEDS,
        ]);
    }
}
