<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrainCoach extends Model
{
    use HasFactory;
    protected $fillable = ['train_id', 'code', 'class_code', 'sort_order'];

    public function train()
    {
        return $this->belongsTo(Train::class);
    }

    public function seats()
    {
        return $this->hasMany(TrainSeat::class)->orderBy('number');
    }
}
