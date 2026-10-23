<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Train extends Model
{
    use HasFactory;
    protected $fillable = ['code', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function routeStops()
    {
        return $this->hasMany(TrainRouteStop::class)->orderBy('stop_order');
    }

    public function coaches()
    {
        return $this->hasMany(TrainCoach::class)->orderBy('sort_order')->orderBy('id');
    }

    public function seats()
    {
        return $this->hasMany(TrainSeat::class);
    }

    public function trips()
    {
        return $this->hasMany(TrainTrip::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
}
