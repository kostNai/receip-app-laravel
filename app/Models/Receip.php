<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receip extends Model
{
    protected $fillable = [
        'title',
        'text',
        'rating',
        'user_id',
        'category_id'
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(Step::class);
    }
    public function stats(): HasMany
    {
        return $this->hasMany(Stat::class);
    }
    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
