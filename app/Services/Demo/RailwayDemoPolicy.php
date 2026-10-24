<?php

namespace App\Services\Demo;

use RuntimeException;

/** Pure safety decisions, separately testable without booting Laravel or connecting. */
class RailwayDemoPolicy
{
    public const DATABASE = 'smartseat_demo';

    public const MARKER = 's27_railway_demo_v1';

    public static function assertConfiguration(string $environment, string $connection, string $driver, string $configuredDatabase, string $acknowledgement): void
    {
        if ($environment !== 'local' || $connection !== 'mysql' || $driver !== 'mysql'
            || $configuredDatabase !== self::DATABASE || $acknowledgement !== self::DATABASE) {
            throw new RuntimeException('Installation requires APP_ENV=local, DB_CONNECTION=mysql, DB_DATABASE=smartseat_demo and --approve-target=smartseat_demo. No database was accessed.');
        }
    }

    public static function assertConnectedDatabase(string $actual): void
    {
        if ($actual !== self::DATABASE) {
            throw new RuntimeException('Connected database must be smartseat_demo. Installation refused.');
        }
    }

    public static function assertSingleConnection(array $configuration): void
    {
        // Reject even empty split definitions: no replica, failover or URL override is supported.
        if (($configuration['driver'] ?? null) !== 'mysql'
            || ($configuration['database'] ?? null) !== self::DATABASE
            || array_key_exists('read', $configuration) || array_key_exists('write', $configuration)
            || ! empty($configuration['url']) || ! empty($configuration['sticky'])
            || ! empty($configuration['prefix'])
            || ! is_string($configuration['host'] ?? null) || $configuration['host'] === ''
            || ! is_array($configuration['options'] ?? [])
            || ! empty(($configuration['options'] ?? [])[\PDO::ATTR_PERSISTENT])
            || (defined('PDO::MYSQL_ATTR_INIT_COMMAND') && array_key_exists(constant('PDO::MYSQL_ATTR_INIT_COMMAND'), $configuration['options'] ?? []))) {
            throw new RuntimeException('Installation requires one explicit, non-persistent MySQL connection to smartseat_demo, without URL overrides, split routing or table prefixes.');
        }
    }

    public static function alreadyInstalled(?string $marker, string $fingerprint): bool
    {
        if ($marker === null) {
            return false;
        }
        $decoded = json_decode($marker, true, 512, JSON_THROW_ON_ERROR);
        if (($decoded['version'] ?? null) !== RailwayDemoPlan::VERSION || ($decoded['fingerprint'] ?? null) !== $fingerprint) {
            throw new RuntimeException('An installation marker already exists for a different plan. Existing data will not be changed.');
        }

        return true;
    }

    public static function assertRole(string $kind, array $roles, array $permissions = []): int
    {
        $title = $kind === 'owner' ? 'Train Owner' : 'User';
        if (count($roles) !== 1 || $roles[0]['title'] !== $title || (int) $roles[0]['id'] <= 1 || $roles[0]['deleted_at'] !== null) {
            throw new RuntimeException('Requires one active, non-Admin '.$title.' role. Shared roles will not be changed.');
        }
        if ($kind === 'owner' && array_diff(['train_management_access', 'train_management_edit'], $permissions) !== []) {
            throw new RuntimeException('Train Owner role requires both existing owner permissions. The generator will not grant permissions.');
        }

        return (int) $roles[0]['id'];
    }
}
