<?php

namespace Tests\MariaDb;

use InvalidArgumentException;

final class ApprovedTarget
{
    public const DATABASE = 'smartseat_rebuild_test';

    public const APPROVAL = 'I approve fresh migrations and disposable concurrency tests on smartseat_rebuild_test';

    /** Pure guard: this method does not connect, create a DB or modify configuration. */
    public static function validate(array $input): array
    {
        $allowed = ['approval', 'database', 'host', 'port', 'username', 'password'];
        if (array_diff(array_keys($input), $allowed)
            || ($input['approval'] ?? null) !== self::APPROVAL
            || ($input['database'] ?? null) !== self::DATABASE
            || ! in_array($input['host'] ?? null, ['127.0.0.1', 'localhost', '::1'], true)
            || ! is_int($input['port'] ?? null) || $input['port'] < 1 || $input['port'] > 65535
            || ($input['username'] ?? null) !== 'smartseat_test_runner'
            || ! is_string($input['password'] ?? null) || $input['password'] === '') {
            throw new InvalidArgumentException('Explicit approval and an exact disposable local target with restricted credentials are required.');
        }

        return $input;
    }
}
