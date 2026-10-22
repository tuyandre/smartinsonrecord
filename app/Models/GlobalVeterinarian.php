<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalVeterinarian extends Model
{
    protected $table = 'global_veterinarians';
    protected $guarded = [];
    protected $casts = ['date_of_birth' => 'date', 'is_active' => 'boolean'];

    /** True when a user account already exists for this RCVD ID. */
    public function hasAccount(): bool
    {
        return User::where('rcvd_id', $this->rcvd_id)->exists();
    }
}
