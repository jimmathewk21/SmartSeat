<?php

namespace Tests\Unit;

use App\Console\Commands\CreateAdministrator;
use App\Services\AdministratorTarget;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class AdministratorTargetTest extends TestCase
{
    private array $originalArgv;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalArgv = $_SERVER['argv'];
        // Simulate invocation metadata only. The command itself is never run.
        $_SERVER['argv'] = ['artisan', 'app:create-administrator', '--database=smartseat_rebuild'];
        Config::set('app.env', 'local');
        Config::set('smartseat.administrator_bootstrap.permitted_database_suffixes', []);
        Config::set('database.default', 'bootstrap');
        Config::set('database.connections', ['bootstrap' => $this->configuration()]);
    }

    protected function tearDown(): void
    {
        $_SERVER['argv'] = $this->originalArgv;
        parent::tearDown();
    }

    private function configuration(): array
    {
        return [
            'driver' => 'mysql', 'database' => 'smartseat_rebuild',
            'host' => '127.0.0.1', 'url' => null, 'unix_socket' => '',
        ];
    }

    #[DataProvider('unsafeConfigurations')]
    public function test_unsafe_configurations_are_rejected_before_resolving_a_connection(array $overrides): void
    {
        Config::set('database.connections.bootstrap', array_replace($this->configuration(), $overrides));
        DB::shouldReceive('connection')->never();
        $this->expectException(RuntimeException::class);
        (new AdministratorTarget)->verify('smartseat_rebuild');
    }

    public static function unsafeConfigurations(): array
    {
        return [
            'sqlite' => [['driver' => 'sqlite']],
            'remote host' => [['host' => 'db.example.test']],
            'host array' => [['host' => ['localhost']]],
            'non-exact host' => [['host' => 'localhost.example.test']],
            'database mismatch' => [['database' => 'smartseat_dev']],
            'URL' => [['url' => 'mysql://localhost/smartseat_rebuild']],
            'socket' => [['unix_socket' => '/tmp/mysql.sock']],
            'read split' => [['read' => ['host' => 'localhost']]],
            'write split' => [['write' => ['host' => 'localhost']]],
            'empty split' => [['read' => null]],
        ];
    }

    public function test_non_local_environment_is_rejected_without_a_connection(): void
    {
        Config::set('app.env', 'production');
        DB::shouldReceive('connection')->never();
        $this->expectException(RuntimeException::class);
        (new AdministratorTarget)->verify('smartseat_rebuild');
    }

    public function test_unapproved_name_is_rejected_without_a_connection(): void
    {
        DB::shouldReceive('connection')->never();
        $this->expectException(RuntimeException::class);
        (new AdministratorTarget)->verify('smartseat_rebuild_testing');
    }

    public function test_verification_outside_explicit_command_is_rejected_without_a_connection(): void
    {
        $_SERVER['argv'] = ['artisan', 'serve'];
        DB::shouldReceive('connection')->never();
        $this->expectException(RuntimeException::class);
        (new AdministratorTarget)->verify('smartseat_rebuild');
    }

    #[DataProvider('localConnections')]
    public function test_approved_local_connections_use_only_the_database_identity_query(string $driver, string $host): void
    {
        $configuration = array_replace($this->configuration(), ['driver' => $driver, 'host' => $host]);
        Config::set('database.connections.bootstrap', $configuration);
        $connection = Mockery::mock(Connection::class);
        DB::shouldReceive('connection')->once()->with('bootstrap')->andReturn($connection);
        $connection->shouldReceive('getConfig')->once()->andReturn($configuration);
        $connection->shouldReceive('selectOne')->once()->with('SELECT DATABASE() AS database_name', [], false)
            ->andReturn((object) ['database_name' => 'smartseat_rebuild']);
        (new AdministratorTarget)->verify('smartseat_rebuild');
    }

    public static function localConnections(): array
    {
        return [['mysql', 'localhost'], ['mysql', '127.0.0.1'], ['mysql', '::1'], ['mariadb', 'localhost']];
    }

    public function test_mismatched_effective_database_is_rejected(): void
    {
        $connection = Mockery::mock(Connection::class);
        DB::shouldReceive('connection')->once()->andReturn($connection);
        $connection->shouldReceive('getConfig')->once()->andReturn($this->configuration());
        $connection->shouldReceive('selectOne')->once()->andReturn((object) ['database_name' => 'unexpected']);
        $this->expectException(RuntimeException::class);
        (new AdministratorTarget)->verify('smartseat_rebuild');
    }

    public function test_unsafe_resolved_connection_is_rejected_before_querying(): void
    {
        $connection = Mockery::mock(Connection::class);
        DB::shouldReceive('connection')->once()->andReturn($connection);
        $connection->shouldReceive('getConfig')->once()->andReturn(array_replace($this->configuration(), ['host' => 'remote']));
        $connection->shouldReceive('selectOne')->never();
        $this->expectException(RuntimeException::class);
        (new AdministratorTarget)->verify('smartseat_rebuild');
    }

    public function test_bootstrap_definition_has_no_password_or_identity_arguments(): void
    {
        $definition = (new CreateAdministrator)->getDefinition();
        $this->assertTrue($definition->hasOption('database'));
        $this->assertFalse($definition->hasOption('password'));
        $this->assertFalse($definition->hasOption('email'));
        $this->assertSame([], $definition->getArguments());
    }
}
