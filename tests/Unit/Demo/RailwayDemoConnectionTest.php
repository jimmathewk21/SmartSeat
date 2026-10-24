<?php

namespace Tests\Unit\Demo;

use App\Services\Demo\RailwayDemoConnection;
use App\Services\Demo\RailwayDemoPolicy;
use Illuminate\Database\LostConnectionException;
use Illuminate\Database\MySqlConnection;
use PDO;
use PDOStatement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RailwayDemoConnectionTest extends TestCase
{
    private static function configuration(): array
    {
        return ['driver' => 'mysql', 'database' => 'smartseat_demo', 'host' => '127.0.0.1', 'prefix' => '', 'options' => []];
    }

    public static function ambiguousConnections(): array
    {
        return [
            'read replica points to dev' => [['read' => ['database' => 'smartseat_dev']]],
            'writer points to dev' => [['write' => ['database' => 'smartseat_dev']]],
            'read and write disagree' => [['read' => ['database' => 'smartseat_demo'], 'write' => ['database' => 'smartseat_train_test']]],
            'same-target split still unsupported' => [['read' => ['database' => 'smartseat_demo'], 'write' => ['database' => 'smartseat_demo']]],
            'empty split' => [['read' => []]],
            'URL overrides target' => [['url' => 'mysql://synthetic:unused@localhost/smartseat_dev']],
            'multiple hosts' => [['host' => ['127.0.0.1', 'other']]],
            'sticky routing' => [['sticky' => true]],
            'persistent session' => [['options' => [PDO::ATTR_PERSISTENT => true]]],
            'prefix' => [['prefix' => 'other_']],
            'dev' => [['database' => 'smartseat_dev']],
            'test' => [['database' => 'smartseat_train_test']],
            'unknown target' => [['database' => 'other']],
        ];
    }

    #[DataProvider('ambiguousConnections')]
    public function test_ambiguous_configuration_is_rejected_without_resolving_any_pdo(array $overrides): void
    {
        $source = new MySqlConnection(function () {
            self::fail('An unsafe configuration must not open a connection.');
        }, 'smartseat_demo', '', array_replace(self::configuration(), $overrides));
        $this->expectException(RuntimeException::class);
        RailwayDemoConnection::verifiedWriter($source);
    }

    public function test_verification_and_every_subsequent_read_use_the_same_writer(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->method('getAttribute')->willReturnMap([[PDO::ATTR_DRIVER_NAME, 'mysql'], [PDO::ATTR_PERSISTENT, false]]);
        $pdo->method('inTransaction')->willReturn(false);
        $statement = $this->createMock(PDOStatement::class);
        $statement->method('execute')->willReturn(true);
        $statement->expects(self::exactly(3))->method('fetchAll')->willReturn([(object) ['db' => 'smartseat_demo']]);
        $pdo->expects(self::exactly(3))->method('prepare')->with('SELECT DATABASE() AS db')->willReturn($statement);
        $source = new MySqlConnection($pdo, 'smartseat_demo', '', self::configuration());
        $source->setReconnector(fn () => self::fail('The pinned installer must not reconnect.'));
        $writer = RailwayDemoConnection::verifiedWriter($source);
        self::assertNotSame($source, $writer);
        self::assertSame($pdo, $writer->getPdo());
        self::assertSame($pdo, $writer->getReadPdo());
        self::assertNull($writer->getRawReadPdo());
        self::assertNull($writer->getEventDispatcher());
        $writer->selectOne('SELECT DATABASE() AS db');
        $writer->selectOne('SELECT DATABASE() AS db', [], false);
        $this->expectException(LostConnectionException::class);
        $writer->reconnect();
    }

    public function test_actual_writer_target_is_checked_not_just_configuration(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->method('getAttribute')->willReturnMap([[PDO::ATTR_DRIVER_NAME, 'mysql'], [PDO::ATTR_PERSISTENT, false]]);
        $pdo->method('inTransaction')->willReturn(false);
        $statement = $this->createMock(PDOStatement::class);
        $statement->method('execute')->willReturn(true);
        $statement->method('fetchAll')->willReturn([(object) ['db' => 'smartseat_dev']]);
        $pdo->expects(self::once())->method('prepare')->with('SELECT DATABASE() AS db')->willReturn($statement);
        $source = new MySqlConnection($pdo, 'smartseat_demo', '', self::configuration());
        $this->expectExceptionMessage('Connected database must be smartseat_demo');
        RailwayDemoConnection::verifiedWriter($source);
    }

    public function test_an_injected_read_pdo_is_rejected_even_without_split_configuration(): void
    {
        $source = new MySqlConnection(fn () => self::fail('Writer must not open.'), 'smartseat_demo', '', self::configuration());
        $source->setReadPdo(fn () => self::fail('Replica must not open.'));
        $this->expectExceptionMessage('existing read PDO');
        RailwayDemoConnection::verifiedWriter($source);
    }

    public function test_a_preexisting_transaction_is_rejected_before_any_query(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->method('getAttribute')->willReturnMap([[PDO::ATTR_DRIVER_NAME, 'mysql'], [PDO::ATTR_PERSISTENT, false]]);
        $pdo->method('inTransaction')->willReturn(true);
        $pdo->expects(self::never())->method('prepare');
        $source = new MySqlConnection($pdo, 'smartseat_demo', '', self::configuration());
        $this->expectExceptionMessage('idle MySQL write connection');
        RailwayDemoConnection::verifiedWriter($source);
    }

    public function test_connection_init_commands_are_not_executed(): void
    {
        if (! defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
            self::markTestSkipped('pdo_mysql is unavailable.');
        }
        $this->expectException(RuntimeException::class);
        RailwayDemoPolicy::assertSingleConnection(array_replace(self::configuration(), [
            'options' => [constant('PDO::MYSQL_ATTR_INIT_COMMAND') => 'USE smartseat_dev'],
        ]));
    }
}
