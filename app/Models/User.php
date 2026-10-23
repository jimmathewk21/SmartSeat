<?php

namespace App\Models;

use App\Notifications\QueuedResetPassword;
use App\Notifications\QueuedVerifyEmail;
use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use DateTimeInterface;

class User extends Authenticatable
{
    use SoftDeletes, Notifiable, Auditable, HasFactory, HasApiTokens;

    public $table = 'users';

    protected $hidden = [
        'remember_token',
        'password',
        'updated_at',
        'deleted_at',
    ];

    protected $dates = [
        'email_verified_at',
        'train_owner_approved_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'mobile',
        'company_name',
        'profile_picture',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        // 'user_type',
        // 'verification_status',
        // 'company_name',
        // 'profile_picture',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getIsAdminAttribute()
    {
        return $this->roles()->where('id', 1)->exists();
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        $photoUrl = trim((string) ($this->profile_picture ?? ''));

        if ($photoUrl === '') {
            return '';
        }

        if (
            str_starts_with($photoUrl, 'http://')
            || str_starts_with($photoUrl, 'https://')
            || str_starts_with($photoUrl, '//')
            || str_starts_with($photoUrl, '/')
        ) {
            return $photoUrl;
        }

        return asset(ltrim($photoUrl, '/'));
    }

    public static function boot()
    {
        parent::boot();
        self::observe(new \App\Observers\UserActionObserver);
    }

    public function getEmailVerifiedAtAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('panel.date_format') . ' ' . config('panel.time_format')) : null;
    }

    public function setEmailVerifiedAtAttribute($value)
    {
        $this->attributes['email_verified_at'] = $value ? Carbon::createFromFormat(config('panel.date_format') . ' ' . config('panel.time_format'), $value)->format('Y-m-d H:i:s') : null;
    }

    public function setPasswordAttribute($input)
    {
        if ($input) {
            $this->attributes['password'] = app('hash')->needsRehash($input) ? Hash::make($input) : $input;
        }
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new QueuedResetPassword($token));
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new QueuedVerifyEmail());
    }

    // === Relationships ===

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'client_id');
    }

    public function trainBookings()
    {
        return $this->hasMany(TrainBooking::class, 'user_id');
    }

    public function trainWaitlistEntries()
    {
        return $this->hasMany(TrainWaitlistEntry::class, 'user_id');
    }

    public function ownedTrains()
    {
        return $this->hasMany(Train::class, 'owner_user_id');
    }

    public function hasApprovedTrainOwnerAccess(string $permission = 'train_management_access'): bool
    {
        return ! empty($this->train_owner_approved_at)
            && $this->roles()
                ->where('title', 'Train Owner')
                ->whereHas('permissions', fn ($query) => $query->where('title', $permission))
                ->exists();
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'user_id');
    }

    public function hasRole($role)
    {
        return in_array($role, $this->roles->pluck('title')->toArray());
    }

    public function participant()
    {
        return $this->hasOne(Participant::class);
    }

    public function loginActivities()
    {
        return $this->hasMany(LoginActivity::class);
    }

    public function latestLoginActivity()
    {
        return $this->hasOne(LoginActivity::class)->latestOfMany('attempted_at');
    }
}
