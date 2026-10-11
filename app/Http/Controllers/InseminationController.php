<?php

namespace App\Http\Controllers;

use App\Models\InseminatedCow;
use App\Models\User;
use App\Services\BullsApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InseminationController extends Controller
{
    private const OBSERVATIONS = [
        'Easy Cervix Penetration',
        'Difficult Cervix Penetration',
        'Macus Follow',
        'Absence of Macus',
        'Cervix opened',
        'Natural Heat',
        'Induction Heat',
        'Previous Abortion',
        'Prulent Macus',
    ];

    public function index(Request $request, BullsApi $bulls): View
    {
        $pattern = trim((string) $request->query('pattern', ''));
        $searchResults = null;

        if ($pattern !== '') {
            $searchResults = DB::table('cows as c')
                ->leftJoin('generations as g', 'c.Eartagno', '=', 'g.Eartagno')
                ->where('c.Eartagno', 'like', '%'.$pattern.'%')
                ->select(['c.Name', 'c.Eartagno', 'c.Gender', 'c.Breed', 'c.Cross_breed1', 'c.Cross_breed2',
                    'c.Breed_Percentage', 'c.Dob', 'c.Number_Calving', 'c.Owner_names', 'c.National_Id',
                    'c.Phone', 'c.District', 'c.Sector', 'c.Cell', 'c.Village',
                    'g.Calf_Eartagno_1', 'g.Breed_1', 'g.Dob_1', 'g.Weight_at_Birth_1', 'g.Birth_Times_1',
                    'g.Observation_1', 'g.Disability_1', 'g.Calf_Eartagno_2', 'g.Breed_2', 'g.Dob_2',
                    'g.Weight_at_Birth_2', 'g.Birth_Times_2', 'g.Observation_2', 'g.Disability_2',
                    'g.Calf_Eartagno_3', 'g.Breed_3', 'g.Dob_3', 'g.Weight_at_Birth_3', 'g.Birth_Times_3',
                    'g.Observation_3', 'g.Disability_3'])
                ->get();
        }

        $bullList = $bulls->all();

        return view('pages.insemination.index', [
            'totalInseminated' => InseminatedCow::count(),
            'totalVets' => User::count(),
            'bullCount' => count($bullList),
            'bullNames' => $bulls->names(),
            'bulls' => $bullList,
            'observations' => self::OBSERVATIONS,
            'searchResults' => $searchResults,
            'pattern' => $pattern,
            'report' => session('report'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'eartag' => ['required', 'integer', 'exists:cows,Eartagno'],
            'pre_calv' => ['nullable', 'date'],
            'heat_obs' => ['required', 'date'],
            'hours_heat' => ['required', 'string', 'max:10'],
            'semen' => ['required', 'string', 'max:30'],
            'mating' => ['required', 'in:1,2,3'],
            'obsr' => ['nullable', 'array'],
            'obsr.*' => ['string', 'in:'.implode(',', self::OBSERVATIONS)],
        ], [
            'eartag.exists' => 'Cow Eartagno Information Not Found',
        ]);

        $vet = $request->user();
        $eartag = (int) $data['eartag'];
        $mating = (int) $data['mating'];
        $observation = implode(', ', $data['obsr'] ?? []);
        $insemDate = now()->format('Y-m-d H:i:s');
        $vetName = (string) $vet->name;
        $vetRcvd = (string) $vet->rcvd_id;
        $vetEmail = (string) $vet->email;
        $semen = $data['semen'];

        if ($mating === 1) {
            DB::table('inseminated_cows')->insert([
                'Eartagno' => $eartag,
                'Mating_Time_1' => 1,
                'Previous_Calving_Date' => (string) ($data['pre_calv'] ?? ''),
                'Heat_Observation_Date_1' => $data['heat_obs'],
                'Hours_Heat_Observation_1' => $data['hours_heat'],
                'Insemination_Date_1' => $insemDate,
                'Bull_Used_1' => Str::limit($semen, 30, ''),
                'Observation1' => Str::limit($observation, 70, ''),
                'Veternary_Name1' => Str::limit($vetName, 30, ''),
                'Veternary_RCVD_ID1' => (int) $vetRcvd,
                'Vaternary_Email1' => Str::limit($vetEmail, 30, ''),
                'Mating_time_2' => 0,
                'Heat_Observation_Date2' => '',
                'Hours_Heat_Observation2' => '',
                'Insemination_Date2' => '',
                'Bull_Used_2' => '',
                'Observation_2' => '',
                'Veternary_Name_2' => '',
                'Veternary_RCVD_ID_2' => '',
                'Vaternary_Email_2' => '',
                'Mating_time_3' => 0,
                'Heat_Observation_Date_3' => '',
                'Hours_Heat_Observation_3' => '',
                'Insemination_Date_3' => '',
                'Bull_Used_3' => '',
                'Observation_3' => '',
                'Veternary_Name_3' => '',
                'Veternary_RCVD_ID_3' => '',
                'Vaternary_Email_3' => '',
            ]);
            $saved = true;
        } else {
            $suffix = $mating === 2 ? ['Heat_Observation_Date2', 'Hours_Heat_Observation2', 'Insemination_Date2', 'Bull_Used_2', 'Observation_2', 'Veternary_Name_2', 'Veternary_RCVD_ID_2', 'Vaternary_Email_2', 'Mating_time_2']
                : ['Heat_Observation_Date_3', 'Hours_Heat_Observation_3', 'Insemination_Date_3', 'Bull_Used_3', 'Observation_3', 'Veternary_Name_3', 'Veternary_RCVD_ID_3', 'Vaternary_Email_3', 'Mating_time_3'];

            $saved = DB::table('inseminated_cows')->where('Eartagno', $eartag)->update([
                $suffix[0] => $data['heat_obs'],
                $suffix[1] => $data['hours_heat'],
                $suffix[2] => $insemDate,
                $suffix[3] => Str::limit($semen, 20, ''),
                $suffix[4] => Str::limit($observation, 70, ''),
                $suffix[5] => Str::limit($vetName, 20, ''),
                $suffix[6] => Str::limit($vetRcvd, 10, ''),
                $suffix[7] => Str::limit($vetEmail, 20, ''),
                $suffix[8] => $mating,
            ]) > 0;
        }

        if (! $saved) {
            return back()->withInput()->with('swal', [
                'title' => 'Error!',
                'text' => 'Registration failed due to an error! Record AI service time 1 for this cow first.',
                'icon' => 'warning',
            ]);
        }

        return redirect()->route('insemination.index')->with([
            'swal' => [
                'title' => 'Success!',
                'text' => 'Cow information successfully saved',
                'icon' => 'success',
            ],
            'report' => [
                'eartag' => $eartag,
                'heat_obs' => $data['heat_obs'],
                'heat_hours' => $data['hours_heat'],
                'insem_date' => $insemDate,
                'bull_used' => $semen,
                'mating' => $mating,
                'observation' => $observation,
                'vet_name' => $vetName,
                'vet_rcvd' => $vetRcvd,
                'vet_email' => $vetEmail,
            ],
        ]);
    }

    public function lastMating(Request $request): JsonResponse
    {
        $empty = ['breed' => '', 'mating_time' => 0];
        $tag = $request->query('mother_tag');

        if (! is_numeric($tag)) {
            return response()->json($empty);
        }

        $row = DB::table('inseminated_cows')
            ->where('Eartagno', (int) $tag)
            ->select(['Bull_Used_1', 'Mating_Time_1', 'Bull_Used_2', 'Mating_time_2', 'Bull_Used_3', 'Mating_time_3'])
            ->first();

        if (! $row) {
            return response()->json($empty);
        }

        foreach ([3, 2, 1] as $n) {
            $time = $n === 1 ? $row->Mating_Time_1 : $row->{'Mating_time_'.$n};
            if ($time > 0) {
                return response()->json(['breed' => $row->{'Bull_Used_'.$n}, 'mating_time' => $time]);
            }
        }

        return response()->json($empty);
    }
}
