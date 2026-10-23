<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class FrontendSeoTitle extends Model
{
    use HasFactory;

    public $table = 'frontend_seo_titles';

    protected $fillable = [
        'path',
        'title',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public static function normalizePath(string $path): string
    {
        $path = trim($path);

        if ($path === '' || $path === '/') {
            return '/';
        }

        $parts = parse_url($path);
        $path = is_array($parts) && isset($parts['path']) ? (string) $parts['path'] : $path;
        $path = '/' . trim($path, '/');

        return $path === '' ? '/' : $path;
    }

    public static function titleForPath(string $path): ?string
    {
        $normalized = self::normalizePath($path);

        try {
            return self::query()
                ->where('is_active', true)
                ->where('path', $normalized)
                ->value('title');
        } catch (Throwable $exception) {
            return null;
        }
    }
}
