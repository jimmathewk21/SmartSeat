<?php

// PREPARED ONLY. Explicit approval is mandatory; never creates or drops a database.
require dirname(__DIR__, 2).'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Tests\MariaDb\Runtime;

try {
    $target = json_decode(stream_get_contents(STDIN), true, 16, JSON_THROW_ON_ERROR);
    Runtime::boot($target, requireSchema: false);
    if (DB::getSchemaBuilder()->getTableListing() !== []) {
        throw new RuntimeException('Fresh migrations require an already-created target with no tables.');
    }
    $migrator = app('migrator');
    $migrator->getRepository()->createRepository();
    $migrator->run([database_path('migrations')]);
    Runtime::emptyTarget();
    echo "Fresh disposable migrations passed; application tables contain zero records.\n";
} catch (Throwable) {
    fwrite(STDERR, "Disposable migration preparation stopped. No cleanup was attempted; a partial schema may remain.\n");
    exit(1);
}
