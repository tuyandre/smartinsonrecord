<?php

namespace App\Http\Controllers;

use App\Models\InseminatedCow;
use App\Models\User;
use App\Services\BullsApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportingController extends Controller
{
    private const SEARCH_OPTIONS = [
        'eartag' => 'Ear Tag Number',
        'vet_name' => 'Veterinary Name',
        'owner' => 'Cow Owner',
        'ins_date' => 'Insemination Date',
        'district' => 'District',
        'sector' => 'Sector',
        'cow_name' => 'Cow Name',
        'semen' => 'Semen Type',
        'ins_status' => 'Insemination Status',
    ];

    private const NOT_FOUND_SWAL = [
        'vet_name' => 'Report Information for Veternary Provided Not Found',
        'eartag' => 'Report Information for provided Eartag Not Found',
        'owner' => 'Report Information for owner Provided Not Found',
        'ins_date' => 'Report Information for Date Provided was Not Found',
    ];

    private const INSEMINATION_COLUMNS = [
        'Eartagno', 'Mating_Time_1', 'Previous_Calving_Date', 'Heat_Observation_Date_1',
        'Hours_Heat_Observation_1', 'Insemination_Date_1', 'Bull_Used_1', 'Observation1',
        'Veternary_Name1', 'Veternary_RCVD_ID1', 'Vaternary_Email1', 'Mating_time_2',
        'Heat_Observation_Date2', 'Hours_Heat_Observation2',
    ];

    private const SEMEN_CODE_COLUMNS = ['Semen_code_1', 'semen_code_2', 'semen_code_3', 'semen_code_4', 'semen_code_5'];

    public function index(Request $request, BullsApi $bulls)
    {
        $semen = null;
        $semenSearched = false;

        if ($request->has('check_semen')) {
            $semen = $bulls->all();
            $semenSearched = true;
        } elseif ($request->has('search_semen')) {
            $needle = (string) $request->query('semen_pattern', '');
            $semen = array_values(array_filter($bulls->all(), function ($row) use ($needle) {
                foreach (self::SEMEN_CODE_COLUMNS as $column) {
                    if (isset($row[$column]) && (string) $row[$column] === $needle) {
                        return true;
                    }
                }

                return false;
            }));
            $semenSearched = true;
        }

        $report = null;
        $searchBy = (string) $request->query('searchBy', 'eartag');

        if ($request->has('search')) {
            $data = $request->validate([
                'searchBy' => ['required', 'in:'.implode(',', array_keys(self::SEARCH_OPTIONS))],
                'pattern' => ['required', 'string', 'max:255'],
            ]);
            $searchBy = $data['searchBy'];
            $report = $this->buildReport($searchBy, $data['pattern']);
            $report['notFound'] = null;

            if ($report['rows']->isEmpty()) {
                if (isset(self::NOT_FOUND_SWAL[$searchBy])) {
                    $request->session()->now('swal', [
                        'title' => 'Error!',
                        'text' => self::NOT_FOUND_SWAL[$searchBy],
                        'icon' => 'warning',
                    ]);
                } else {
                    $report['notFound'] = $searchBy === 'ins_status'
                        ? 'No information found for the given criteria.'
                        : 'No information found';
                }
            }
        }

        return view('pages.reporting.index', [
            'insemTotal' => InseminatedCow::count(),
            'vetTotal' => User::count(),
            'bullCount' => $bulls->count(),
            'bullNames' => $bulls->names(),
            'searchOptions' => self::SEARCH_OPTIONS,
            'searchBy' => $searchBy,
            'report' => $report,
            'semen' => $semen,
            'semenSearched' => $semenSearched,
            'now' => now()->format('Y-m-d h:i:s'),
            'today' => now()->format('Y-m-d'),
        ]);
    }

    private function buildReport(string $searchBy, string $pattern): array
    {
        $inseminationSelect = array_map(fn ($column) => 'inseminated_cows.'.$column, self::INSEMINATION_COLUMNS);

        return match ($searchBy) {
            'eartag' => ['type' => 'cow', 'rows' => DB::table('cows')->where('Eartagno', $pattern)->get()],
            'owner' => ['type' => 'cow', 'rows' => DB::table('cows')->where('Owner_names', $pattern)->get()],
            'cow_name' => ['type' => 'cow', 'rows' => DB::table('cows')->where('Name', $pattern)->get()],
            'vet_name' => ['type' => 'insemination', 'rows' => DB::table('inseminated_cows')
                ->where(fn ($q) => $q->where('Veternary_Name1', $pattern)
                    ->orWhere('Veternary_Name_2', $pattern)
                    ->orWhere('Veternary_Name_3', $pattern))
                ->get(self::INSEMINATION_COLUMNS)],
            'ins_date' => ['type' => 'insemination', 'rows' => DB::table('inseminated_cows')
                ->where(fn ($q) => $q->where('Insemination_Date_1', $pattern)
                    ->orWhere('Insemination_Date2', $pattern)
                    ->orWhere('Insemination_Date_3', $pattern))
                ->get(self::INSEMINATION_COLUMNS)],
            'semen' => ['type' => 'insemination', 'rows' => DB::table('inseminated_cows')
                ->where(fn ($q) => $q->where('Bull_Used_1', $pattern)
                    ->orWhere('Bull_Used_2', $pattern)
                    ->orWhere('Bull_Used_3', $pattern))
                ->get(self::INSEMINATION_COLUMNS)],
            'district', 'sector' => [
                'type' => $searchBy,
                'label' => $pattern,
                'rows' => DB::table('cows')
                    ->join('inseminated_cows', 'cows.Eartagno', '=', 'inseminated_cows.Eartagno')
                    ->where('cows.'.($searchBy === 'district' ? 'District' : 'Sector'), $pattern)
                    ->get($inseminationSelect),
            ],
            'ins_status' => ['type' => 'status', 'rows' => DB::table('pd')
                ->join('inseminated_cows', 'pd.Eartagno', '=', 'inseminated_cows.Eartagno')
                ->whereRaw('TRIM(LOWER(pd.Observation)) = ?', [$pattern])
                ->get([
                    'pd.Eartagno as ear_tag', 'pd.Observation as status',
                    'inseminated_cows.Previous_Calving_Date as previous_calving',
                    'inseminated_cows.Heat_Observation_Date_1 as heat_date',
                    'inseminated_cows.Insemination_Date_1 as insemination_date',
                    'inseminated_cows.Bull_Used_1 as bull',
                    'inseminated_cows.Veternary_Name1 as vet',
                ])],
        };
    }
}
