<?php

namespace App\Support;

use App\Models\Setting;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class TicketFxSettings
{
    public const USD_TO_INR_KEY = 'ticket_fx_usd_to_inr';
    public const LAST_UPDATED_AT_KEY = 'ticket_fx_last_updated_at';
    private const DEFAULT_USD_TO_INR = 83.0;

    public static function usdToInr(): float
    {
        $raw = Setting::query()
            ->where('key', self::USD_TO_INR_KEY)
            ->value('value');

        $rate = is_numeric($raw) ? (float) $raw : self::DEFAULT_USD_TO_INR;

        if ($rate <= 0) {
            $rate = self::DEFAULT_USD_TO_INR;
        }

        return $rate;
    }

    public static function inrToUsd(): float
    {
        return round(1 / self::usdToInr(), 6);
    }

    public static function lastUpdatedAt(): ?CarbonInterface
    {
        $raw = Setting::query()
            ->where('key', self::LAST_UPDATED_AT_KEY)
            ->value('value');

        if (! $raw) {
            return null;
        }

        try {
            return Carbon::parse($raw);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function setUsdToInr(float $rate): void
    {
        $rate = round($rate, 6);
        if ($rate <= 0) {
            $rate = self::DEFAULT_USD_TO_INR;
        }

        Setting::query()->updateOrCreate(
            ['key' => self::USD_TO_INR_KEY],
            ['value' => number_format($rate, 6, '.', '')]
        );

        Setting::query()->updateOrCreate(
            ['key' => self::LAST_UPDATED_AT_KEY],
            ['value' => now()->format('Y-m-d H:i:s')]
        );
    }
}
