<?php

namespace Tests;

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = require dirname(__DIR__).'/bootstrap/app.php';
        $this->traitsUsedByTest = array_flip(class_uses_recursive(static::class));

        // Replace connection configuration before any application provider boots.
        $app->afterBootstrapping(LoadConfiguration::class, function ($app) {
            $app['config']->set([
                'app.env' => 'testing',
                // Ephemeral test encryption key; never writes APP_KEY or .env.
                'app.key' => 'base64:'.base64_encode(str_repeat('t', 32)),
                'database.default' => 'sqlite',
                'database.connections' => [
                    'sqlite' => [
                        'driver' => 'sqlite',
                        'database' => ':memory:',
                        'prefix' => '',
                        'foreign_key_constraints' => true,
                    ],
                ],
                'cache.default' => 'array',
                'session.driver' => 'array',
                'queue.default' => 'sync',
                'mail.default' => 'array',
                'auth.defaults.guard' => 'web',
                'auth.providers.users.model' => User::class,
                'hashing.bcrypt.rounds' => 4,
            ]);
        });

        $app->make(Kernel::class)->bootstrap();

        // Reject any accidentally restored connection before tests can use the DB.
        $connections = $app['config']->get('database.connections');
        if ($app['config']->get('database.default') !== 'sqlite'
            || array_keys($connections) !== ['sqlite']
            || $connections['sqlite']['driver'] !== 'sqlite'
            || $connections['sqlite']['database'] !== ':memory:'
            || ! empty($connections['sqlite']['url'])) {
            throw new RuntimeException('Tests require SQLite :memory: with no other configured connections.');
        }

        return $app;
    }
}
