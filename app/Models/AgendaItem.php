<?php

namespace App\Models;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgendaItem extends Model
{
    use SoftDeletes, Auditable, HasFactory;

    public $table = 'agenda_items';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'event_id',
        'agenda_day_id',
        'agenda_room_id',
        'title',
        'topic_description',
        'start_time',
        'end_time',
        'panel_theme',
        'moderator_name',
        'moderator_speaker_id',
        'moderator_ppt_url',
        'agenda_file_url',
        'sort_order',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function day()
    {
        return $this->belongsTo(AgendaDay::class, 'agenda_day_id');
    }

    public function room()
    {
        return $this->belongsTo(AgendaRoom::class, 'agenda_room_id');
    }

    public function moderatorSpeaker()
    {
        return $this->belongsTo(Speaker::class, 'moderator_speaker_id');
    }

    public function speakers()
    {
        return $this->belongsToMany(Speaker::class, 'agenda_item_speaker', 'agenda_item_id', 'speaker_id')
            ->withPivot('sort_order')
            ->orderBy('agenda_item_speaker.sort_order')
            ->orderBy('agenda_item_speaker.id');
    }
}

