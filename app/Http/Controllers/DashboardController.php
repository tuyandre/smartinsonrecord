<?php

namespace App\Http\Controllers;

use App\Models\InseminatedCow;
use App\Models\User;
use App\Services\BullsApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request, BullsApi $bulls)
    {
        $rows = InseminatedCow::query()->get([
            'Observation1', 'Observation_2', 'Observation_3',
            'Insemination_Date_1', 'Insemination_Date2', 'Insemination_Date_3',
        ]);

        $observations = $rows
            ->flatMap(fn ($row) => [$row->Observation1, $row->Observation_2, $row->Observation_3])
            ->filter()
            ->countBy();

        $months = array_fill_keys(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], 0);
        $rows
            ->flatMap(fn ($row) => [$row->Insemination_Date_1, $row->Insemination_Date2, $row->Insemination_Date_3])
            ->filter()
            ->each(function ($date) use (&$months) {
                if ($time = strtotime($date)) {
                    $months[date('M', $time)]++;
                }
            });

        $pattern = trim((string) $request->query('pattern', ''));
        $searchResults = null;
        if ($pattern !== '') {
            $searchResults = DB::table('cows as c')
                ->leftJoin('generations as g', 'c.Eartagno', '=', 'g.Eartagno')
                ->where(fn ($q) => $q->where('c.Eartagno', 'like', "%{$pattern}%")->orWhere('c.National_Id', 'like', "%{$pattern}%"))
                ->get();
        }

        return view('pages.dashboard', [
            'insemTotal' => InseminatedCow::count(),
            'vetTotal' => User::count(),
            'bullCount' => $bulls->count(),
            'bullNames' => $bulls->names(),
            'observationLabels' => $observations->keys(),
            'observationCounts' => $observations->values(),
            'months' => array_keys($months),
            'monthCounts' => array_values($months),
            'searchResults' => $searchResults,
            'latest' => DB::table('inseminated_cows as i')
                ->leftJoin('cows as c', 'c.Eartagno', '=', 'i.Eartagno')
                ->orderByDesc('i.Insemination_Date_1')
                ->limit(5)
                ->get(['i.Eartagno', 'i.Bull_Used_1', 'i.Insemination_Date_1', 'i.Veternary_Name1', 'c.Name as cow_name']),
        ]);
    }
}
