<?php

namespace Tests\Unit;

use App\Support\AdministratorDatabaseName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AdministratorDatabaseNameTest extends TestCase
{
    public function test_base_database_is_accepted(): void
    {
        $this->assertTrue(AdministratorDatabaseName::allows('smartseat_rebuild'));
    }

    public function test_explicitly_permitted_suffixes_are_accepted(): void
    {
        foreach (['testing', 'test_2', '123', 'a1_b2_c3'] as $suffix) {
            $this->assertTrue(AdministratorDatabaseName::allows('smartseat_rebuild_'.$suffix, [$suffix]));
        }
    }

    public function test_unlisted_suffixes_are_rejected(): void
    {
        $this->assertFalse(AdministratorDatabaseName::allows('smartseat_rebuild_testing'));
        $this->assertFalse(AdministratorDatabaseName::allows('smartseat_rebuild_testing', ['test']));
        $this->assertFalse(AdministratorDatabaseName::allows('smartseat_rebuild_test_extra', ['test']));
    }

    #[DataProvider('unapprovedNames')]
    public function test_unapproved_database_names_are_rejected(string $database): void
    {
        $this->assertFalse(AdministratorDatabaseName::allows($database, ['testing']));
    }

    public static function unapprovedNames(): array
    {
        return array_map(static fn (string $name): array => [$name], [
            'smartseat_dev', 'unrelated', 'smartseat_rebuilds', 'smartseat_rebuild_',
            'prefix_smartseat_rebuild', 'SmartSeat_rebuild', 'SMARTSEAT_REBUILD',
            'smartseat_rebuild_TESTING', ' smartseat_rebuild', 'smartseat_rebuild ',
            "smartseat_rebuild\n", '',
        ]);
    }

    #[DataProvider('invalidSuffixes')]
    public function test_invalid_suffixes_are_rejected_even_when_listed(string $suffix): void
    {
        $this->assertFalse(AdministratorDatabaseName::allows('smartseat_rebuild_'.$suffix, [$suffix]));
    }

    public static function invalidSuffixes(): array
    {
        return array_map(static fn (string $suffix): array => [$suffix], [
            '', '_test', 'test_', 'test__one', 'TEST', 'test-one', 'test.one',
            '*', '%', 'test*', 'test%', 'test?', ' test', 'test ', "test\n", "test\0", 'tést',
        ]);
    }

    public function test_wildcard_and_non_string_permissions_do_not_approve_suffixes(): void
    {
        $this->assertFalse(AdministratorDatabaseName::allows('smartseat_rebuild_testing', ['*', '%', 'test*', 'test%']));
        $this->assertFalse(AdministratorDatabaseName::allows('smartseat_rebuild_123', [123, true, null, []]));
    }
}
