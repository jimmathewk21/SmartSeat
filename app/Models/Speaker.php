<?php

namespace App\Models;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Speaker extends Model
{
    use SoftDeletes, Auditable, HasFactory;

    public $table = 'speakers';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'event_id',
        'name',
        'designation',
        'bio',
        'photo_url',
        'gender',
        'speaker_role',
        'is_active',
        'sort_order',
        'company_name',
        'country',
        'email',
        'phone',
        'show_contact_details',
        'linkedin_url',
        'topic',
        'category',
        'topic_category',
        'biography_type',
        'resource_url',
        'advert_url',
        'presentation_file_url',
        'show_on_homepage',
        'show_on_scrollbar',
        'is_key_figure',
        'show_on_current_conference',
        'show_on_future_conference',
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
        'is_key_figure' => 'boolean',
        'show_on_current_conference' => 'boolean',
        'show_on_future_conference' => 'boolean',
        'show_contact_details' => 'boolean',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function agendaItems()
    {
        return $this->belongsToMany(AgendaItem::class, 'agenda_item_speaker', 'speaker_id', 'agenda_item_id')
            ->withPivot('sort_order')
            ->orderBy('agenda_item_speaker.sort_order')
            ->orderBy('agenda_item_speaker.id');
    }

    public function moderatedAgendaItems()
    {
        return $this->hasMany(AgendaItem::class, 'moderator_speaker_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function scopeOrderedForDisplay($query)
    {
        return $query
            ->orderByDesc('is_key_figure')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function getDisplayPhotoUrlAttribute(): string
    {
        $photoUrl = trim((string) ($this->photo_url ?? ''));

        if ($photoUrl !== '') {
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

        return match (trim((string) ($this->gender ?? ''))) {
            'male' => asset('images/male-worker.png'),
            'female' => asset('images/female-worker.png'),
            default => asset('images/team/1.webp'),
        };
    }
}
