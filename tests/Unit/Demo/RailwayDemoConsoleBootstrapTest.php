<?php

namespace Tests\Unit\Demo;

use App\Services\Demo\RailwayDemoConsoleBootstrap;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class RailwayDemoConsoleBootstrapTest extends TestCase
{
    public static function invocations(): array
    {
        return [
            'preview' => [['artisan', 'smartseat:railway-demo', '--as-of=2026-10-24'], false],
            'installation or rejected installation' => [['artisan', 'smartseat:railway-demo', '--install'], false],
            'global option' => [['artisan', '--env=local', 'smartseat:railway-demo'], false],
            'unrelated command' => [['artisan', 'route:list'], true],
            'no command' => [['artisan'], true],
            'unrelated argument contains command name' => [['artisan', 'list', 'smartseat:railway-demo'], true],
        ];
    }

    #[DataProvider('invocations')]
    public function test_only_this_commands_process_overrides_loaded_telemetry_configuration(array $argv, bool $enabled): void
    {
        $app = new Application(dirname(__DIR__, 3));
        // Represents either loaded files or a cached config repository: neither env() nor a provider is needed.
        $config = new Repository(['telescope' => ['enabled' => true], 'pulse' => ['enabled' => true], 'cache' => ['default' => 'database', 'limiter' => 'redis']]);
        $app->instance('config', $config);
        RailwayDemoConsoleBootstrap::configure($app, $argv);
        self::assertSame($enabled, $config->get('telescope.enabled'));
        self::assertSame($enabled, $config->get('pulse.enabled'));
        self::assertSame($enabled ? 'database' : 'array', $config->get('cache.default'));
        self::assertSame($enabled ? 'redis' : 'array', $config->get('cache.limiter'));
    }

    public function test_fresh_artisan_preview_succeeds_without_database_or_manual_telemetry_overrides(): void
    {
        $root = dirname(__DIR__, 3);
        $process = new Process([PHP_BINARY, 'artisan', 'smartseat:railway-demo', '--as-of=2026-10-24', '--seed=27001'], $root, [
            'APP_ENV' => 'local',
            'APP_CONFIG_CACHE' => sys_get_temp_dir().'/smartseat-no-config-'.bin2hex(random_bytes(12)).'.php',
            'DB_CONNECTION' => 'offline_preview_no_connection',
            'DB_HOST' => '127.0.0.1', 'DB_PORT' => '1', 'DB_DATABASE' => 'offline_preview_no_database',
            'DATABASE_URL' => false,
            'TELESCOPE_ENABLED' => 'true', 'PULSE_ENABLED' => 'true',
            'TELESCOPE_DB_CONNECTION' => 'offline_preview_no_connection',
            'PULSE_DB_CONNECTION' => 'offline_preview_no_connection',
            'CACHE_DRIVER' => 'database', 'SESSION_DRIVER' => 'database', 'LOG_CHANNEL' => 'stderr',
        ]);
        $process->setTimeout(60);
        $process->run();
        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput().$process->getOutput());
        self::assertStringContainsString('PREVIEW ONLY. No database connection, credential generation or data writes.', $process->getOutput());
        self::assertStringContainsString('1300 confirmed + 200 cancelled', $process->getOutput());
        self::assertStringContainsString('a2682d9a3455c5ebefbab9929eba16a150e8ba5db0c92cf4f423c37d5df4a353', $process->getOutput());
    }
}
