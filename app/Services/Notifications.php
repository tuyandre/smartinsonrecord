<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class Notifications
{
    /** Real, data-driven alerts for the top bar bell. */
    public function all(): array
    {
        $items = [];
        $now = time();

        // Latest insemination per cow (dates are stored as strings, newest slot wins).
        $rows = DB::table('inseminated_cows as i')
            ->leftJoin('cows as c', 'c.Eartagno', '=', 'i.Eartagno')
            ->get(['i.Eartagno', 'c.Name as cow_name', 'i.Insemination_Date_1', 'i.Insemination_Date2', 'i.Insemination_Date_3']);
        $pdTags = DB::table('pd')->pluck('Eartagno')->flip();

        foreach ($rows as $row) {
            $last = null;
            foreach ([$row->Insemination_Date_1, $row->Insemination_Date2, $row->Insemination_Date_3] as $d) {
                $t = $d ? strtotime($d) : false;
                if ($t && (! $last || $t > $last)) {
                    $last = $t;
                }
            }
            if (! $last) {
                continue;
            }
            $days = (int) floor(($now - $last) / 86400);
            $name = trim(($row->cow_name ?: 'Cow').' · '.$row->Eartagno);

            if ($days >= 0 && $days <= 7) {
                $items[] = ['icon' => 'bi-heart-pulse', 'text' => "{$name} inseminated ".($days === 0 ? 'today' : "{$days}d ago"), 'time' => $last, 'url' => route('records.inseminations')];
            } elseif ($days >= 45 && ! isset($pdTags[$row->Eartagno])) {
                $items[] = ['icon' => 'bi-clipboard2-pulse', 'text' => "{$name}: pregnancy diagnosis due ({$days}d since insemination)", 'time' => $last, 'url' => route('dashboard', ['pattern' => $row->Eartagno])];
            }
        }

        foreach (DB::table('newborn')->orderByDesc('DOB')->limit(5)->get() as $n) {
            $t = strtotime($n->DOB);
            if ($t && $now - $t <= 14 * 86400) {
                $items[] = ['icon' => 'bi-balloon-heart', 'text' => "New calf {$n->New_born_Tagno} born to cow {$n->Mother_Eartag}", 'time' => $t, 'url' => route('dashboard', ['pattern' => $n->Mother_Eartag])];
            }
        }

        usort($items, fn ($a, $b) => $b['time'] <=> $a['time']);

        return $items;
    }
}
