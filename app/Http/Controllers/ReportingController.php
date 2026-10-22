<?php

namespace App\Http\Controllers;

use App\Models\Semen;
use App\Services\Relatedness;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * One report page, six report types. Each type needs its own "view" permission, and every type
 * shares the same filters (date range, district, sector, veterinary, cow, bull, status, semen code).
 */
class ReportingController extends Controller
{
    /** type => [label, icon, permission, filters it understands] */
    private const TYPES = [
        'cows' => ['Cows', 'bi-tag', 'cows.view', ['date', 'district', 'sector', 'cow']],
        'bulls' => ['Bulls', 'bi-gender-male', 'bulls.view', ['date', 'district', 'sector', 'bull']],
        'inseminations' => ['Inseminations', 'bi-heart-pulse', 'inseminations.view', ['date', 'district', 'sector', 'vet', 'cow', 'bull']],
        'calves' => ['Calves', 'bi-emoji-smile', 'calves.view', ['date', 'district', 'sector', 'cow', 'bull']],
        'pd' => ['Pregnancy diagnosis', 'bi-clipboard2-pulse', 'pd.view', ['date', 'district', 'sector', 'vet', 'cow', 'bull', 'status']],
        'semen' => ['Semen info', 'bi-droplet-half', 'semen.view', ['date', 'bull', 'semen_code']],
    ];

    private const SEMEN_COLUMNS = [
        'Eartag' => 'Eartag', 'Breed' => 'Breed', 'Dob' => 'Dob', 'Name' => 'Name',
        'registration_no_1' => 'Registration No 1', 'freezing_date_1' => 'Freezing Date 1', 'Semen_code_1' => 'Semen Code 1',
        'registration_no_2' => 'Registration No 2', 'freezing_date_2' => 'Freezing Date 2', 'semen_code_2' => 'Semen Code 2',
        'registration__code_3' => 'Registration No 3', 'freezing_date_3' => 'Freezing Date 3', 'semen_code_3' => 'Semen Code 3',
        'registration_no_4' => 'Registration No 4', 'freezing_date_4' => 'Freezing Date 4', 'semen_code_4' => 'Semen Code 4',
        'registration_no_5' => 'Registration No 5', 'freezing_date_5' => 'Freezing Date 5', 'semen_code_5' => 'Semen Code 5',
        'international_code' => 'International Code', 'aic_no' => 'AIC No', 'bull_no' => 'Bull No',
    ];

    public function index(Request $request)
    {
        $user = $request->user();
        $types = array_filter(self::TYPES, fn ($t) => $user->hasPermission($t[2]));
        abort_if($types === [], 403, 'You do not have permission to open any report.');

        // Old bookmark: /reporting?check_semen=Semen+Info
        $type = $request->has('check_semen') ? 'semen' : (string) $request->query('type', array_key_first($types));
        abort_unless(isset($types[$type]), 403, 'You do not have permission to open this report.');

        $f = $this->filters($request);
        $generated = $request->has('run') || $request->has('check_semen');
        $report = $generated ? $this->build($type, $f) : null;

        return view('pages.reporting.index', [
            'types' => $types,
            'type' => $type,
            'f' => $f,
            'report' => $report,
            'title' => self::TYPES[$type][0],
            'appliesTo' => array_map(fn ($t) => $t[3], self::TYPES),
            'districts' => $this->distinct('District'),
            'sectors' => $this->distinct('Sector'),
            'vets' => $this->vets(),
            'statuses' => DB::table('pd')->whereRaw("TRIM(Observation) <> ''")->pluck('Observation')->map(fn ($s) => trim($s))->unique()->sort()->values(),
            'selectedCow' => $f['cow'] ? DB::table('cows')->where('Eartagno', $f['cow'])->first(['Eartagno', 'Name']) : null,
            'selectedBull' => $f['bull'] ? DB::table('bulls')->where('Eartagno', $f['bull'])->first(['Eartagno', 'Name']) : null,
            'now' => now()->format('Y-m-d h:i:s'),
        ]);
    }

    /** Search select source for the cow / bull filters. */
    public function animals(Request $request, string $kind): JsonResponse
    {
        abort_unless(in_array($kind, ['cows', 'bulls'], true), 404);

        $q = trim((string) $request->query('q', ''));
        $like = '%'.addcslashes($q, '\\%_').'%';

        $rows = DB::table($kind)
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('Eartagno', 'like', $like)->orWhere('Name', 'like', $like)->orWhere('Owner_names', 'like', $like)))
            ->orderBy('Eartagno')->limit(30)->get(['Eartagno', 'Name', 'Owner_names']);

        return response()->json(['results' => $rows->map(fn ($r) => [
            'id' => (string) $r->Eartagno,
            'text' => $r->Eartagno.' · '.$r->Name.($r->Owner_names ? ' ('.$r->Owner_names.')' : ''),
        ])->all()]);
    }

    // ---------------------------------------------------------------- filters

    private function filters(Request $request): array
    {
        $clean = fn ($key) => trim((string) $request->query($key, ''));
        $date = fn ($key) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $clean($key)) ? $clean($key) : '';

        return [
            'from' => $date('from'),
            'to' => $date('to'),
            'district' => $clean('district'),
            'sector' => $clean('sector'),
            'vet' => $clean('vet'),
            'cow' => preg_match('/^[A-Za-z0-9]+$/', $clean('cow')) ? $clean('cow') : '',
            'bull' => preg_match('/^[A-Za-z0-9]+$/', $clean('bull')) ? $clean('bull') : '',
            'status' => strtolower($clean('status')),
            'semen_code' => $clean('semen_code'),
        ];
    }

    private function inRange(?string $value, array $f): bool
    {
        if ($f['from'] === '' && $f['to'] === '') {
            return true;
        }
        $time = $this->time($value);
        if ($time === null) {
            return false;
        }
        $day = date('Y-m-d', $time);

        return ($f['from'] === '' || $day >= $f['from']) && ($f['to'] === '' || $day <= $f['to']);
    }

    private function time(?string $value): ?int
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        try {
            return (preg_match('#^\d{1,2}/\d{1,2}/\d{4}$#', $value) ? Carbon::createFromFormat('d/m/Y', $value) : Carbon::parse($value))->getTimestamp();
        } catch (\Throwable) {
            return null;
        }
    }

    private function distinct(string $column): array
    {
        return DB::table('cows')->select($column)->union(DB::table('bulls')->select($column))
            ->get()->pluck($column)->map(fn ($v) => trim((string) $v))->filter()->unique()->sort()->values()->all();
    }

    private function vets(): array
    {
        $names = [];
        foreach (['Veternary_Name1', 'Veternary_Name_2', 'Veternary_Name_3'] as $column) {
            $names = array_merge($names, DB::table('inseminated_cows')->whereRaw("TRIM($column) <> ''")->distinct()->pluck($column)->all());
        }

        return collect($names)->map(fn ($v) => trim($v))->unique()->sort()->values()->all();
    }

    // ---------------------------------------------------------------- reports

    /** @return array{columns: array<int, array{0: string, 1: ?string}>, rows: array<int, array>} */
    private function build(string $type, array $f): array
    {
        return match ($type) {
            'cows' => $this->animalsReport('cows', $f),
            'bulls' => $this->animalsReport('bulls', $f),
            'inseminations' => $this->inseminations($f),
            'calves' => $this->calves($f),
            'pd' => $this->pd($f),
            'semen' => $this->semen($f),
        };
    }

    private function animalsReport(string $table, array $f): array
    {
        $tag = $table === 'cows' ? $f['cow'] : $f['bull'];
        $rows = DB::table($table)
            ->when($tag !== '', fn ($q) => $q->where('Eartagno', $tag))
            ->when($f['district'] !== '', fn ($q) => $q->where('District', $f['district']))
            ->when($f['sector'] !== '', fn ($q) => $q->where('Sector', $f['sector']))
            ->orderBy('Eartagno')->get()
            ->filter(fn ($r) => $this->inRange($r->Dob, $f));

        return [
            'columns' => [
                ['Eartag', 'text'], ['Name', 'text'], ['Breed', 'select'], ['Cross breeds', null], ['Date of birth', null],
                ['Calvings', null], ['Owner', 'text'], ['National ID', null], ['Phone', null],
                ['District', 'select'], ['Sector', 'select'], ['Cell', null], ['Village', null],
            ],
            'rows' => $rows->map(fn ($r) => [
                $r->Eartagno, $r->Name, $r->Breed,
                collect([$r->Cross_breed1, $r->Cross_breed2])->reject(fn ($v) => $v === '' || $v === 'N/A')->implode(' / ') ?: '—',
                $r->Dob, $r->Number_Calving, $r->Owner_names, $r->National_Id, $r->Phone,
                $r->District, $r->Sector, $r->Cell, $r->Village,
            ])->values()->all(),
        ];
    }

    private function inseminations(array $f): array
    {
        $relatedness = app(Relatedness::class);
        $slots = [
            1 => ['Heat_Observation_Date_1', 'Hours_Heat_Observation_1', 'Insemination_Date_1', 'Bull_Used_1', 'Observation1', 'Veternary_Name1', 'Veternary_RCVD_ID1', 'Vaternary_Email1'],
            2 => ['Heat_Observation_Date2', 'Hours_Heat_Observation2', 'Insemination_Date2', 'Bull_Used_2', 'Observation_2', 'Veternary_Name_2', 'Veternary_RCVD_ID_2', 'Vaternary_Email_2'],
            3 => ['Heat_Observation_Date_3', 'Hours_Heat_Observation_3', 'Insemination_Date_3', 'Bull_Used_3', 'Observation_3', 'Veternary_Name_3', 'Veternary_RCVD_ID_3', 'Vaternary_Email_3'],
        ];

        $records = DB::table('inseminated_cows as i')
            ->leftJoin('cows as c', 'c.Eartagno', '=', 'i.Eartagno')
            ->when($f['cow'] !== '', fn ($q) => $q->where('i.Eartagno', $f['cow']))
            ->when($f['district'] !== '', fn ($q) => $q->where('c.District', $f['district']))
            ->when($f['sector'] !== '', fn ($q) => $q->where('c.Sector', $f['sector']))
            ->orderBy('i.Eartagno')
            ->get(['i.*', 'c.Name as cow_name', 'c.Owner_names as owner', 'c.District as district', 'c.Sector as sector']);

        $rows = [];
        foreach ($records as $r) {
            foreach ($slots as $n => [$heat, $hours, $date, $bull, $obs, $vet, $rcvd, $email]) {
                if (trim((string) $r->$date) === '' || ! $this->inRange($r->$date, $f)) {
                    continue;
                }
                if ($f['vet'] !== '' && trim((string) $r->$vet) !== $f['vet']) {
                    continue;
                }
                if ($f['bull'] !== '' && $relatedness->bullTag(trim((string) $r->$bull)) !== $f['bull']) {
                    continue;
                }
                $rows[] = [
                    $r->Eartagno, $r->cow_name, $r->owner, $r->district, $r->sector, $n,
                    $r->$heat, $r->$hours, $r->$date, $r->$bull, $r->$obs, $r->$vet, $r->$rcvd, $r->$email,
                ];
            }
        }

        return [
            'columns' => [
                ['Eartag', 'text'], ['Cow', 'text'], ['Owner', 'text'], ['District', 'select'], ['Sector', 'select'], ['AI time', 'select'],
                ['Heat date', null], ['Heat hours', null], ['Insemination date', null], ['Bull used', 'select'], ['Observation', 'select'],
                ['Veterinary', 'select'], ['RCVD ID', null], ['Email', null],
            ],
            'rows' => $rows,
        ];
    }

    private function calves(array $f): array
    {
        $rows = [];
        $generations = DB::table('generations as g')
            ->leftJoin('cows as c', 'c.Eartagno', '=', 'g.Eartagno')
            ->when($f['cow'] !== '', fn ($q) => $q->where('g.Eartagno', $f['cow']))
            ->when($f['district'] !== '', fn ($q) => $q->where('c.District', $f['district']))
            ->when($f['sector'] !== '', fn ($q) => $q->where('c.Sector', $f['sector']))
            ->orderBy('g.Eartagno')
            ->get(['g.*', 'c.Name as mother_name', 'c.District as district', 'c.Sector as sector']);

        foreach ($generations as $g) {
            foreach ([1, 2, 3] as $n) {
                $tag = (string) $g->{"Calf_Eartagno_$n"};
                if ($tag === '0' || $tag === '' || ! $this->inRange($g->{"Dob_$n"}, $f)) {
                    continue;
                }
                if ($f['bull'] !== '' && (string) $g->{"Breed_$n"} !== $f['bull']) {
                    continue;
                }
                $sex = DB::table('cows')->where('Eartagno', $tag)->exists() ? 'Female' : (DB::table('bulls')->where('Eartagno', $tag)->exists() ? 'Male' : '—');
                $rows[] = [
                    $g->Eartagno, $g->mother_name, $tag, $sex, $g->{"Breed_$n"} ?: '—', $g->{"Dob_$n"}, $g->{"Weight_at_Birth_$n"},
                    $g->{"Birth_Times_$n"}, $g->{"Observation_$n"}, $g->{"Disability_$n"} ?: '—', $g->district, $g->sector,
                ];
            }
        }

        return [
            'columns' => [
                ['Mother', 'text'], ['Mother name', 'text'], ['Calf', 'text'], ['Sex', 'select'], ['Sire', 'select'], ['Date of birth', null],
                ['Weight (kg)', null], ['Birth times', null], ['Observation', 'select'], ['Disability', null], ['District', 'select'], ['Sector', 'select'],
            ],
            'rows' => $rows,
        ];
    }

    private function pd(array $f): array
    {
        $relatedness = app(Relatedness::class);
        $records = DB::table('pd as p')
            ->leftJoin('cows as c', 'c.Eartagno', '=', 'p.Eartagno')
            ->leftJoin('inseminated_cows as i', 'i.Eartagno', '=', 'p.Eartagno')
            ->when($f['cow'] !== '', fn ($q) => $q->where('p.Eartagno', $f['cow']))
            ->when($f['district'] !== '', fn ($q) => $q->where('c.District', $f['district']))
            ->when($f['sector'] !== '', fn ($q) => $q->where('c.Sector', $f['sector']))
            ->when($f['status'] !== '', fn ($q) => $q->whereRaw('TRIM(LOWER(p.Observation)) = ?', [$f['status']]))
            ->orderByDesc('p.pd_id')
            ->get(['p.pd_id', 'p.Eartagno', 'p.Observation', 'c.Name as cow_name', 'c.Owner_names as owner', 'c.District as district', 'c.Sector as sector', 'i.*']);

        $rows = [];
        foreach ($records as $r) {
            // Latest insemination round of the cow.
            $latest = null;
            foreach ([['Insemination_Date_1', 'Bull_Used_1', 'Veternary_Name1'], ['Insemination_Date2', 'Bull_Used_2', 'Veternary_Name_2'], ['Insemination_Date_3', 'Bull_Used_3', 'Veternary_Name_3']] as [$d, $b, $v]) {
                if (trim((string) ($r->$d ?? '')) !== '') {
                    $latest = [$r->$d, $r->$b, $r->$v];
                }
            }
            if (! $this->inRange($latest[0] ?? null, $f)) {
                continue;
            }
            if ($f['vet'] !== '' && trim((string) ($latest[2] ?? '')) !== $f['vet']) {
                continue;
            }
            if ($f['bull'] !== '' && $relatedness->bullTag(trim((string) ($latest[1] ?? ''))) !== $f['bull']) {
                continue;
            }
            $rows[] = [$r->Eartagno, $r->cow_name, $r->owner, $r->district, $r->sector, trim($r->Observation), $latest[0] ?? '—', $latest[1] ?? '—', $latest[2] ?? '—'];
        }

        return [
            'columns' => [
                ['Eartag', 'text'], ['Cow', 'text'], ['Owner', 'text'], ['District', 'select'], ['Sector', 'select'],
                ['PD result', 'select'], ['Insemination date', null], ['Bull used', 'select'], ['Veterinary', 'select'],
            ],
            'rows' => $rows,
        ];
    }

    /** Semen registered in the system (Semen List); the government catalogue is imported with GovernmentBullsSeeder. */
    private function semen(array $f): array
    {
        $records = Semen::with(['bull', 'lots'])->orderBy('bull_eartag')->get()->map->toReportRow()->all();

        $rows = [];
        foreach ($records as $row) {
            if ($f['bull'] !== '' && (string) ($row['Eartag'] ?? '') !== $f['bull']) {
                continue;
            }
            if ($f['semen_code'] !== '') {
                $match = false;
                foreach (['Semen_code_1', 'semen_code_2', 'semen_code_3', 'semen_code_4', 'semen_code_5'] as $key) {
                    $match = $match || (isset($row[$key]) && strcasecmp((string) $row[$key], $f['semen_code']) === 0);
                }
                if (! $match) {
                    continue;
                }
            }
            if ($f['from'] !== '' || $f['to'] !== '') {
                $inRange = false;
                foreach ([1, 2, 3, 4, 5] as $n) {
                    $inRange = $inRange || (! empty($row["freezing_date_$n"]) && $this->inRange($row["freezing_date_$n"], $f));
                }
                if (! $inRange) {
                    continue;
                }
            }
            $rows[] = array_map(fn ($key) => $row[$key] ?? '', array_keys(self::SEMEN_COLUMNS));
        }

        $filterKind = ['Eartag' => 'text', 'Name' => 'text', 'Breed' => 'select', 'bull_no' => 'text', 'international_code' => 'text'];

        return [
            'columns' => array_map(fn ($key, $label) => [$label, $filterKind[$key] ?? null], array_keys(self::SEMEN_COLUMNS), self::SEMEN_COLUMNS),
            'rows' => $rows,
        ];
    }
}
