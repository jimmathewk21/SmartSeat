<?php

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes, Auditable, HasFactory;

    public $table = 'events';

    protected $dates = [
        'start_at',
        'end_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'title',
        'slug',
        'venue',
        'location',
        'start_at',
        'end_at',
        'description',
        'image_url',
        'gallery_url',
        'brochure_url',
        'ticket_hero_label',
        'ticket_hero_suffix',
        'ticket_hero_description',
        'is_published',
        'is_active',
        'sort_order',
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
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_published' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeCurrentOrUpcoming(Builder $query, $reference = null): Builder
    {
        $reference = $reference instanceof CarbonInterface ? $reference : ($reference ? Carbon::parse($reference) : now());

        return $query->where(function (Builder $builder) use ($reference) {
            $builder
                ->where(function (Builder $sub) use ($reference) {
                    $sub->whereNotNull('end_at')->where('end_at', '>=', $reference);
                })
                ->orWhere(function (Builder $sub) use ($reference) {
                    $sub->whereNull('end_at')
                        ->whereNotNull('start_at')
                        ->where('start_at', '>=', $reference);
                });
        });
    }

    public function scopeBookableUpcoming(Builder $query, $reference = null): Builder
    {
        $reference = $reference instanceof CarbonInterface ? $reference : ($reference ? Carbon::parse($reference) : now());

        return $query->published()
            ->whereNotNull('start_at')
            ->where('start_at', '>', $reference);
    }

    public function isCurrentOrUpcoming($reference = null): bool
    {
        $reference = $reference instanceof CarbonInterface ? $reference : ($reference ? Carbon::parse($reference) : now());

        if ($this->end_at) {
            return $this->end_at->greaterThanOrEqualTo($reference);
        }

        return $this->start_at ? $this->start_at->greaterThanOrEqualTo($reference) : false;
    }

    public function isBookableUpcoming($reference = null): bool
    {
        $reference = $reference instanceof CarbonInterface ? $reference : ($reference ? Carbon::parse($reference) : now());

        return $this->is_published
            && $this->start_at
            && $this->start_at->greaterThan($reference);
    }

    public function speakers()
    {
        return $this->hasMany(Speaker::class, 'event_id')->orderBy('sort_order')->orderBy('name');
    }

    public function ticketPlans()
    {
        return $this->hasMany(EventTicketPlan::class, 'event_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function bookings()
    {
        return $this->hasMany(EventBooking::class, 'event_id');
    }

    public function agendaDays()
    {
        return $this->hasMany(AgendaDay::class, 'event_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function agendaRooms()
    {
        return $this->hasMany(AgendaRoom::class, 'event_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function agendaItems()
    {
        return $this->hasMany(AgendaItem::class, 'event_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
