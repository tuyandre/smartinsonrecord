<?php

namespace App\Http\Controllers;

use App\Models\InseminatedCow;
use App\Models\Pd;
use App\Models\User;
use App\Services\BullsApi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PdController extends Controller
{
    private const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    public function index(BullsApi $bulls): View
    {
        $counts = array_fill_keys(self::MONTHS, 0);

        DB::table('inseminated_cows')
            ->select(['Insemination_Date_1', 'Insemination_Date2', 'Insemination_Date_3'])
            ->get()
            ->each(function ($row) use (&$counts) {
                foreach ([$row->Insemination_Date_1, $row->Insemination_Date2, $row->Insemination_Date_3] as $date) {
                    if (empty($date) || ($time = strtotime($date)) === false) {
                        continue;
                    }

                    $counts[date('M', $time)]++;
                }
            });

        return view('pages.pd.index', [
            'totalInseminated' => InseminatedCow::count(),
            'totalVets' => User::count(),
            'bullCount' => $bulls->count(),
            'bullNames' => $bulls->names(),
            'months' => array_keys($counts),
            'counts' => array_values($counts),
            'candidate' => session('pd_candidate'),
        ]);
    }

    public function lookup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pd_eartagno' => ['required', 'integer'],
        ], [
            'pd_eartagno.required' => 'Provide a cow ear tag number.',
            'pd_eartagno.integer' => 'Provide a valid cow ear tag number.',
        ]);

        $latest = $this->latestInseminationDate((int) $data['pd_eartagno']);

        if ($latest === null) {
            return redirect()->route('pd.index')->with('swal', [
                'title' => 'Error Occurred!',
                'text' => 'No records found for the given Ear Tag Number.',
                'icon' => 'warning',
            ]);
        }

        if ($latest < date('Y-m-d', strtotime('-9 months'))) {
            return redirect()->route('pd.index')->with('swal', [
                'title' => 'No Recent Data!',
                'text' => 'No insemination within the last 9 months.',
                'icon' => 'warning',
            ]);
        }

        return redirect()->route('pd.index')->with('pd_candidate', [
            'eartagno' => (int) $data['pd_eartagno'],
            'insemination_date' => $latest,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'eartagno' => ['required', 'integer'],
            'status' => ['required', 'in:Conception,Not Pregnant'],
        ]);

        $latest = $this->latestInseminationDate((int) $data['eartagno']);

        if ($latest === null || $latest < date('Y-m-d', strtotime('-9 months'))) {
            return redirect()->route('pd.index')->with('swal', [
                'title' => 'Error Occurred!',
                'text' => 'Error while updating status.',
                'icon' => 'warning',
            ]);
        }

        Pd::create([
            'Eartagno' => (int) $data['eartagno'],
            'Observation' => $data['status'],
        ]);

        return redirect()->route('pd.index')->with('swal', [
            'title' => 'Success!',
            'text' => 'Status Updated Successfully.',
            'icon' => 'success',
        ]);
    }

    private function latestInseminationDate(int $eartagno): ?string
    {
        $row = DB::table('inseminated_cows')
            ->where('Eartagno', $eartagno)
            ->first(['Insemination_Date_1', 'Insemination_Date2', 'Insemination_Date_3']);

        if (! $row) {
            return null;
        }

        $dates = array_filter([$row->Insemination_Date_1, $row->Insemination_Date2, $row->Insemination_Date_3]);

        return $dates === [] ? null : max($dates);
    }
}
