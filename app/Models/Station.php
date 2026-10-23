<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Station extends Model
{
    use HasFactory;
    protected $fillable = ['code', 'name', 'city', 'state', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function routeStops()
    {
        return $this->hasMany(TrainRouteStop::class);
    }
}
