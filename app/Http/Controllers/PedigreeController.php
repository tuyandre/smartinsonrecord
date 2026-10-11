<?php

namespace App\Http\Controllers;

use App\Models\InseminatedCow;
use App\Models\User;
use App\Services\BullsApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PedigreeController extends Controller
{
    public function index(Request $request, BullsApi $bulls): View
    {
        $searched = $request->filled('eartagno');
        $tree = [];

        if ($searched) {
            $tree = DB::table('generations')
                ->select('Eartagno as Mother', 'Calf_Eartagno_1 as Calf_1', 'Calf_Eartagno_2 as Calf_2', 'Calf_Eartagno_3 as Calf_3')
                ->where(function ($query) {
                    $query->whereNotNull('Calf_Eartagno_1')
                        ->orWhereNotNull('Calf_Eartagno_2')
                        ->orWhereNotNull('Calf_Eartagno_3');
                })
                ->where('Eartagno', 'like', '%'.$request->input('eartagno').'%')
                ->get()
                ->all();
        }

        $counts = array_fill_keys(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], 0);

        $rows = DB::table('inseminated_cows')
            ->select('Insemination_Date_1', 'Insemination_Date2', 'Insemination_Date_3')
            ->get();

        foreach ($rows as $row) {
            foreach ([$row->Insemination_Date_1, $row->Insemination_Date2, $row->Insemination_Date_3] as $date) {
                if (! empty($date) && ($time = strtotime($date)) !== false) {
                    $counts[date('M', $time)]++;
                }
            }
        }

        return view('pages.pedigree.index', [
            'searched' => $searched,
            'treeData' => $tree,
            'months' => array_keys($counts),
            'counts' => array_values($counts),
            'totalInseminated' => InseminatedCow::count(),
            'totalVets' => User::count(),
            'bullCount' => $bulls->count(),
            'bullNames' => implode(', ', $bulls->names()),
        ]);
    }
}
