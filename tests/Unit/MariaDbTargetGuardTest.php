<?php

namespace Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\MariaDb\ApprovedTarget;

class MariaDbTargetGuardTest extends TestCase
{
    private function approved(): array
    {
        return ['approval' => ApprovedTarget::APPROVAL,
            'database' => ApprovedTarget::DATABASE, 'host' => '127.0.0.1', 'port' => 3306,
            'username' => 'smartseat_test_runner', 'password' => 'not-used-by-this-pure-test'];
    }

    public function test_exact_approved_descriptor_is_validated_without_connecting(): void
    {
        $this->assertSame($this->approved(), ApprovedTarget::validate($this->approved()));
    }

    public function test_existing_databases_and_implicit_connection_settings_are_rejected(): void
    {
        foreach ([
            ['database' => 'smartseat_dev'], ['approval' => ''], ['host' => 'remote.example.test'],
            ['username' => 'root'], ['url' => 'mysql://localhost/existing'], ['read' => []],
            ['database' => 'smartseat_rebuild'], ['database' => 'smartseat_rebuild_booking_concurrency_test'],
            ['username' => 'application_user'], ['unix_socket' => '/tmp/mysql.sock'], ['write' => []],
            ['password' => ''],
        ] as $overrides) {
            try {
                ApprovedTarget::validate(array_replace($this->approved(), $overrides));
                $this->fail('Unsafe descriptor must be rejected before any connection.');
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }
}
