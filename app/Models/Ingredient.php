<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ingredient extends Model
{
    protected $fillable = ['ingredient', 'receip_id'];

    public function receip(): HasOne
    {
        return $this->hasOne(Receip::class);
    }
}
