<?php

namespace App\Models;

use App\Services\MyContactStarService;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MyContact extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DEFAULT = 'default';
    public const STATUS_KNOWN = 'known';
    public const STATUS_UNSUBSCRIBED = 'unsubscribed';
    public const STATUS_BLACKLISTED = 'blacklisted';

    public $table = 'my_contacts';

    protected $fillable = [
        'picture_url',
        'gender',
        'name',
        'designation',
        'company_name',
        'official_email',
        'personal_email',
        'alternate_email',
        'mobile_number',
        'whatsapp_number',
        'alternate_mobile_number',
        'country',
        'category',
        'specialization',
        'experience',
        'role',
        'speaker_tag',
        'event_name',
        'event_names',
        'is_subscribed',
        'stars',
        'linkedin_profile',
        'web_url',
        'is_whitelisted',
        'is_blacklisted',
        'contact_status',
        'is_published',
        'show_email',
        'show_whatsapp',
        'show_linkedin',
        'show_website',
        'show_category',
        'show_expertise',
    ];

    protected $casts = [
        'is_subscribed' => 'boolean',
        'stars' => 'integer',
        'is_whitelisted' => 'boolean',
        'is_blacklisted' => 'boolean',
        'is_published' => 'boolean',
        'show_email' => 'boolean',
        'show_whatsapp' => 'boolean',
        'show_linkedin' => 'boolean',
        'show_website' => 'boolean',
        'show_category' => 'boolean',
        'show_expertise' => 'boolean',
    ];

    public function getStatusLabelAttribute(): string
    {
        return match ($this->contact_status) {
            self::STATUS_KNOWN => 'Known Contact',
            self::STATUS_UNSUBSCRIBED => 'Unsubscribed',
            self::STATUS_BLACKLISTED => 'Blacklisted',
            default => 'Default',
        };
    }

    public function getEventListAttribute(): array
    {
        $raw = $this->event_names;
        if (! $raw) {
            return [];
        }

        return collect(preg_split('/[\r\n,]+/', (string) $raw))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique(fn (string $item) => mb_strtolower($item))
            ->values()
            ->all();
    }

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected static function booted(): void
    {
        static::saving(function (MyContact $contact): void {
            $contact->stars = app(MyContactStarService::class)->calculate($contact);
        });
    }
}
