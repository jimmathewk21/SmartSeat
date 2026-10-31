<?php

namespace Tests\Unit;

use App\Support\RailwaySchema;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RailwaySchemaCompilationTest extends TestCase
{
    public function test_mariadb_ddl_is_explicitly_innodb_and_has_no_partial_indexes(): void
    {
        $sql = implode("\n", RailwaySchema::statements('mariadb'));
        $this->assertSame(11, substr_count($sql, 'ENGINE=InnoDB'));
        $this->assertStringContainsString('GENERATED ALWAYS AS', $sql);
        $this->assertStringContainsString('UNIQUE (active_inventory_id)', $sql);
        $this->assertStringNotContainsString('AUTOINCREMENT', $sql);
        $this->assertStringNotContainsString(' GLOB ', $sql);
        $this->assertStringContainsString("BINARY code NOT REGEXP '[^A-Z]'", $sql);
        $this->assertStringContainsString("number NOT REGEXP '[^0-9]'", $sql);
        $this->assertSame(RailwaySchema::statements('mysql'), RailwaySchema::statements('mariadb'));
    }

    public function test_disallowed_character_checks_reject_final_newlines_without_end_anchor_ambiguity(): void
    {
        // MariaDB uses PCRE. Demonstrate the previous $ anchor's final-newline edge case.
        $this->assertSame(1, preg_match('/^[A-Z]+$/', "AAA\n"));
        foreach (["AAA\n", 'aaa', 'A A', 'A1', "A\r\n"] as $code) {
            $this->assertSame(1, preg_match('/[^A-Z]/', $code));
        }
        $this->assertSame(0, preg_match('/[^A-Z]/', 'AAA'));
    }

    public function test_unsupported_drivers_fail_before_any_sql_is_returned(): void
    {
        $this->expectException(InvalidArgumentException::class);
        RailwaySchema::statements('pgsql');
    }
}
