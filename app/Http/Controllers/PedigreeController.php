<?php

namespace App\Http\Controllers;

use App\Services\Relatedness;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PedigreeController extends Controller
{
    private const ANCESTOR_GENERATIONS = 3;
    private const DESCENDANT_GENERATIONS = 2;

    public function index(Request $request, Relatedness $relatedness): View
    {
        $query = trim((string) $request->query('eartagno', ''));
        $root = null;
        $suggestions = collect();

        if ($query !== '') {
            $found = $this->find($query);
            if ($found) {
                $root = $found;
            } else {
                $like = '%'.addcslashes($query, '\\%_').'%';
                $suggestions = collect(['cows' => 'cow', 'bulls' => 'bull'])->flatMap(fn ($kind, $table) => DB::table($table)
                    ->where('Eartagno', 'like', $like)->orWhere('Name', 'like', $like)->limit(8)->get(['Eartagno', 'Name'])
                    ->map(fn ($r) => ['tag' => (string) $r->Eartagno, 'name' => $r->Name, 'kind' => $kind]))->take(10)->values();
            }
        }

        return view('pages.pedigree.index', [
            'query' => $query,
            'root' => $root,
            'ancestors' => $root ? $this->ancestors($root['tag'], $root['kind'], $relatedness, self::ANCESTOR_GENERATIONS) : null,
            'descendants' => $root ? $this->descendants($root['tag'], $root['kind'], self::DESCENDANT_GENERATIONS) : null,
            'suggestions' => $suggestions,
            'months' => $this->months()['labels'],
            'counts' => $this->months()['counts'],
        ]);
    }

    /** Search select source: registered cows and bulls. */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $like = '%'.addcslashes($q, '\\%_').'%';
        $results = [];

        foreach (['cows' => 'Cows', 'bulls' => 'Bulls'] as $table => $group) {
            $rows = DB::table($table)
                ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('Eartagno', 'like', $like)->orWhere('Name', 'like', $like)->orWhere('Owner_names', 'like', $like)))
                ->orderBy('Eartagno')->limit(15)->get(['Eartagno', 'Name', 'Owner_names']);
            if ($rows->isNotEmpty()) {
                $results[] = ['text' => $group, 'children' => $rows->map(fn ($r) => [
                    'id' => (string) $r->Eartagno,
                    'text' => $r->Eartagno.' · '.$r->Name.($r->Owner_names ? ' ('.$r->Owner_names.')' : ''),
                ])->all()];
            }
        }

        return response()->json(['results' => $results]);
    }

    // ------------------------------------------------------------ tree building

    /** Cow or bull with the given ear tag (exact match, any letter case). */
    private function find(string $tag): ?array
    {
        foreach (['cows' => 'cow', 'bulls' => 'bull'] as $table => $kind) {
            if ($row = DB::table($table)->where('Eartagno', $tag)->first()) {
                return $this->node((string) $row->Eartagno, $kind, $row);
            }
        }

        return null;
    }

    /** @return array{tag: string, kind: string, name: ?string, breed: ?string, dob: ?string, known: bool} */
    private function node(string $tag, string $kind, ?object $row = null): array
    {
        $row ??= DB::table($kind === 'bull' ? 'bulls' : 'cows')->where('Eartagno', $tag)->first();

        return [
            'tag' => $tag,
            'kind' => $kind,
            'known' => $row !== null,
            'name' => $row->Name ?? null,
            'breed' => $row->Breed ?? null,
            'dob' => $row->Dob ?? null,
        ];
    }

    /** Dam and sire, recursively (the animal itself is the root). */
    private function ancestors(string $tag, string $kind, Relatedness $relatedness, int $depth, array $seen = []): array
    {
        $node = $this->node($tag, $kind) + ['children' => []];
        $seen[] = $kind.$tag;

        if ($depth > 0) {
            [$dam, $sire] = $relatedness->parents($tag);
            foreach ([['cow', $dam, 'Dam'], ['bull', $sire, 'Sire']] as [$parentKind, $parentTag, $role]) {
                if ($parentTag !== null && ! in_array($parentKind.$parentTag, $seen, true)) {
                    $node['children'][] = $this->ancestors($parentTag, $parentKind, $relatedness, $depth - 1, $seen) + ['role' => $role];
                }
            }
        }

        return $node;
    }

    /** Calves (a cow's own calves, or the calves sired by a bull), recursively. */
    private function descendants(string $tag, string $kind, int $depth, array $seen = []): array
    {
        $node = $this->node($tag, $kind) + ['children' => []];
        $seen[] = $tag;

        if ($depth > 0) {
            foreach ($this->calvesOf($tag, $kind) as $calf) {
                if (! in_array($calf['tag'], $seen, true)) {
                    $node['children'][] = $this->descendants($calf['tag'], $calf['kind'], $depth - 1, $seen)
                        + ['role' => $kind === 'cow' ? 'Calf '.$calf['no'].($calf['sire'] ? ' · sire '.$calf['sire'] : '') : 'Calf '.$calf['no'].' of '.$calf['mother']];
                }
            }
        }

        return $node;
    }

    /** @return array<int, array{tag: string, kind: string, no: int, sire: ?string, mother: string}> */
    private function calvesOf(string $tag, string $kind): array
    {
        $rows = $kind === 'cow'
            ? DB::table('generations')->where('Eartagno', $tag)->get()
            : DB::table('generations')->where('Breed_1', $tag)->orWhere('Breed_2', $tag)->orWhere('Breed_3', $tag)->get();

        $calves = [];
        foreach ($rows as $row) {
            foreach ([1, 2, 3] as $n) {
                $calf = (string) $row->{"Calf_Eartagno_$n"};
                if ($calf === '0' || $calf === '') {
                    continue;
                }
                $sire = trim((string) $row->{"Breed_$n"}) ?: null;
                if ($kind === 'bull' && $sire !== $tag) {
                    continue;
                }
                $calves[] = [
                    'tag' => $calf,
                    'kind' => DB::table('bulls')->where('Eartagno', $calf)->exists() ? 'bull' : 'cow',
                    'no' => $n,
                    'sire' => $sire,
                    'mother' => (string) $row->Eartagno,
                ];
            }
        }

        return $calves;
    }

    private function months(): array
    {
        static $cache = null;
        if ($cache) {
            return $cache;
        }

        $counts = array_fill_keys(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], 0);
        foreach (DB::table('inseminated_cows')->select('Insemination_Date_1', 'Insemination_Date2', 'Insemination_Date_3')->get() as $row) {
            foreach ([$row->Insemination_Date_1, $row->Insemination_Date2, $row->Insemination_Date_3] as $date) {
                if (! empty($date) && ($time = strtotime($date)) !== false) {
                    $counts[date('M', $time)]++;
                }
            }
        }

        return $cache = ['labels' => array_keys($counts), 'counts' => array_values($counts)];
    }
}
