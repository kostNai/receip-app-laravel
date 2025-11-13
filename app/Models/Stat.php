<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    protected $fillable = [
        'prep_time',
        'cooking_time',
        'yields',
        'receip_id'
    ];
}
