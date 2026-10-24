<?php

namespace App\Services\Demo;

use Illuminate\Foundation\Application;
use Symfony\Component\Console\Input\ArgvInput;

/** Process-local overrides, applied before Telescope/Pulse providers register. */
class RailwayDemoConsoleBootstrap
{
    public static function configure(Application $app, array $argv): void
    {
        if ((new ArgvInput($argv))->getFirstArgument() !== 'smartseat:railway-demo') {
            return;
        }

        // Also protect rejected installation attempts and password-hash inserts from recording.
        $app->make('config')->set([
            'telescope.enabled' => false, 'pulse.enabled' => false,
            // RouteServiceProvider resolves the rate limiter during console bootstrap.
            'cache.default' => 'array', 'cache.limiter' => 'array',
        ]);
    }
}
