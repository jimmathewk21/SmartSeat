<?php

namespace App\Services\Demo;

use Illuminate\Database\Connection;
use Illuminate\Database\MySqlConnection;
use PDO;
use RuntimeException;

class RailwayDemoConnection
{
    public static function verifiedWriter(Connection $source): MySqlConnection
    {
        RailwayDemoPolicy::assertSingleConnection($source->getConfig());
        if ($source->getRawReadPdo() !== null) {
            throw new RuntimeException('An existing read PDO is not supported for installation.');
        }
        $pdo = $source->getPdo(); // Explicitly the writer; never resolve the read PDO.
        if (! $pdo instanceof PDO || $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql' || $pdo->getAttribute(PDO::ATTR_PERSISTENT)
            || $source->transactionLevel() !== 0 || $pdo->inTransaction()) {
            throw new RuntimeException('Installation requires an idle MySQL write connection.');
        }

        // A private wrapper pins every query/schema read/lock/transaction to this PDO.
        // It has no read PDO, event dispatcher or reconnector: losing the session fails closed.
        $connection = new MySqlConnection($pdo, RailwayDemoPolicy::DATABASE, '', $source->getConfig());
        $connection->useWriteConnectionWhenReading();
        RailwayDemoPolicy::assertConnectedDatabase((string) $connection->selectOne('SELECT DATABASE() AS db', [], false)->db);

        return $connection;
    }
}
