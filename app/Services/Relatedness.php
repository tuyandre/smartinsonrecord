<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Decides whether a cow and a bull are related, so inbreeding can be blocked.
 *
 * Parents come from the records already in the system: a calf registered under a mother
 * (generations table) has that cow as its dam, and the bull used in the dam's latest insemination as its sire.
 * Ancestors are followed three generations back. The pair is related when
 *  - the bull is an ancestor of the cow (sire, grand-sire...),
 *  - the cow is an ancestor of the bull (dam, grand-dam...), or
 *  - they share an ancestor (siblings, half siblings, cousins...).
 */
class Relatedness
{
    private const GENERATIONS = 3;

    public function __construct(private BullsApi $bulls)
    {
    }

    /** Human readable reason, or null when the two animals are not related. */
    public function between(string $cowTag, string $bullTag): ?string
    {
        $cowAncestors = $this->ancestors($cowTag, 'C');
        $bullAncestors = $this->ancestors($bullTag, 'B');

        if (isset($cowAncestors['B:'.$bullTag])) {
            return "Bull {$bullTag} is an ancestor of cow {$cowTag}.";
        }
        if (isset($bullAncestors['C:'.$cowTag])) {
            return "Cow {$cowTag} is an ancestor of bull {$bullTag}.";
        }

        $shared = array_intersect_key($cowAncestors, $bullAncestors);
        if ($shared) {
            [$kind, $tag] = explode(':', array_key_first($shared), 2);

            return 'Cow '.$cowTag.' and bull '.$bullTag.' share an ancestor ('.($kind === 'C' ? 'cow' : 'bull')." {$tag}).";
        }

        return null;
    }

    /** @return array<string, true> keys like "C:<cow tag>" / "B:<bull tag>" */
    private function ancestors(string $tag, string $kind): array
    {
        $found = [];
        $frontier = [$tag];

        for ($i = 0; $i < self::GENERATIONS && $frontier; $i++) {
            $next = [];
            foreach ($frontier as $calf) {
                [$dam, $sire] = $this->parents($calf);
                foreach ([['C', $dam], ['B', $sire]] as [$k, $parent]) {
                    if ($parent !== null && ! isset($found[$k.':'.$parent])) {
                        $found[$k.':'.$parent] = true;
                        $next[] = $parent;
                    }
                }
            }
            $frontier = $next;
        }

        return $found;
    }

    /** @return array{0: ?string, 1: ?string} [dam tag, sire tag] */
    public function parents(string $calfTag): array
    {
        if ($calfTag === '' || $calfTag === '0') {
            return [null, null];
        }

        $row = DB::table('generations')
            ->where('Calf_Eartagno_1', $calfTag)->orWhere('Calf_Eartagno_2', $calfTag)->orWhere('Calf_Eartagno_3', $calfTag)
            ->first();
        if (! $row) {
            return [null, null];
        }

        // The sire recorded at birth wins; older records fall back to the dam's latest insemination.
        $sire = null;
        foreach ([1, 2, 3] as $n) {
            if ((string) $row->{"Calf_Eartagno_$n"} === $calfTag && trim((string) $row->{"Breed_$n"}) !== '') {
                $sire = $this->bullTag(trim((string) $row->{"Breed_$n"}));
            }
        }

        return [(string) $row->Eartagno, $sire ?? $this->sireOf((string) $row->Eartagno)];
    }

    /** Bull tag used in the cow's most recent insemination. */
    public function sireOf(string $cowTag): ?string
    {
        $row = DB::table('inseminated_cows')->where('Eartagno', $cowTag)->first();
        if (! $row) {
            return null;
        }

        $slots = [
            [$row->Insemination_Date_1, $row->Bull_Used_1],
            [$row->Insemination_Date2, $row->Bull_Used_2],
            [$row->Insemination_Date_3, $row->Bull_Used_3],
        ];
        $used = null;
        foreach ($slots as [$date, $bull]) {
            if (trim((string) $date) !== '' && trim((string) $bull) !== '') {
                $used = trim((string) $bull);
            }
        }

        return $used === null ? null : $this->bullTag($used);
    }

    /** "TAG (CODE)" -> TAG; older records hold just a semen code, which is mapped back to its bull. */
    public function bullTag(string $stored): string
    {
        if (preg_match('/^(.+?)\s*\(/', $stored, $m)) {
            return trim($m[1]);
        }

        foreach ($this->bulls->all() as $bull) {
            foreach ($bull as $key => $value) {
                if (stripos($key, 'semen_code') !== false && (string) $value === $stored) {
                    return (string) $bull['Eartag'];
                }
            }
        }

        return $stored;
    }
}
