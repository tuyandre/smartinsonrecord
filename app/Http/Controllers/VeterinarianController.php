<?php

namespace App\Http\Controllers;

use App\Models\GlobalVeterinarian;
use App\Models\User;
use App\Services\VeterinarianImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VeterinarianController extends Controller
{
    public function index(): View
    {
        return view('pages.veterinarians.index', [
            'rows' => GlobalVeterinarian::orderBy('rcvd_id')->get(),
            'accounts' => User::pluck('rcvd_id')->map(fn ($id) => strtolower((string) $id))->flip(),
        ]);
    }

    public function create(): View
    {
        return view('pages.veterinarians.create');
    }

    public function show(GlobalVeterinarian $veterinarian): View
    {
        return view('pages.veterinarians.show', ['vet' => $veterinarian, 'account' => User::where('rcvd_id', $veterinarian->rcvd_id)->first()]);
    }

    public function edit(GlobalVeterinarian $veterinarian): View
    {
        return view('pages.veterinarians.edit', ['vet' => $veterinarian]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        GlobalVeterinarian::create($data + ['is_active' => true]);

        return redirect()->route('veterinarians.index')->with('swal', ['title' => 'Success!', 'text' => 'Veterinarian successfully saved', 'icon' => 'success']);
    }

    public function update(Request $request, GlobalVeterinarian $veterinarian): RedirectResponse
    {
        $veterinarian->update($request->validate($this->rules($veterinarian)));

        return redirect()->route('veterinarians.index')->with('swal', ['title' => 'Success!', 'text' => 'Veterinarian successfully updated', 'icon' => 'success']);
    }

    public function destroy(GlobalVeterinarian $veterinarian): RedirectResponse
    {
        $veterinarian->delete();

        return redirect()->route('veterinarians.index')->with('swal', ['title' => 'Success!', 'text' => 'Veterinarian successfully deleted', 'icon' => 'success']);
    }

    /** Activate or deactivate. Deactivated veterinarians stay in the register but are no longer valid. */
    public function toggle(GlobalVeterinarian $veterinarian): RedirectResponse
    {
        $veterinarian->update(['is_active' => ! $veterinarian->is_active]);

        return redirect()->route('veterinarians.index')->with('swal', [
            'title' => 'Success!',
            'text' => $veterinarian->names.($veterinarian->is_active ? ' is active again' : ' has been deactivated'),
            'icon' => 'success',
        ]);
    }

    public function importForm(): View
    {
        return view('pages.veterinarians.import');
    }

    public function import(Request $request, VeterinarianImport $importer): RedirectResponse
    {
        $request->validate(
            ['file' => ['required', 'file', 'max:5120', 'extensions:xlsx,csv']],
            ['file.required' => 'Choose the veterinarians list to import.', 'file.extensions' => 'Only .xlsx or .csv files can be imported.', 'file.max' => 'The file may not be larger than 5 MB.']
        );

        $file = $request->file('file');

        try {
            $result = $importer->import($file->getRealPath(), $file->getClientOriginalExtension());
        } catch (\Throwable) {
            return back()->with('swal', ['title' => 'Import failed', 'text' => 'The file could not be read. Check that it is a valid .xlsx or .csv list.', 'icon' => 'warning']);
        }

        return redirect()->route('veterinarians.import')->with('import_result', $result);
    }

    /** Sample file with the expected headings. */
    public function template()
    {
        $csv = "No,RCVD ID,National ID,Names,date of birth,District,Sector\n1,rw100,1199580010110001,Kabera Eric,11/01/1995,Kicukiro,Gatenga\n";

        return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="veterinarians-template.csv"']);
    }

    private function rules(?GlobalVeterinarian $vet = null): array
    {
        return [
            'rcvd_id' => ['required', 'string', 'max:30', Rule::unique('global_veterinarians', 'rcvd_id')->ignore($vet)],
            'national_id' => ['nullable', 'digits:16', Rule::unique('global_veterinarians', 'national_id')->ignore($vet)],
            'names' => ['required', 'string', 'max:120'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'district' => ['nullable', 'string', 'max:60'],
            'sector' => ['nullable', 'string', 'max:60'],
        ];
    }
}
