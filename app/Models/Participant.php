<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    public $table = 'participants';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'mobile',
        'last_login_ip',
        'last_login_at',
        'is_approved',
        'approved_at',
        'train_access_enabled',
    ];

    protected $dates = [
        'last_login_at',
        'approved_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'train_access_enabled' => 'boolean',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loginActivities()
    {
        return $this->hasMany(LoginActivity::class);
    }

}
