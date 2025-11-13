<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = ['ingredient'];

    public function receips()
    {
        return $this->belongsToMany(Receip::class);
    }
}
