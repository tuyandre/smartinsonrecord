<?php

namespace App\Http\Controllers\Concerns;

use App\Services\RwandaAddress;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Validation and breed handling shared by the cow and bull registration forms. */
trait HandlesAnimalForm
{
    public const BREEDS = ['Ankole', 'Fresian', 'Browns Swiss', 'Jersey', 'Sahiwar'];

    /** Rules shared by the register and edit forms (the ear tag is validated separately on register). */
    private function animalRules(string $sex): array
    {
        return [
            'cow_name' => ['required', 'string', 'max:20'],
            'gender' => ['nullable', 'in:'.$sex],
            'breed_identification' => ['required', 'in:single,cross'],
            'breed' => ['required_if:breed_identification,single', 'nullable', 'string', Rule::in(self::BREEDS)],
            'cross_breeds' => ['required_if:breed_identification,cross', 'nullable', 'array', 'min:2', 'max:'.count(self::BREEDS)],
            'cross_breeds.*.breed' => ['required', 'string', 'distinct', Rule::in(self::BREEDS)],
            'cross_breeds.*.percentage' => ['required', 'integer', 'min:1', 'max:100'],
            'dob' => ['required', 'date'],
            'calves' => ['nullable', 'integer', 'min:0'],
            'owner_name' => ['required', 'string', 'max:25'],
            'nid' => ['required', 'digits_between:1,16'],
            'Phone' => ['required', 'digits_between:1,15'],
            'district' => ['required', 'string', 'max:25', 'not_in:--'],
            'sector' => ['required', 'string', 'max:25', 'not_in:--'],
            'cell' => ['required', 'string', 'max:25', 'not_in:--'],
            'village' => ['required', 'string', 'max:25', 'not_in:--'],
        ];
    }

    private function animalMessages(): array
    {
        return [
            'cow_eartag.unique' => 'Ear tag already exists',
            'cow_eartag.regex' => 'Ear tag may contain only letters and digits.',
            'breed.required_if' => 'Choose the breed.',
            'cross_breeds.required_if' => 'Add at least two breeds for a cross breed.',
            'cross_breeds.min' => 'A cross breed needs at least two breeds.',
            'cross_breeds.*.breed.required' => 'Choose a breed for every row.',
            'cross_breeds.*.breed.distinct' => 'Each breed can only be used once.',
            'cross_breeds.*.percentage.required' => 'Enter a percentage for every breed.',
            'district.not_in' => 'Please choose a district',
            'sector.not_in' => 'Please choose a sector',
            'cell.not_in' => 'Please choose a cell',
            'village.not_in' => 'Please choose a village',
        ];
    }

    /** Breed, Cross_breed1 and Cross_breed2 column values for the submitted breed choice. */
    private function breedColumns(array $data): array
    {
        if ($data['breed_identification'] !== 'cross') {
            return ['Breed' => $data['breed'] ?? '', 'Cross_breed1' => 'N/A', 'Cross_breed2' => 'N/A'];
        }

        if (collect($data['cross_breeds'])->sum('percentage') !== 100) {
            throw ValidationException::withMessages(['cross_breeds' => 'The breed percentages must add up to exactly 100%.']);
        }
        $parts = collect($data['cross_breeds'])->map(fn ($row) => "{$row['breed']} ({$row['percentage']}%)")->values();

        return ['Breed' => 'Cross Breed', 'Cross_breed1' => $parts->first(), 'Cross_breed2' => $parts->slice(1)->implode(', ')];
    }

    /** Rejects district/sector/cell/village combinations that are not in Rwanda's address list. */
    private function assertValidLocation(RwandaAddress $address, array $data): void
    {
        if (! $address->isValid($data['district'], $data['sector'], $data['cell'], $data['village'])) {
            throw ValidationException::withMessages(['village' => 'Please choose a valid district, sector, cell and village.']);
        }
    }
}
