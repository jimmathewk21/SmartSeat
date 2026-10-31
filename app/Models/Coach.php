<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coach extends Model
{
    use HasFactory;

    protected $fillable = ['train_id', 'label', 'travel_class'];

    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }
}
