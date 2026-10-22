<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SemenLot extends Model
{
    protected $table = 'semen_lots';
    protected $guarded = [];
    protected $casts = ['freezing_date' => 'date'];

    public function semen(): BelongsTo
    {
        return $this->belongsTo(Semen::class);
    }
}
