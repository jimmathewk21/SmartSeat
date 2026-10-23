<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqQuestion extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ANSWERED = 'answered';

    public $table = 'faq_questions';

    protected $fillable = [
        'name',
        'email',
        'question',
        'answer',
        'status',
        'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
