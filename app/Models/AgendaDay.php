<?php

namespace App\Models;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgendaDay extends Model
{
    use SoftDeletes, Auditable, HasFactory;

    public $table = 'agenda_days';

    protected $dates = [
        'agenda_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'event_id',
        'title',
        'weekday',
        'agenda_date',
        'sort_order',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'agenda_date' => 'date',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function items()
    {
        return $this->hasMany(AgendaItem::class, 'agenda_day_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}

