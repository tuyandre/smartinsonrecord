<?php

namespace App\Http\Controllers;

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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegistrationController extends Controller
{
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
            'cows' => Cow::orderBy('Eartagno')->get(),
            'pattern' => $pattern,
            'searchResults' => $searchResults,
            'totalInseminated' => InseminatedCow::count(),
            'totalVets' => User::count(),
            'bullCount' => $bulls->count(),
            'bullNames' => $bulls->names(),
            'breeds' => ['Ankole', 'Fresian', 'Browns Swiss', 'Jersey', 'Sahiwar'],
        ]);
    }

    public function store(Request $request, RwandaAddress $address): RedirectResponse
    {
        $data = $request->validate([
            'cow_name' => ['required', 'string', 'max:20'],
            'cow_eartag' => ['required', 'integer', 'min:1', 'max:2147483647', 'unique:cows,Eartagno'],
            'gender' => ['nullable', 'string', 'max:10'],
            'breed' => ['nullable', 'string', 'max:25'],
            'cross_breed1' => ['nullable', 'string', 'max:20'],
            'cross_breed1_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'cross_breed2' => ['nullable', 'string', 'max:20'],
            'cross_breed2_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'dob' => ['required', 'date'],
            'calves' => ['nullable', 'integer', 'min:0'],
            'owner_name' => ['required', 'string', 'max:25'],
            'nid' => ['required', 'digits_between:1,16'],
            'Phone' => ['required', 'digits_between:1,15'],
            'district' => ['required', 'string', 'max:25', 'not_in:--'],
            'sector' => ['required', 'string', 'max:25', 'not_in:--'],
            'cell' => ['required', 'string', 'max:25', 'not_in:--'],
            'village' => ['required', 'string', 'max:25', 'not_in:--'],
        ], [
            'cow_eartag.unique' => 'Cow already exists',
            'district.not_in' => 'Please choose a district',
            'sector.not_in' => 'Please choose a sector',
            'cell.not_in' => 'Please choose a cell',
            'village.not_in' => 'Please choose a village',
        ]);

        $this->assertValidLocation($address, $data);

        $percentage1 = (int) ($data['cross_breed1_percentage'] ?? 0);
        $percentage2 = (int) ($data['cross_breed2_percentage'] ?? 0);
        $crossBreed1 = $data['cross_breed1'] ?? '';
        $crossBreed2 = $data['cross_breed2'] ?? '';

        try {
            Cow::create([
                'Name' => $data['cow_name'],
                'Eartagno' => $data['cow_eartag'],
                'Gender' => $data['gender'] ?? 'Female',
                'Breed' => $data['breed'] ?? '',
                'Cross_breed1' => $crossBreed1 !== '' ? "$crossBreed1 ($percentage1%)" : 'N/A',
                'Cross_breed2' => $crossBreed2 !== '' ? "$crossBreed2 ($percentage2%)" : 'N/A',
                'Breed_Percentage' => $percentage1 + $percentage2,
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

        return redirect()->route('registration.index')->with('swal', ['title' => 'Success!', 'text' => 'Cow information successfully saved', 'icon' => 'success']);
    }

    public function update(Request $request, RwandaAddress $address, int $eartagno): RedirectResponse
    {
        $cow = Cow::findOrFail($eartagno);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:20'],
            'gender' => ['required', 'string', 'max:10'],
            'breed' => ['nullable', 'string', 'max:25'],
            'cross_breed1' => ['nullable', 'string', 'max:20'],
            'cross_breed2' => ['nullable', 'string', 'max:20'],
            'breed_percentage' => ['required', 'integer', 'min:0', 'max:200'],
            'dob' => ['required', 'string', 'max:25'],
            'number_calving' => ['required', 'integer', 'min:0'],
            'owner_names' => ['required', 'string', 'max:25'],
            'national_id' => ['required', 'digits_between:1,16'],
            'phone' => ['required', 'digits_between:1,15'],
            'district' => ['required', 'string', 'max:25'],
            'sector' => ['required', 'string', 'max:25'],
            'cell' => ['required', 'string', 'max:25'],
            'village' => ['required', 'string', 'max:25'],
        ]);

        $unchanged = [$cow->District, $cow->Sector, $cow->Cell, $cow->Village]
            === [$data['district'], $data['sector'], $data['cell'], $data['village']];
        if (! $unchanged) {
            $this->assertValidLocation($address, $data);
        }

        try {
            $cow->update([
                'Name' => $data['name'],
                'Gender' => $data['gender'],
                'Breed' => $data['breed'] ?? '',
                'Cross_breed1' => $data['cross_breed1'] ?? '',
                'Cross_breed2' => $data['cross_breed2'] ?? '',
                'Breed_Percentage' => $data['breed_percentage'],
                'Dob' => $data['dob'],
                'Number_Calving' => $data['number_calving'],
                'Owner_names' => $data['owner_names'],
                'National_Id' => $data['national_id'],
                'Phone' => $data['phone'],
                'District' => $data['district'],
                'Sector' => $data['sector'],
                'Cell' => $data['cell'],
                'Village' => $data['village'],
            ]);
        } catch (QueryException) {
            return back()->with('swal', ['title' => 'Error!', 'text' => 'Failed to update cow record', 'icon' => 'warning']);
        }

        return redirect()->route('registration.index')->with('swal', ['title' => 'Success!', 'text' => 'Cow record successfully updated', 'icon' => 'success']);
    }

    public function destroy(int $eartagno): RedirectResponse
    {
        $cow = Cow::findOrFail($eartagno);

        try {
            $cow->delete();
        } catch (QueryException) {
            return redirect()->route('registration.index')->with('swal', ['title' => 'Error!', 'text' => 'Failed to delete cow Information', 'icon' => 'warning']);
        }

        return redirect()->route('registration.index')->with('swal', ['title' => 'Success!', 'text' => 'Cow record successfully deleted', 'icon' => 'success']);
    }

    public function check(Request $request): JsonResponse
    {
        $eartagno = $request->validate(['eartagno' => ['required', 'integer']])['eartagno'];

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
        $eartagno = $request->validate(['eartagno' => ['required', 'integer']])['eartagno'];

        return response()->json(Cow::find($eartagno));
    }

    /** Rejects district/sector/cell/village combinations that are not in Rwanda's address list. */
    private function assertValidLocation(RwandaAddress $address, array $data): void
    {
        if (! $address->isValid($data['district'], $data['sector'], $data['cell'], $data['village'])) {
            throw ValidationException::withMessages(['village' => 'Please choose a valid district, sector, cell and village.']);
        }
    }
}
