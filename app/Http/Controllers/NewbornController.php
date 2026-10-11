<?php

namespace App\Http\Controllers;

use App\Models\InseminatedCow;
use App\Models\Newborn;
use App\Models\User;
use App\Services\BullsApi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NewbornController extends Controller
{
    public function index(BullsApi $bulls): View
    {
        $counts = array_fill_keys(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], 0);

        $rows = DB::table('inseminated_cows')
            ->select('Insemination_Date_1', 'Insemination_Date2', 'Insemination_Date_3')
            ->get();

        foreach ($rows as $row) {
            foreach ([$row->Insemination_Date_1, $row->Insemination_Date2, $row->Insemination_Date_3] as $date) {
                $time = $this->timestampOf($date);
                if ($time !== null) {
                    $counts[date('M', $time)]++;
                }
            }
        }

        return view('pages.newborn.index', [
            'totalInseminated' => InseminatedCow::count(),
            'totalVets' => User::count(),
            'bullCount' => $bulls->count(),
            'bullNames' => $bulls->names(),
            'months' => array_keys($counts),
            'counts' => array_values($counts),
            'report' => session('report'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tagno' => ['required', 'integer'],
            'M_tagno' => ['required', 'integer'],
            'breeds' => ['nullable', 'string', 'max:20'],
            'dob' => ['nullable', 'date'],
            'weights' => ['nullable', 'integer', 'min:0'],
            'calf_number' => ['required', 'in:1,2,3'],
            'birth_times' => ['nullable', 'integer', 'min:0'],
            'observation' => ['nullable', 'in:Normal,Disability'],
            'disability_desc' => ['nullable', 'string', 'max:70'],
        ], [
            'calf_number.required' => 'Invalid calf number!',
            'calf_number.in' => 'Invalid calf number!',
        ]);

        $n = (int) $data['calf_number'];
        $mother = (int) $data['M_tagno'];

        if (! DB::table('inseminated_cows')->where('Eartagno', $mother)->exists()) {
            return back()->withInput()->with('swal', [
                'title' => 'Error!',
                'text' => 'Mother tag number is not an inseminated cow!',
                'icon' => 'warning',
            ]);
        }

        $values = [
            "Calf_Eartagno_$n" => (int) $data['tagno'],
            "Breed_$n" => $data['breeds'] ?? '',
            "Dob_$n" => $data['dob'] ?? '',
            "Weight_at_Birth_$n" => (int) ($data['weights'] ?? 0),
            "Birth_Times_$n" => (int) ($data['birth_times'] ?? 0),
            "Observation_$n" => $data['observation'] ?? '',
            "Disability_$n" => $data['disability_desc'] ?? '',
        ];

        $duplicate = false;

        DB::transaction(function () use ($mother, $n, $values, &$duplicate) {
            $existing = DB::table('generations')->where('Eartagno', $mother)->lockForUpdate()->get();

            if ($existing->contains(fn ($row) => (int) $row->{"Birth_Times_$n"} !== 0)) {
                $duplicate = true;

                return;
            }

            if ($existing->isEmpty()) {
                $blank = [];
                foreach ([1, 2, 3] as $i) {
                    $blank += [
                        "Calf_Eartagno_$i" => 0, "Breed_$i" => '', "Dob_$i" => '', "Weight_at_Birth_$i" => 0,
                        "Birth_Times_$i" => 0, "Observation_$i" => '', "Disability_$i" => '',
                    ];
                }
                DB::table('generations')->insert(['Eartagno' => $mother] + array_merge($blank, $values));
            } else {
                DB::table('generations')->where('Eartagno', $mother)->update($values);
            }
        });

        if ($duplicate) {
            return back()->withInput()->with('swal', [
                'title' => 'Error!',
                'text' => 'A record with this calf number already exists!',
                'icon' => 'warning',
            ]);
        }

        return redirect()->route('newborn.index')->with([
            'swal' => [
                'title' => 'Success!',
                'text' => 'Newborn information successfully saved',
                'icon' => 'success',
            ],
            'report' => [
                'calf_tagno' => $data['tagno'],
                'mother_tagno' => $data['M_tagno'],
                'breed' => $data['breeds'] ?? '',
                'dob' => $data['dob'] ?? '',
                'calf_number' => $n,
                'weights' => $data['weights'] ?? '',
                'birth_time' => $data['birth_times'] ?? '',
                'observation' => $data['observation'] ?? '',
                'disability' => $data['disability_desc'] ?? '',
            ],
        ]);
    }

    public function insemination(Request $request): JsonResponse
    {
        $empty = ['breed' => '', 'mating_time' => 0];
        $tag = $request->query('mother_tag');

        if (! is_numeric($tag)) {
            return response()->json($empty);
        }

        $row = DB::table('inseminated_cows')
            ->where('Eartagno', (int) $tag)
            ->select('Bull_Used_1', 'Mating_Time_1', 'Bull_Used_2', 'Mating_time_2', 'Bull_Used_3', 'Mating_time_3')
            ->first();

        if (! $row) {
            return response()->json($empty);
        }

        if ($row->Mating_time_3 > 0) {
            return response()->json(['breed' => $row->Bull_Used_3, 'mating_time' => $row->Mating_time_3]);
        }
        if ($row->Mating_time_2 > 0) {
            return response()->json(['breed' => $row->Bull_Used_2, 'mating_time' => $row->Mating_time_2]);
        }
        if ($row->Mating_Time_1 > 0) {
            return response()->json(['breed' => $row->Bull_Used_1, 'mating_time' => $row->Mating_Time_1]);
        }

        return response()->json($empty);
    }

    public function data(): JsonResponse
    {
        $latest = null;

        foreach (DB::table('inseminated_cows')->get() as $row) {
            $slots = [
                [$row->Insemination_Date_1, $row->Bull_Used_1],
                [$row->Insemination_Date2, $row->Bull_Used_2],
                [$row->Insemination_Date_3, $row->Bull_Used_3],
            ];
            foreach ($slots as [$date, $bull]) {
                $time = $this->timestampOf($date);
                if ($time !== null && ($latest === null || $time > $latest['time'])) {
                    $latest = ['time' => $time, 'row' => $row, 'bull' => $bull, 'date' => $date];
                }
            }
        }

        if ($latest === null) {
            return response()->json(['message' => 'No data found'], 404);
        }

        $cow = DB::table('cows')->where('Eartagno', $latest['row']->Eartagno)->first();

        return response()->json([
            'mother' => [
                'Eartag' => $latest['row']->Eartagno,
                'Breed' => $cow->Breed ?? null,
                'DOB' => $cow->DOB ?? null,
                'Latest_Semen' => $latest['bull'],
                'Latest_Semen_Date' => $latest['date'],
            ],
            'newborns' => Newborn::query()->get(['New_born_Tagno', 'DOB', 'Breed']),
        ]);
    }

    private function timestampOf(?string $date): ?int
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->getTimestamp();
        } catch (\Throwable) {
            return null;
        }
    }
}
