<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pd extends Model
{
    protected $table = 'pd';
    public $timestamps = false;
    protected $guarded = [];
    protected $primaryKey = 'pd_id';
}
