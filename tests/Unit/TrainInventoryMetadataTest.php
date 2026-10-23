<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class TrainInventoryMetadataTest extends TestCase
{
    public function test_unsigned_bigint_metadata_accepts_mysql_and_mariadb_display_width_forms(): void
    {
        $migration = require __DIR__ . '/../../database/migrations/2026_10_22_000004_harden_train_inventory_constraints.php';
        $matcher = new ReflectionMethod($migration, 'isExpectedUnsignedBigintType');
        $matcher->setAccessible(true);

        foreach (['bigint unsigned', 'BIGINT(20) UNSIGNED', 'bigint(8) unsigned'] as $columnType) {
            self::assertTrue($matcher->invoke($migration, $columnType), $columnType);
        }
    }

    public function test_unsigned_bigint_metadata_rejects_wrong_base_type_or_signedness(): void
    {
        $migration = require __DIR__ . '/../../database/migrations/2026_10_22_000004_harden_train_inventory_constraints.php';
        $matcher = new ReflectionMethod($migration, 'isExpectedUnsignedBigintType');
        $matcher->setAccessible(true);

        foreach (['bigint', 'bigint signed', 'int unsigned', 'bigint(20) unsigned zerofill'] as $columnType) {
            self::assertFalse($matcher->invoke($migration, $columnType), $columnType);
        }
    }
}
