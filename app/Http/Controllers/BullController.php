<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesAnimalForm;
use App\Models\Bull;
use App\Services\RwandaAddress;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BullController extends Controller
{
    use HandlesAnimalForm;

    public function index(): View
    {
        return view('pages.bulls.index', ['rows' => Bull::orderBy('Eartagno')->get()]);
    }

    public function create(): View
    {
        return view('pages.bulls.register', ['breeds' => self::BREEDS]);
    }

    public function show(string $eartagno): View
    {
        return view('pages.bulls.show', ['bull' => Bull::findOrFail($eartagno)]);
    }

    public function edit(string $eartagno): View
    {
        return view('pages.bulls.edit', ['bull' => Bull::findOrFail($eartagno), 'breeds' => self::BREEDS]);
    }

    public function store(Request $request, RwandaAddress $address): RedirectResponse
    {
        $data = $request->validate([
            'cow_eartag' => ['required', 'regex:/^[A-Za-z0-9]+$/', 'max:30', 'unique:bulls,Eartagno'],
        ] + $this->animalRules('Male'), $this->animalMessages());

        $this->assertValidLocation($address, $data);

        try {
            Bull::create($this->attributes($data) + ['Eartagno' => $data['cow_eartag']]);
        } catch (QueryException) {
            return back()->withInput()->with('swal', ['title' => 'Error!', 'text' => 'Registration failed due to an error!', 'icon' => 'warning']);
        }

        return redirect()->route('bulls.index')->with('swal', ['title' => 'Success!', 'text' => 'Bull information successfully saved', 'icon' => 'success']);
    }

    public function update(Request $request, RwandaAddress $address, string $eartagno): RedirectResponse
    {
        $bull = Bull::findOrFail($eartagno);
        $data = $request->validate($this->animalRules('Male'), $this->animalMessages());

        $unchanged = [$bull->District, $bull->Sector, $bull->Cell, $bull->Village]
            === [$data['district'], $data['sector'], $data['cell'], $data['village']];
        if (! $unchanged) {
            $this->assertValidLocation($address, $data);
        }

        try {
            $bull->update($this->attributes($data));
        } catch (QueryException) {
            return back()->withInput()->with('swal', ['title' => 'Error!', 'text' => 'Failed to update bull record', 'icon' => 'warning']);
        }

        return redirect()->route('bulls.index')->with('swal', ['title' => 'Success!', 'text' => 'Bull record successfully updated', 'icon' => 'success']);
    }

    public function destroy(string $eartagno): RedirectResponse
    {
        try {
            Bull::findOrFail($eartagno)->delete();
        } catch (QueryException) {
            return redirect()->route('bulls.index')->with('swal', ['title' => 'Error!', 'text' => 'Failed to delete bull information', 'icon' => 'warning']);
        }

        return redirect()->route('bulls.index')->with('swal', ['title' => 'Success!', 'text' => 'Bull record successfully deleted', 'icon' => 'success']);
    }

    /** Table columns for the validated form data. */
    private function attributes(array $data): array
    {
        return [
            'Name' => $data['cow_name'],
            'Gender' => 'Male',
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
        ];
    }
}
