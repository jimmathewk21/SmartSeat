<?php

namespace Tests\Unit\Demo;

use App\Services\Demo\RailwayDemoCredentials;
use Illuminate\Hashing\BcryptHasher;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RailwayDemoCredentialsTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().'/smartseat-credential-test-'.bin2hex(random_bytes(12));
        mkdir($this->directory, 0700);
    }

    protected function tearDown(): void
    {
        // Only explicitly named files inside this test's random temporary directory.
        foreach (['credentials.json', 'original.json'] as $file) {
            if (file_exists($this->directory.'/'.$file)) {
                unlink($this->directory.'/'.$file);
            }
        }
        rmdir($this->directory);
    }

    private function plan(): array
    {
        return ['tables' => ['users' => ['s27.passenger.001@example.invalid' => ['name' => 'Synthetic Passenger']]]];
    }

    public function test_hashes_match_random_passwords_and_reference_survives_only_success(): void
    {
        $hasher = new BcryptHasher(['rounds' => 4]);
        $credentials = new RailwayDemoCredentials($hasher, $this->directory);
        $result = $credentials->withCleanup(fn () => $credentials->create($this->plan()));
        $payload = json_decode(file_get_contents($result[1]), true, 512, JSON_THROW_ON_ERROR);
        $password = $payload['accounts'][0]['password'];
        self::assertSame(32, strlen($password));
        self::assertTrue($hasher->check($password, $result[0]['s27.passenger.001@example.invalid']));
        self::assertStringNotContainsString($password, $result[0]['s27.passenger.001@example.invalid']);
        $credentials->discard();
        self::assertFileExists($result[1]); // A committed reference is no longer owned for cleanup.
        if (DIRECTORY_SEPARATOR !== '\\') {
            self::assertSame(0600, fileperms($result[1]) & 0777);
        }
    }

    public function test_later_transaction_failure_discards_only_the_attempts_reference(): void
    {
        $credentials = new RailwayDemoCredentials(new BcryptHasher(['rounds' => 4]), $this->directory);
        try {
            $credentials->withCleanup(function () use ($credentials) {
                $credentials->create($this->plan());
                throw new RuntimeException('Simulated SQL rollback.');
            });
            self::fail('A failed operation must not return success.');
        } catch (RuntimeException $exception) {
            self::assertSame('Simulated SQL rollback.', $exception->getMessage());
        }
        self::assertFileDoesNotExist($this->directory.'/credentials.json');
    }

    public function test_partial_write_is_an_error_and_the_partial_file_is_removed(): void
    {
        $credentials = new class(new BcryptHasher(['rounds' => 4]), $this->directory) extends RailwayDemoCredentials
        {
            protected function write(string $payload): int|false
            {
                parent::write(substr($payload, 0, 30));

                return 30;
            }
        };
        try {
            $credentials->withCleanup(fn () => $credentials->create($this->plan()));
            self::fail('Partial writes must not succeed.');
        } catch (RuntimeException $exception) {
            self::assertSame('Private credential creation failed; installation must roll back.', $exception->getMessage());
        }
        self::assertFileDoesNotExist($this->directory.'/credentials.json');
    }

    public function test_cleanup_failure_is_explicit_and_does_not_leave_usable_plaintext(): void
    {
        $credentials = new class(new BcryptHasher(['rounds' => 4]), $this->directory) extends RailwayDemoCredentials
        {
            protected function remove(string $path): bool
            {
                return false;
            }
        };
        try {
            $credentials->withCleanup(function () use ($credentials) {
                $credentials->create($this->plan());
                throw new RuntimeException('Simulated rollback.');
            });
            self::fail('Cleanup failures must be reported.');
        } catch (RuntimeException $exception) {
            self::assertStringStartsWith('Private credential cleanup failed.', $exception->getMessage());
        }
        self::assertSame('', file_get_contents($this->directory.'/credentials.json'));
    }

    public function test_failed_flush_does_not_report_success_or_retain_a_reference(): void
    {
        $credentials = new class(new BcryptHasher(['rounds' => 4]), $this->directory) extends RailwayDemoCredentials
        {
            private bool $firstFlush = true;

            protected function flush(): bool
            {
                if ($this->firstFlush) {
                    $this->firstFlush = false;

                    return false;
                }

                return parent::flush();
            }
        };
        try {
            $credentials->withCleanup(fn () => $credentials->create($this->plan()));
            self::fail('Failed flush must not succeed.');
        } catch (RuntimeException $exception) {
            self::assertSame('Private credential creation failed; installation must roll back.', $exception->getMessage());
        }
        self::assertFileDoesNotExist($this->directory.'/credentials.json');
    }

    public function test_hashing_errors_cannot_expose_passwords_in_exception_messages(): void
    {
        $hasher = new class extends BcryptHasher
        {
            public function make($value, array $options = [])
            {
                throw new RuntimeException('Sensitive hashing details: '.$value);
            }
        };
        $credentials = new RailwayDemoCredentials($hasher, $this->directory);
        try {
            $credentials->withCleanup(fn () => $credentials->create($this->plan()));
            self::fail('Hash failure must not succeed.');
        } catch (RuntimeException $exception) {
            self::assertSame('Private credential creation failed; installation must roll back.', $exception->getMessage());
            self::assertNull($exception->getPrevious());
        }
        self::assertFileDoesNotExist($this->directory.'/credentials.json');
    }

    public function test_existing_reference_is_not_overwritten_or_removed(): void
    {
        $path = $this->directory.'/credentials.json';
        file_put_contents($path, 'unrelated pre-existing reference');
        $credentials = new RailwayDemoCredentials(new BcryptHasher(['rounds' => 4]), $this->directory);
        try {
            $credentials->withCleanup(fn () => $credentials->create($this->plan()));
            self::fail('Existing references must be preserved.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('will not be overwritten', $exception->getMessage());
        }
        self::assertSame('unrelated pre-existing reference', file_get_contents($path));
    }

    public function test_noop_does_not_create_or_change_a_reference(): void
    {
        $path = $this->directory.'/credentials.json';
        file_put_contents($path, 'already installed');
        $credentials = new RailwayDemoCredentials(new BcryptHasher(['rounds' => 4]), $this->directory);
        self::assertSame(['installed' => false, 'credentials' => null], $credentials->withCleanup(fn () => ['installed' => false, 'credentials' => null]));
        self::assertSame('already installed', file_get_contents($path));
    }

    public function test_a_replaced_path_is_never_removed_by_cleanup(): void
    {
        $credentials = new RailwayDemoCredentials(new BcryptHasher(['rounds' => 4]), $this->directory);
        [, $path] = $credentials->create($this->plan());
        rename($path, $this->directory.'/original.json');
        file_put_contents($path, 'unrelated replacement');
        try {
            $credentials->discard();
            self::fail('A replaced path must require manual review.');
        } catch (RuntimeException $exception) {
            self::assertStringStartsWith('Private credential cleanup failed.', $exception->getMessage());
        }
        self::assertSame('unrelated replacement', file_get_contents($path));
        self::assertSame('', file_get_contents($this->directory.'/original.json'));
    }
}
