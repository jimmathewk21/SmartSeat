<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use RuntimeException;

abstract class DatabaseTestCase extends TestCase
{
    use DatabaseTransactions;

    public function createApplication()
    {
        $app = parent::createApplication();
        $connection = $app['db']->connection('sqlite');

        if ($connection->getDriverName() !== 'sqlite' || $connection->getDatabaseName() !== ':memory:') {
            throw new RuntimeException('Refusing to create test fixtures outside SQLite :memory:.');
        }

        // Minimal disposable fixture schema. No migrations or seeders are run.
        $connection->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role', 32)->default('passenger');
            $table->rememberToken();
            $table->timestamps();
        });

        return $app;
    }
}
