<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrainWaitlistPassenger extends Model
{
    use HasFactory;
    protected $fillable = ['train_waitlist_entry_id', 'name', 'age', 'gender', 'requires_accessible_seat'];

    protected $casts = ['requires_accessible_seat' => 'boolean', 'age' => 'integer'];

    public function entry()
    {
        return $this->belongsTo(TrainWaitlistEntry::class, 'train_waitlist_entry_id');
    }
}
