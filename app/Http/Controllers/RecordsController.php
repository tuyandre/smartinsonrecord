<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecordsController extends Controller
{
    /** Max rows sent to the browser; DataTables filters client-side. CSV export is never capped. */
    private const MAX_ROWS = 5000;

    public function cows(Request $request)
    {
        $query = DB::table('cows')->orderBy('Eartagno');

        if ($request->query('export') === 'csv') {
            return $this->csv($query, 'cows', [
                'Eartag No' => 'Eartagno', 'Name' => 'Name', 'Gender' => 'Gender', 'Breed' => 'Breed',
                'Cross Breed 1' => 'Cross_breed1', 'Cross Breed 2' => 'Cross_breed2',
                'Breed Percentage' => 'Breed_Percentage', 'Date of Birth' => 'Dob',
                'Number of Calvings' => 'Number_Calving', 'Owner' => 'Owner_names',
                'National ID' => 'National_Id', 'Phone' => 'Phone', 'District' => 'District',
                'Sector' => 'Sector', 'Cell' => 'Cell', 'Village' => 'Village',
            ]);
        }

        return view('pages.records.cows', $this->cap($query));
    }

    public function inseminations(Request $request)
    {
        $query = DB::table('inseminated_cows as i')
            ->leftJoin('cows as c', 'c.Eartagno', '=', 'i.Eartagno')
            ->select('i.*', 'c.Name as cow_name', 'c.Owner_names as owner')
            ->orderBy('i.Eartagno');

        if ($request->query('export') === 'csv') {
            $map = [
                'Eartag No' => 'Eartagno', 'Cow Name' => 'cow_name', 'Owner' => 'owner',
                'Previous Calving Date' => 'Previous_Calving_Date',
            ];
            $rounds = [
                1 => ['Mating_Time_1', 'Heat_Observation_Date_1', 'Hours_Heat_Observation_1', 'Insemination_Date_1', 'Bull_Used_1', 'Observation1', 'Veternary_Name1', 'Veternary_RCVD_ID1', 'Vaternary_Email1'],
                2 => ['Mating_time_2', 'Heat_Observation_Date2', 'Hours_Heat_Observation2', 'Insemination_Date2', 'Bull_Used_2', 'Observation_2', 'Veternary_Name_2', 'Veternary_RCVD_ID_2', 'Vaternary_Email_2'],
                3 => ['Mating_time_3', 'Heat_Observation_Date_3', 'Hours_Heat_Observation_3', 'Insemination_Date_3', 'Bull_Used_3', 'Observation_3', 'Veternary_Name_3', 'Veternary_RCVD_ID_3', 'Vaternary_Email_3'],
            ];
            $labels = ['Mating Time', 'Heat Date', 'Heat Hours', 'Insemination Date', 'Bull', 'Observation', 'Vet Name', 'Vet RCVD ID', 'Vet Email'];
            foreach ($rounds as $n => $list) {
                foreach ($list as $k => $col) {
                    $map["Round $n {$labels[$k]}"] = $col;
                }
            }

            return $this->csv($query, 'inseminations', $map);
        }

        return view('pages.records.inseminations', $this->cap($query));
    }

    public function calves(Request $request)
    {
        $union = null;
        foreach ([1, 2, 3] as $n) {
            $sel = DB::table('generations as g')
                ->leftJoin('cows as c', 'c.Eartagno', '=', 'g.Eartagno')
                ->where("g.Calf_Eartagno_$n", '!=', '0')
                ->selectRaw("g.Eartagno as mother_tag, c.Name as mother_name, $n as calf_no, g.Calf_Eartagno_$n as calf_tag, g.Breed_$n as breed, g.Dob_$n as dob, g.Weight_at_Birth_$n as weight, g.Birth_Times_$n as birth_times, g.Observation_$n as observation, g.Disability_$n as disability");
            $union = $union ? $union->unionAll($sel) : $sel;
        }

        $query = DB::query()->fromSub($union, 'calves')->orderBy('mother_tag')->orderBy('calf_no');

        if ($request->query('export') === 'csv') {
            return $this->csv($query, 'calves', [
                'Mother Eartag' => 'mother_tag', 'Mother Name' => 'mother_name', 'Calf No' => 'calf_no',
                'Calf Eartag' => 'calf_tag', 'Breed' => 'breed', 'Date of Birth' => 'dob',
                'Weight at Birth (kg)' => 'weight', 'Birth Times' => 'birth_times',
                'Observation' => 'observation', 'Disability' => 'disability',
            ]);
        }

        return view('pages.records.calves', $this->cap($query));
    }

    public function pd(Request $request)
    {
        $query = DB::table('pd as p')
            ->leftJoin('cows as c', 'c.Eartagno', '=', 'p.Eartagno')
            ->select('p.pd_id', 'p.Eartagno', 'p.Observation', 'c.Name as cow_name', 'c.Owner_names as owner')
            ->orderByDesc('p.pd_id');

        if ($request->query('export') === 'csv') {
            return $this->csv($query, 'pd-records', [
                'PD ID' => 'pd_id', 'Eartag No' => 'Eartagno', 'Cow Name' => 'cow_name',
                'Owner' => 'owner', 'Observation' => 'Observation',
            ]);
        }

        return view('pages.records.pd', $this->cap($query));
    }

    /** Fetch every row (up to MAX_ROWS) for the client-side table. */
    private function cap(Builder $query): array
    {
        $rows = $query->limit(self::MAX_ROWS + 1)->get();
        $truncated = $rows->count() > self::MAX_ROWS;
        if ($truncated) {
            $rows = $rows->take(self::MAX_ROWS)->values();
        }

        return ['rows' => $rows, 'truncated' => $truncated, 'max' => self::MAX_ROWS];
    }

    private function csv(Builder $query, string $name, array $map)
    {
        $file = $name.'-'.date('ymd').'.csv';

        return response()->streamDownload(function () use ($query, $map) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, array_keys($map));
            foreach ($query->cursor() as $row) {
                $line = [];
                foreach ($map as $col) {
                    $v = (string) ($row->$col ?? '');
                    // Neutralise spreadsheet formula injection.
                    if ($v !== '' && strpbrk($v[0], "=+-@\t\r") !== false && ! is_numeric($v)) {
                        $v = "'".$v;
                    }
                    $line[] = $v;
                }
                fputcsv($out, $line);
            }
            fclose($out);
        }, $file, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
