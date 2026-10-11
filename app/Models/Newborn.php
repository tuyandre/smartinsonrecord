<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Newborn extends Model
{
    protected $table = 'newborn';
    public $timestamps = false;
    protected $guarded = [];
    protected $primaryKey = 'New_born_Tagno';
    public $incrementing = false;
}
