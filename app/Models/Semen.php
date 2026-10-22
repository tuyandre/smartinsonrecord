<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semen extends Model
{
    protected $table = 'semens';
    protected $guarded = [];

    public function bull(): BelongsTo
    {
        return $this->belongsTo(Bull::class, 'bull_eartag', 'Eartagno');
    }

    public function lots(): HasMany
    {
        return $this->hasMany(SemenLot::class)->orderBy('id');
    }

    /** Same shape as a row of the Semen Info report (and of the government catalogue). */
    public function toReportRow(): array
    {
        $row = [
            'Eartag' => $this->bull_eartag,
            'Breed' => $this->bull?->Breed,
            'Dob' => $this->bull?->Dob,
            'Name' => $this->bull?->Name,
        ];

        foreach ($this->lots->values() as $i => $lot) {
            $n = $i + 1;
            // The report keeps the catalogue's own (inconsistent) key spelling.
            $row[$n === 3 ? 'registration__code_3' : "registration_no_$n"] = $lot->registration_no;
            $row["freezing_date_$n"] = $lot->freezing_date->format('d/m/Y');
            $row[$n === 1 ? 'Semen_code_1' : "semen_code_$n"] = $lot->semen_code;
        }

        return $row + ['international_code' => $this->international_code, 'aic_no' => $this->aic_no, 'bull_no' => $this->bull_no];
    }
}
