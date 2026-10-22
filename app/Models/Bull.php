<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bull extends Model
{
    protected $table = 'bulls';
    public $timestamps = false;
    protected $guarded = [];
    protected $primaryKey = 'Eartagno';
    protected $keyType = 'string';
    public $incrementing = false;
}
