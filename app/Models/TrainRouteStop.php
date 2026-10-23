<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrainRouteStop extends Model
{
    use HasFactory;
    protected $fillable = ['train_id', 'station_id', 'stop_order', 'arrival_offset_minutes', 'departure_offset_minutes'];

    protected $casts = ['stop_order' => 'integer', 'arrival_offset_minutes' => 'integer', 'departure_offset_minutes' => 'integer'];

    public function train()
    {
        return $this->belongsTo(Train::class);
    }

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}
