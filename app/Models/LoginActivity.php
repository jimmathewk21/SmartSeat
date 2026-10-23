<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginActivity extends Model
{
    use HasFactory;

    public $table = 'login_activities';

    protected $fillable = [
        'user_id',
        'participant_id',
        'email',
        'status',
        'ip_address',
        'user_agent',
        'attempted_at',
    ];

    protected $dates = [
        'attempted_at',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}
