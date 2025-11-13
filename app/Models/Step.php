<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Step extends Model
{
    protected $fillable = [
        'step_number',
        'step_text',
        'receip_id'
    ];
}
