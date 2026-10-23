<?php

namespace App\Models;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SponsorCategory extends Model
{
    use SoftDeletes, Auditable, HasFactory;

    public $table = 'sponsor_categories';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'is_active',
        'sort_order',
        'logo_size',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function sponsors()
    {
        return $this->belongsToMany(Sponsor::class, 'sponsor_sponsor_category')
            ->withTimestamps()
            ->orderBy('sort_order')
            ->orderBy('name');
    }
}
