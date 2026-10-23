<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrainSeat extends Model
{
    use HasFactory;
    protected $fillable = ['train_id', 'train_coach_id', 'number', 'berth_type', 'is_accessible'];

    protected $casts = ['is_accessible' => 'boolean'];

    public function train()
    {
        return $this->belongsTo(Train::class);
    }

    public function coach()
    {
        return $this->belongsTo(TrainCoach::class, 'train_coach_id');
    }

    public function inventories()
    {
        return $this->hasMany(TripSeatInventory::class, 'train_seat_id');
    }
}
