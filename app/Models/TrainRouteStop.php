<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainRouteStop extends Model
{
    use HasFactory;

    protected $fillable = ['train_id', 'station_id', 'stop_order', 'arrival_offset_minutes', 'departure_offset_minutes'];

    protected function casts(): array
    {
        return ['stop_order' => 'integer', 'arrival_offset_minutes' => 'integer', 'departure_offset_minutes' => 'integer'];
    }

    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }
}
