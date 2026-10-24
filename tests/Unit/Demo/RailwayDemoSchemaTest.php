<?php

namespace Tests\Unit\Demo;

use App\Services\Demo\RailwayDemoPlan;
use App\Services\Demo\RailwayDemoSchema;
use PHPUnit\Framework\TestCase;

class RailwayDemoSchemaTest extends TestCase
{
    public function test_every_written_column_is_declared_in_existing_migration_sources(): void
    {
        $declared = [];
        foreach (glob(__DIR__.'/../../../database/migrations/*.php') as $file) {
            $source = file_get_contents($file);
            // Static source inspection only: never require or execute a migration.
            preg_match_all("/Schema::(?:create|table)\('([a-z_]+)'/", $source, $matches, PREG_OFFSET_CAPTURE);
            foreach ($matches[1] as $index => [$table]) {
                $offset = $matches[0][$index][1];
                $next = $matches[0][$index + 1][1] ?? strlen($source);
                $declared[$table] = ($declared[$table] ?? '').substr($source, $offset, $next - $offset);
            }
            if (str_ends_with($file, '2026_10_24_000001_add_journey_to_train_bookings_and_waitlist.php')) {
                foreach (['train_bookings', 'train_waitlist_entries'] as $table) {
                    $declared[$table] .= $source;
                }
            }
        }
        foreach (RailwayDemoSchema::COLUMNS as $table => $columns) {
            self::assertArrayHasKey($table, $declared);
            foreach (explode(' ', $columns) as $column) {
                if (in_array($column, ['created_at', 'updated_at'], true)) {
                    self::assertStringContainsString('->timestamps()', $declared[$table]);
                } elseif ($column === 'deleted_at') {
                    self::assertStringContainsString('->softDeletes()', $declared[$table]);
                } else {
                    self::assertStringContainsString("'".$column."'", $declared[$table], $table.'.'.$column);
                }
            }
        }
    }

    public function test_hardening_objects_and_journey_dependencies_remain_in_migration_sources(): void
    {
        $source = file_get_contents(__DIR__.'/../../../database/migrations/2026_10_22_000004_harden_train_inventory_constraints.php');
        foreach (['trip_inventory_status_pointer_chk', 'active_trip_seat_inventory_id', 'train_booking_active_inventory_uq', 'train_booking_seat_inventory_trip_fk', 'train_booking_seat_passenger_booking_fk'] as $name) {
            self::assertStringContainsString($name, $source);
        }
        self::assertSame(
            RailwayDemoSchema::normalizeExpression("(status = 'available' AND current_booking_id IS NULL) OR (status = 'booked' AND current_booking_id IS NOT NULL)"),
            RailwayDemoSchema::normalizeExpression("((`status` = _utf8mb4'available' and `current_booking_id` is null) or (`status` = _utf8mb4'booked' and `current_booking_id` is not null))"),
        );
        self::assertNotContains('active_trip_seat_inventory_id', RailwayDemoSchema::columns('train_booking_seats'));
    }

    public function test_general_seeder_and_legacy_demo_seeders_do_not_call_the_new_installer(): void
    {
        foreach (['DatabaseSeeder', 'TrainDemoSeeder', 'IndianTrainDemoSeeder'] as $class) {
            $source = file_get_contents(__DIR__.'/../../../database/seeders/'.$class.'.php');
            self::assertStringNotContainsString('RailwayDemoInstaller', $source);
            self::assertStringNotContainsString('GenerateRailwayDemoCommand', $source);
        }
        self::assertSame('S27-v1', RailwayDemoPlan::VERSION);
    }
}
