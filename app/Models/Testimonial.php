<?php

namespace App\Models;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use SoftDeletes, Auditable, HasFactory;

    public $table = 'testimonials';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'testimonial_date',
    ];

    protected $fillable = [
        'event_id',
        'country_id',
        'name',
        'photo_url',
        'company',
        'designation',
        'year',
        'message',
        'experience_word',
        'stars',
        'video_id',
        'testimonial_date',
        'mail_image_url',
        'is_active',
        'sort_order',
        'show_on_homepage',
        'show_on_scrollbar',
        'is_key_figure',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'stars' => 'integer',
        'is_active' => 'boolean',
        'show_on_homepage' => 'boolean',
        'show_on_scrollbar' => 'boolean',
        'is_key_figure' => 'boolean',
        'testimonial_date' => 'date',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
