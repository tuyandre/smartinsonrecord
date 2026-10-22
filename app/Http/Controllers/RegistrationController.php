<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesAnimalForm;
use App\Models\Cow;
use App\Models\Generation;
use App\Models\InseminatedCow;
use App\Models\User;
use App\Services\BullsApi;
use App\Services\RwandaAddress;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    use HandlesAnimalForm;

    public function index(Request $request, BullsApi $bulls): View
    {
        $pattern = trim((string) $request->query('pattern', ''));

        $searchResults = null;
        if ($pattern !== '') {
            $searchResults = DB::table('cows as c')
                ->leftJoin('generations as g', 'c.Eartagno', '=', 'g.Eartagno')
                ->where('c.Eartagno', 'like', '%'.addcslashes($pattern, '\\%_').'%')
                ->select('c.*', 'g.Calf_Eartagno_1', 'g.Breed_1', 'g.Dob_1', 'g.Weight_at_Birth_1', 'g.Birth_Times_1', 'g.Observation_1', 'g.Disability_1', 'g.Calf_Eartagno_2', 'g.Breed_2', 'g.Dob_2', 'g.Weight_at_Birth_2', 'g.Birth_Times_2', 'g.Observation_2', 'g.Disability_2', 'g.Calf_Eartagno_3', 'g.Breed_3', 'g.Dob_3', 'g.Weight_at_Birth_3', 'g.Birth_Times_3', 'g.Observation_3', 'g.Disability_3')
                ->get();
        }

        return view('pages.registration.index', [
            'pattern' => $pattern,
            'searchResults' => $searchResults,
            'totalInseminated' => InseminatedCow::count(),
            'totalVets' => User::count(),
            'bullCount' => $bulls->count(),
            'bullNames' => $bulls->names(),
            'breeds' => self::BREEDS,
        ]);
    }


    public function store(Request $request, RwandaAddress $address): RedirectResponse
    {
        $data = $request->validate([
            'cow_eartag' => ['required', 'regex:/^[A-Za-z0-9]+$/', 'max:30', 'unique:cows,Eartagno'],
        ] + $this->animalRules('Female'), $this->animalMessages());

        $this->assertValidLocation($address, $data);

        $breed = $this->breedColumns($data);

        try {
            Cow::create([
                'Name' => $data['cow_name'],
                'Eartagno' => $data['cow_eartag'],
                'Gender' => 'Female',
                ...$breed,
                'Breed_Percentage' => 100,
                'Dob' => $data['dob'],
                'Number_Calving' => (int) ($data['calves'] ?? 0),
                'Owner_names' => $data['owner_name'],
                'National_Id' => $data['nid'],
                'Phone' => $data['Phone'],
                'District' => $data['district'],
                'Sector' => $data['sector'],
                'Cell' => $data['cell'],
                'Village' => $data['village'],
            ]);
        } catch (QueryException) {
            return back()->withInput()->with('swal', ['title' => 'Error!', 'text' => 'Registration failed due to an error!', 'icon' => 'warning']);
        }

        return redirect()->route('records.cows')->with('swal', ['title' => 'Success!', 'text' => 'Cow information successfully saved', 'icon' => 'success']);
    }

    public function update(Request $request, RwandaAddress $address, string $eartagno): RedirectResponse
    {
        $cow = Cow::findOrFail($eartagno);

        $data = $request->validate($this->animalRules('Female'), $this->animalMessages());

        $unchanged = [$cow->District, $cow->Sector, $cow->Cell, $cow->Village]
            === [$data['district'], $data['sector'], $data['cell'], $data['village']];
        if (! $unchanged) {
            $this->assertValidLocation($address, $data);
        }

        try {
            $cow->update([
                'Name' => $data['cow_name'],
                'Gender' => 'Female',
                ...$this->breedColumns($data),
                'Breed_Percentage' => 100,
                'Dob' => $data['dob'],
                'Number_Calving' => (int) ($data['calves'] ?? 0),
                'Owner_names' => $data['owner_name'],
                'National_Id' => $data['nid'],
                'Phone' => $data['Phone'],
                'District' => $data['district'],
                'Sector' => $data['sector'],
                'Cell' => $data['cell'],
                'Village' => $data['village'],
            ]);
        } catch (QueryException) {
            return back()->withInput()->with('swal', ['title' => 'Error!', 'text' => 'Failed to update cow record', 'icon' => 'warning']);
        }

        return redirect()->route('records.cows')->with('swal', ['title' => 'Success!', 'text' => 'Cow record successfully updated', 'icon' => 'success']);
    }

    public function destroy(string $eartagno): RedirectResponse
    {
        $cow = Cow::findOrFail($eartagno);

        try {
            $cow->delete();
        } catch (QueryException) {
            return redirect()->route('records.cows')->with('swal', ['title' => 'Error!', 'text' => 'Failed to delete cow Information', 'icon' => 'warning']);
        }

        return redirect()->route('records.cows')->with('swal', ['title' => 'Success!', 'text' => 'Cow record successfully deleted', 'icon' => 'success']);
    }

    public function check(Request $request): JsonResponse
    {
        $eartagno = $request->validate(['eartagno' => ['required', 'regex:/^[A-Za-z0-9]+$/', 'max:30']])['eartagno'];

        $generation = Generation::where('Calf_Eartagno_1', $eartagno)
            ->orWhere('Calf_Eartagno_2', $eartagno)
            ->orWhere('Calf_Eartagno_3', $eartagno)
            ->first();

        $dob = null;
        if ($generation) {
            foreach ([1, 2, 3] as $i) {
                if ((string) $generation->{"Calf_Eartagno_$i"} === (string) $eartagno) {
                    $dob = $generation->{"Dob_$i"};
                    break;
                }
            }
        }

        return response()->json([
            'exists' => Cow::whereKey($eartagno)->exists(),
            'found' => $generation !== null,
            'dob' => $dob,
        ]);
    }

    public function details(Request $request): JsonResponse
    {
        $eartagno = $request->validate(['eartagno' => ['required', 'regex:/^[A-Za-z0-9]+$/', 'max:30']])['eartagno'];

        return response()->json(Cow::find($eartagno));
    }
}
