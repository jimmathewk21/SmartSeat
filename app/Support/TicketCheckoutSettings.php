<?php

namespace App\Support;

use App\Models\Setting;

class TicketCheckoutSettings
{
    public const SETTING_KEY = 'ticket_checkout_settings';

    public static function defaults(): array
    {
        return [
            'gateway_charge_type' => 'none', // none|percent|flat
            'gateway_charge_percent' => 0,
            'gateway_charge_inr' => '0.00',
            'gateway_charge_usd' => '0.00',
            'min_ticket_quantity' => 1,
            'max_ticket_quantity' => 10,
            'plan_caption' => 'Conference access with curated benefits and member-ready pricing.',
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

        return array_merge(self::defaults(), $decoded);
    }

    public static function set(array $data): void
    {
        $payload = array_merge(self::defaults(), $data);
        Setting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
    }
}
