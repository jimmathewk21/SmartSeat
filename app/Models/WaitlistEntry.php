<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaitlistEntry extends Model
{
    use HasFactory;

    protected $fillable = ['passenger_id', 'trip_id', 'travel_class', 'position', 'status', 'cancelled_at', 'promoted_at'];

    protected function casts(): array
    {
        return ['cancelled_at' => 'immutable_datetime', 'promoted_at' => 'immutable_datetime', 'position' => 'integer'];
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(TrainTrip::class, 'trip_id');
    }
}
