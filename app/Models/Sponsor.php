<?php

namespace App\Models;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sponsor extends Model
{
    use SoftDeletes, Auditable, HasFactory;

    public $table = 'sponsors';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'sponsor_category_id',
        'name',
        'sub_category',
        'use_sub_category_text',
        'sub_category_text',
        'website_url',
        'linkedin_url',
        'logo_url',
        'logo_size',
        'is_active',
        'sort_order',
        'detail',
        'asset_type',
        'resource_url',
        'show_on_homepage',
        'show_on_scrollbar',
        'show_contact_details',
        'show_website_url',
        'show_linkedin_url',
        'show_bio',
        'show_advert',
        'contact_name',
        'contact_designation',
        'contact_mobile',
        'contact_email',
        'advert_url',
        'brochure_url',
        'sponsor_video_url',
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
        'is_active' => 'boolean',
        'show_on_homepage' => 'boolean',
        'show_on_scrollbar' => 'boolean',
        'use_sub_category_text' => 'boolean',
        'show_contact_details' => 'boolean',
        'show_website_url' => 'boolean',
        'show_linkedin_url' => 'boolean',
        'show_bio' => 'boolean',
        'show_advert' => 'boolean',
    ];

    protected $appends = [
        'display_logo_url',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function sponsorCategory()
    {
        return $this->belongsTo(SponsorCategory::class, 'sponsor_category_id');
    }

    public function sponsorCategories()
    {
        return $this->belongsToMany(SponsorCategory::class, 'sponsor_sponsor_category')
            ->withTimestamps()
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function getDisplayLogoUrlAttribute(): string
    {
        $logoUrl = trim((string) ($this->logo_url ?? ''));

        if ($logoUrl === '') {
            return '';
        }

        if (
            str_starts_with($logoUrl, 'http://')
            || str_starts_with($logoUrl, 'https://')
            || str_starts_with($logoUrl, '//')
            || str_starts_with($logoUrl, '/')
        ) {
            return $logoUrl;
        }

        return asset(ltrim($logoUrl, '/'));
    }
}
