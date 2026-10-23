<?php

namespace App\Support;

use App\Models\Setting;

class AgendaPageSettings
{
    public const SETTING_KEY = 'agenda_page';

    public static function defaults(): array
    {
        return [
            'event_id' => null,
            'subtitle' => 'Schedule Details',
            'title' => 'Information of Event Schedules',
            'small_description' => 'World is committed to making participation in the event a harassment free experience for everyone. Manage topics and PPT files from admin.',
            'theme_color' => 'blue',
            'agenda_download_text' => 'Download Agenda',
            'agenda_download_url' => '#',
        ];
    }

    public static function get(): array
    {
        $raw = Setting::query()->where('key', self::SETTING_KEY)->value('value');
        if (! $raw) {
            return self::defaults();
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return self::defaults();
        }

        if (! is_array($decoded)) {
            return self::defaults();
        }

        $data = array_merge(self::defaults(), $decoded);
        $data['theme_color'] = 'blue';
        $data['agenda_download_url'] = FrontendLink::normalize((string) ($data['agenda_download_url'] ?? ''));

        return $data;
    }

    public static function set(array $data): void
    {
        $payload = array_merge(self::defaults(), $data);
        $payload['theme_color'] = 'blue';
        $payload['agenda_download_url'] = FrontendLink::normalize((string) ($payload['agenda_download_url'] ?? ''));

        Setting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );
    }
}
