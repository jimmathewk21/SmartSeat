<?php

namespace Tests\Unit\Demo;

use App\Services\Demo\RailwayDemoPlan;
use App\Services\Demo\RailwayDemoPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RailwayDemoPolicyTest extends TestCase
{
    public function test_only_explicit_local_demo_target_is_permitted(): void
    {
        RailwayDemoPolicy::assertConfiguration('local', 'mysql', 'mysql', 'smartseat_demo', 'smartseat_demo');
        RailwayDemoPolicy::assertConnectedDatabase('smartseat_demo');
        self::assertSame('smartseat_demo', RailwayDemoPolicy::DATABASE);
    }

    public static function rejectedTargets(): array
    {
        return [
            ['local', 'mysql', 'mysql', 'smartseat_dev', 'smartseat_demo'],
            ['local', 'mysql', 'mysql', 'smartseat_train_test', 'smartseat_demo'],
            ['local', 'mysql', 'mysql', 'other_database', 'smartseat_demo'],
            ['production', 'mysql', 'mysql', 'smartseat_demo', 'smartseat_demo'],
            ['testing', 'mysql', 'mysql', 'smartseat_demo', 'smartseat_demo'],
            ['local', 'sqlite', 'sqlite', 'smartseat_demo', 'smartseat_demo'],
            ['local', 'mysql', 'sqlite', 'smartseat_demo', 'smartseat_demo'],
            ['local', 'mysql', 'mysql', 'smartseat_demo', ''],
            ['local', 'mysql', 'mysql', 'smartseat_demo', 'smartseat_dev'],
        ];
    }

    #[DataProvider('rejectedTargets')]
    public function test_unintended_configurations_are_rejected_before_connection(string $env, string $connection, string $driver, string $database, string $ack): void
    {
        $this->expectException(RuntimeException::class);
        RailwayDemoPolicy::assertConfiguration($env, $connection, $driver, $database, $ack);
    }

    public static function rejectedActualTargets(): array
    {
        return [['smartseat_dev'], ['smartseat_train_test'], ['other_database'], ['']];
    }

    #[DataProvider('rejectedActualTargets')]
    public function test_actual_database_must_match_the_allowlist(string $actual): void
    {
        $this->expectException(RuntimeException::class);
        RailwayDemoPolicy::assertConnectedDatabase($actual);
    }

    public function test_matching_marker_is_a_noop_and_a_new_snapshot_is_not_installed(): void
    {
        self::assertFalse(RailwayDemoPolicy::alreadyInstalled(null, 'abc'));
        self::assertTrue(RailwayDemoPolicy::alreadyInstalled(json_encode(['version' => RailwayDemoPlan::VERSION, 'fingerprint' => 'abc']), 'abc'));
        $this->expectException(RuntimeException::class);
        RailwayDemoPolicy::alreadyInstalled(json_encode(['version' => RailwayDemoPlan::VERSION, 'fingerprint' => 'abc']), 'different');
    }

    public function test_owner_permissions_must_already_exist(): void
    {
        $role = [['id' => 9, 'title' => 'Train Owner', 'deleted_at' => null]];
        self::assertSame(9, RailwayDemoPolicy::assertRole('owner', $role, ['train_management_access', 'train_management_edit']));
        $this->expectException(RuntimeException::class);
        RailwayDemoPolicy::assertRole('owner', $role, ['train_management_access']);
    }

    public function test_reserved_admin_role_id_is_never_assigned(): void
    {
        $this->expectException(RuntimeException::class);
        RailwayDemoPolicy::assertRole('owner', [['id' => 1, 'title' => 'Train Owner', 'deleted_at' => null]], ['train_management_access', 'train_management_edit']);
    }

    public function test_ambiguous_role_titles_are_not_repaired(): void
    {
        $this->expectException(RuntimeException::class);
        RailwayDemoPolicy::assertRole('passenger', [
            ['id' => 2, 'title' => 'User', 'deleted_at' => null],
            ['id' => 3, 'title' => 'User', 'deleted_at' => null],
        ]);
    }
}
