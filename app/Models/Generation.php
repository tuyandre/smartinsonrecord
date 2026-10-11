<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Generation extends Model
{
    protected $table = 'generations';
    public $timestamps = false;
    protected $guarded = [];
    protected $primaryKey = 'Eartagno';
    public $incrementing = false; // legacy table has no primary key; rows are matched on Eartagno
}
