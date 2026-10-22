<?php

namespace App\Http\Controllers;

use App\Models\Bull;
use App\Models\Cow;
use App\Models\Newborn;
use App\Services\BullsApi;
use App\Services\Relatedness;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewbornController extends Controller
{
    private const TAG = ['required', 'regex:/^[A-Za-z0-9]+$/', 'max:30'];

    public function index(): View
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

        $selectedMother = old('M_tagno')
            ? DB::table('cows')->where('Eartagno', old('M_tagno'))->first(['Eartagno', 'Name'])
            : null;

        return view('pages.newborn.index', [
            'months' => array_keys($counts),
            'counts' => array_values($counts),
            'selectedMother' => $selectedMother,
            'report' => session('report'),
        ]);
    }

    /** Inseminated cows for the mother search select. */
    public function mothers(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $like = '%'.addcslashes($q, '\\%_').'%';

        $rows = DB::table('inseminated_cows as i')
            ->join('cows as c', 'c.Eartagno', '=', 'i.Eartagno')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('c.Eartagno', 'like', $like)->orWhere('c.Name', 'like', $like)->orWhere('c.Owner_names', 'like', $like)))
            ->orderBy('c.Eartagno')
            ->limit(30)
            ->get(['c.Eartagno', 'c.Name', 'c.Owner_names']);

        return response()->json(['results' => $rows->map(fn ($r) => [
            'id' => (string) $r->Eartagno,
            'text' => $r->Eartagno.' · '.$r->Name.($r->Owner_names ? ' ('.$r->Owner_names.')' : ''),
        ])->all()]);
    }

    /** What the form needs once a mother is chosen: sire, birth times, free calf numbers, relationship check. */
    public function insemination(Request $request, Relatedness $relatedness): JsonResponse
    {
        $tag = (string) $request->query('mother_tag', '');
        $empty = ['bull' => '', 'bull_name' => '', 'mating_time' => 0, 'free_numbers' => [1, 2, 3], 'related' => false, 'message' => null];

        if (! preg_match('/^[A-Za-z0-9]+$/', $tag)) {
            return response()->json($empty);
        }

        $row = DB::table('inseminated_cows')->where('Eartagno', $tag)->first();
        if (! $row) {
            return response()->json($empty);
        }

        $time = 0;
        foreach ([3, 2, 1] as $n) {
            $value = $n === 1 ? $row->Mating_Time_1 : $row->{'Mating_time_'.$n};
            if ($value > 0) {
                $time = (int) $value;
                break;
            }
        }

        $bull = $relatedness->sireOf($tag);
        $reason = $bull ? $relatedness->between($tag, $bull) : null;

        $gen = DB::table('generations')->where('Eartagno', $tag)->first();
        $free = array_values(array_filter([1, 2, 3], fn ($n) => ! $gen || (int) $gen->{"Birth_Times_$n"} === 0));

        return response()->json([
            'bull' => (string) $bull,
            'bull_name' => $bull ? (string) $this->bullDetails($bull)['name'] : '',
            'mating_time' => $time,
            'free_numbers' => $free,
            'related' => $reason !== null,
            'message' => $reason,
        ]);
    }

    public function store(Request $request, Relatedness $relatedness): RedirectResponse
    {
        $data = $request->validate([
            'tagno' => self::TAG,
            'M_tagno' => self::TAG,
            'sex' => ['required', 'in:Female,Male'],
            'calf_name' => ['nullable', 'string', 'max:20'],
            'dob' => ['required', 'date', 'before_or_equal:today'],
            'weights' => ['nullable', 'integer', 'min:0'],
            'calf_number' => ['required', 'in:1,2,3'],
            'birth_times' => ['nullable', 'integer', 'min:0'],
            'observation' => ['required', 'in:Normal,Disability'],
            'disability_desc' => ['nullable', 'required_if:observation,Disability', 'string', 'max:70'],
        ], [
            'calf_number.required' => 'Invalid calf number!',
            'calf_number.in' => 'Invalid calf number!',
            'sex.required' => 'Choose the calf sex.',
            'observation.required' => 'Choose an observation.',
            'disability_desc.required_if' => 'Describe the disability.',
        ]);

        $tag = (string) $data['tagno'];
        $mother = (string) $data['M_tagno'];
        $n = (int) $data['calf_number'];

        if (! DB::table('inseminated_cows')->where('Eartagno', $mother)->exists()) {
            return $this->fail('Mother tag number is not an inseminated cow!');
        }
        if (strcasecmp($tag, $mother) === 0) {
            return $this->fail('A calf cannot have the same ear tag as its mother.');
        }
        if ($this->tagInUse($tag)) {
            return $this->fail("Ear tag {$tag} is already registered.");
        }

        $bullTag = $relatedness->sireOf($mother);
        if ($bullTag && ($reason = $relatedness->between($mother, $bullTag))) {
            return $this->fail($reason.' This birth cannot be registered.', 'Not allowed');
        }

        $values = [
            "Calf_Eartagno_$n" => $tag,
            "Breed_$n" => Str::limit((string) $bullTag, 20, ''),
            "Dob_$n" => $data['dob'],
            "Weight_at_Birth_$n" => (int) ($data['weights'] ?? 0),
            "Birth_Times_$n" => (int) ($data['birth_times'] ?? 0),
            "Observation_$n" => $data['observation'],
            "Disability_$n" => $data['observation'] === 'Disability' ? ($data['disability_desc'] ?? '') : '',
        ];

        $duplicate = false;

        DB::transaction(function () use ($mother, $n, $values, $data, $tag, $bullTag, &$duplicate) {
            $existing = DB::table('generations')->where('Eartagno', $mother)->lockForUpdate()->get();

            if ($existing->contains(fn ($row) => (int) $row->{"Birth_Times_$n"} !== 0 || ($row->{"Calf_Eartagno_$n"} ?? '0') !== '0')) {
                $duplicate = true;

                return;
            }

            if ($existing->isEmpty()) {
                DB::table('generations')->insert(['Eartagno' => $mother] + $values + $this->blankSlots());
            } else {
                DB::table('generations')->where('Eartagno', $mother)->update($values);
            }

            $this->registerCalf($tag, $mother, $bullTag, $data);
        });

        if ($duplicate) {
            return $this->fail('A record with this calf number already exists!');
        }

        return redirect()->route('newborn.index')->with([
            'swal' => ['title' => 'Success!', 'text' => 'Newborn information successfully saved', 'icon' => 'success'],
            'report' => [
                'calf_tagno' => $tag,
                'mother_tagno' => $mother,
                'sex' => $data['sex'],
                'breed' => (string) $bullTag,
                'dob' => $data['dob'],
                'calf_number' => $n,
                'weights' => $data['weights'] ?? '',
                'birth_time' => $data['birth_times'] ?? '',
                'observation' => $data['observation'],
                'disability' => $values["Disability_$n"],
            ],
        ]);
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
                'DOB' => $cow->Dob ?? null,
                'Latest_Semen' => $latest['bull'],
                'Latest_Semen_Date' => $latest['date'],
            ],
            'newborns' => Newborn::query()->get(['New_born_Tagno', 'DOB', 'Breed']),
        ]);
    }

    // ---------- birth records (the calves list): view, edit, delete ----------

    public function show(string $mother, int $n): View
    {
        [$gen, $calf] = $this->slot($mother, $n);

        return view('pages.calves.show', [
            'calf' => $calf,
            'mother' => Cow::find($mother),
            'animal' => $this->animalOf($calf['tag']),
        ]);
    }

    public function edit(string $mother, int $n): View
    {
        [$gen, $calf] = $this->slot($mother, $n);

        return view('pages.calves.edit', ['calf' => $calf, 'mother' => Cow::find($mother)]);
    }

    public function update(Request $request, string $mother, int $n): RedirectResponse
    {
        [$gen, $calf] = $this->slot($mother, $n);

        $data = $request->validate([
            'dob' => ['required', 'date', 'before_or_equal:today'],
            'weights' => ['nullable', 'integer', 'min:0'],
            'birth_times' => ['nullable', 'integer', 'min:0'],
            'observation' => ['required', 'in:Normal,Disability'],
            'disability_desc' => ['nullable', 'required_if:observation,Disability', 'string', 'max:70'],
        ], ['disability_desc.required_if' => 'Describe the disability.']);

        DB::transaction(function () use ($mother, $n, $calf, $data) {
            DB::table('generations')->where('Eartagno', $mother)->update([
                "Dob_$n" => $data['dob'],
                "Weight_at_Birth_$n" => (int) ($data['weights'] ?? 0),
                "Birth_Times_$n" => (int) ($data['birth_times'] ?? 0),
                "Observation_$n" => $data['observation'],
                "Disability_$n" => $data['observation'] === 'Disability' ? ($data['disability_desc'] ?? '') : '',
            ]);

            // Keep the registered animal's date of birth in step.
            foreach (['cows', 'bulls'] as $table) {
                DB::table($table)->where('Eartagno', $calf['tag'])->update(['Dob' => $data['dob']]);
            }
        });

        return redirect()->route('records.calves')->with('swal', ['title' => 'Success!', 'text' => 'Birth record successfully updated', 'icon' => 'success']);
    }

    /** Clears the birth record. The calf's own cow/bull registration is kept. */
    public function destroy(string $mother, int $n): RedirectResponse
    {
        $this->slot($mother, $n);

        DB::table('generations')->where('Eartagno', $mother)->update([
            "Calf_Eartagno_$n" => '0', "Breed_$n" => '', "Dob_$n" => '', "Weight_at_Birth_$n" => 0,
            "Birth_Times_$n" => 0, "Observation_$n" => '', "Disability_$n" => '',
        ]);

        return redirect()->route('records.calves')->with('swal', ['title' => 'Success!', 'text' => 'Birth record deleted. The calf stays in its registered list.', 'icon' => 'success']);
    }

    // ---------- helpers ----------

    private function fail(string $message, string $title = 'Error!'): RedirectResponse
    {
        return back()->withInput()->with('swal', ['title' => $title, 'text' => $message, 'icon' => 'warning']);
    }

    private function blankSlots(): array
    {
        $blank = [];
        foreach ([1, 2, 3] as $i) {
            $blank += [
                "Calf_Eartagno_$i" => '0', "Breed_$i" => '', "Dob_$i" => '', "Weight_at_Birth_$i" => 0,
                "Birth_Times_$i" => 0, "Observation_$i" => '', "Disability_$i" => '',
            ];
        }

        return $blank;
    }

    /** A tag is taken when it is already a cow, a bull or a recorded calf. */
    private function tagInUse(string $tag): bool
    {
        return DB::table('cows')->where('Eartagno', $tag)->exists()
            || DB::table('bulls')->where('Eartagno', $tag)->exists()
            || DB::table('generations')->where(fn ($q) => $q
                ->where('Calf_Eartagno_1', $tag)->orWhere('Calf_Eartagno_2', $tag)->orWhere('Calf_Eartagno_3', $tag))->exists();
    }

    /** The calf becomes a registered cow or bull, with owner and location taken from its mother. */
    private function registerCalf(string $tag, string $motherTag, ?string $bullTag, array $data): void
    {
        $mother = Cow::findOrFail($motherTag);
        $bull = $bullTag ? $this->bullDetails($bullTag) : ['breed' => '', 'name' => ''];
        $motherBreed = (string) $mother->Breed;
        $bullBreed = (string) $bull['breed'];

        if ($motherBreed !== '' && $motherBreed === $bullBreed && $motherBreed !== 'Cross Breed') {
            $breed = ['Breed' => $motherBreed, 'Cross_breed1' => 'N/A', 'Cross_breed2' => 'N/A'];
        } else {
            $breed = [
                'Breed' => 'Cross Breed',
                'Cross_breed1' => ($motherBreed ?: 'Unknown').' (50%)',
                'Cross_breed2' => ($bullBreed ?: 'Unknown').' (50%)',
            ];
        }

        $attributes = [
            'Eartagno' => $tag,
            'Name' => Str::limit(($data['calf_name'] ?? null) ?: $tag, 20, ''),
            'Gender' => $data['sex'],
            ...$breed,
            'Breed_Percentage' => 100,
            'Dob' => $data['dob'],
            'Number_Calving' => 0,
            'Owner_names' => $mother->Owner_names,
            'National_Id' => $mother->National_Id,
            'Phone' => $mother->Phone,
            'District' => $mother->District,
            'Sector' => $mother->Sector,
            'Cell' => $mother->Cell,
            'Village' => $mother->Village,
        ];

        $data['sex'] === 'Male' ? Bull::create($attributes) : Cow::create($attributes);
    }

    /** @return array{breed: string, name: string} breed and name of a bull, registered here or in the government catalogue */
    private function bullDetails(string $tag): array
    {
        if ($bull = Bull::find($tag)) {
            return ['breed' => (string) $bull->Breed, 'name' => (string) $bull->Name];
        }

        foreach (app(BullsApi::class)->all() as $entry) {
            if ((string) ($entry['Eartag'] ?? '') === $tag) {
                return ['breed' => (string) ($entry['Breed'] ?? ''), 'name' => (string) ($entry['Name'] ?? '')];
            }
        }

        return ['breed' => '', 'name' => ''];
    }

    /** @return array{0: object, 1: array} generation row and the calf stored in slot $n */
    private function slot(string $mother, int $n): array
    {
        abort_unless(in_array($n, [1, 2, 3], true), 404);

        $gen = DB::table('generations')->where('Eartagno', $mother)->first();
        abort_unless($gen && ($gen->{"Calf_Eartagno_$n"} ?? '0') !== '0', 404);

        return [$gen, [
            'mother' => $mother,
            'no' => $n,
            'tag' => (string) $gen->{"Calf_Eartagno_$n"},
            'sire' => (string) $gen->{"Breed_$n"},
            'dob' => (string) $gen->{"Dob_$n"},
            'weight' => (int) $gen->{"Weight_at_Birth_$n"},
            'birth_times' => (int) $gen->{"Birth_Times_$n"},
            'observation' => (string) $gen->{"Observation_$n"},
            'disability' => (string) $gen->{"Disability_$n"},
        ]];
    }

    /** Where the calf was registered as an animal, if anywhere. */
    private function animalOf(string $tag): ?array
    {
        if ($cow = Cow::find($tag)) {
            return ['type' => 'cow', 'name' => $cow->Name, 'route' => 'cows.show', 'permission' => 'cows.view'];
        }
        if ($bull = Bull::find($tag)) {
            return ['type' => 'bull', 'name' => $bull->Name, 'route' => 'bulls.show', 'permission' => 'bulls.view'];
        }

        return null;
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
