<?php

namespace App\Http\Controllers;

use App\Models\Bull;
use App\Models\Semen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SemenController extends Controller
{
    private const MAX_LOTS = 5;

    public function index(): View
    {
        return view('pages.semen.index', ['rows' => Semen::with(['bull', 'lots'])->orderBy('bull_eartag')->get()]);
    }

    public function create(): View
    {
        $taken = Semen::pluck('bull_eartag');

        return view('pages.semen.register', [
            'bulls' => Bull::whereNotIn('Eartagno', $taken)->orderBy('Name')->get(['Eartagno', 'Name']),
            'maxLots' => self::MAX_LOTS,
        ]);
    }

    public function show(Semen $semen): View
    {
        return view('pages.semen.show', ['semen' => $semen->load(['bull', 'lots'])]);
    }

    public function edit(Semen $semen): View
    {
        return view('pages.semen.edit', ['semen' => $semen->load(['bull', 'lots']), 'maxLots' => self::MAX_LOTS]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bull_eartag' => ['required', 'exists:bulls,Eartagno', 'unique:semens,bull_eartag'],
        ] + $this->rules(), $this->messages());

        DB::transaction(function () use ($data) {
            $semen = Semen::create($this->info($data) + ['bull_eartag' => $data['bull_eartag']]);
            $semen->lots()->createMany($data['lots']);
        });

        return redirect()->route('semen.index')->with('swal', ['title' => 'Success!', 'text' => 'Semen information successfully saved', 'icon' => 'success']);
    }

    public function update(Request $request, Semen $semen): RedirectResponse
    {
        $data = $request->validate($this->rules($semen), $this->messages());

        DB::transaction(function () use ($semen, $data) {
            $semen->update($this->info($data));
            $semen->lots()->delete();
            $semen->lots()->createMany($data['lots']);
        });

        return redirect()->route('semen.index')->with('swal', ['title' => 'Success!', 'text' => 'Semen information successfully updated', 'icon' => 'success']);
    }

    public function destroy(Semen $semen): RedirectResponse
    {
        $semen->delete();

        return redirect()->route('semen.index')->with('swal', ['title' => 'Success!', 'text' => 'Semen record successfully deleted', 'icon' => 'success']);
    }

    private function rules(?Semen $semen = null): array
    {
        return [
            'international_code' => ['nullable', 'string', 'max:30'],
            'aic_no' => ['nullable', 'string', 'max:30'],
            'bull_no' => ['nullable', 'string', 'max:30'],
            'lots' => ['required', 'array', 'min:1', 'max:'.self::MAX_LOTS],
            'lots.*.registration_no' => ['required', 'string', 'max:30'],
            'lots.*.freezing_date' => ['required', 'date'],
            'lots.*.semen_code' => [
                'required', 'string', 'max:20', 'distinct',
                Rule::unique('semen_lots', 'semen_code')->when($semen, fn ($rule) => $rule->where(fn ($q) => $q->where('semen_id', '!=', $semen->id))),
            ],
        ];
    }

    private function messages(): array
    {
        return [
            'bull_eartag.unique' => 'Semen information for this bull already exists. Edit it instead.',
            'lots.required' => 'Add at least one semen batch.',
            'lots.*.registration_no.required' => 'Enter a registration number for every batch.',
            'lots.*.freezing_date.required' => 'Enter a freezing date for every batch.',
            'lots.*.semen_code.required' => 'Enter a semen code for every batch.',
            'lots.*.semen_code.distinct' => 'Each semen code can only be used once.',
            'lots.*.semen_code.unique' => 'This semen code is already registered.',
        ];
    }

    private function info(array $data): array
    {
        return [
            'international_code' => $data['international_code'] ?? null,
            'aic_no' => $data['aic_no'] ?? null,
            'bull_no' => $data['bull_no'] ?? null,
        ];
    }
}
