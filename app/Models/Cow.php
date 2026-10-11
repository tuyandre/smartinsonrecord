<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cow extends Model
{
    protected $table = 'cows';
    public $timestamps = false;
    protected $guarded = [];
    protected $primaryKey = 'Eartagno';
    public $incrementing = false;
}
