<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FreshInstallationTest extends TestCase
{
    use DatabaseTransactions;

    public function createApplication()
    {
        $app = parent::createApplication();
        // The parent has already removed every connection except SQLite :memory:.
        foreach (glob(dirname(__DIR__, 2).'/database/migrations/*.php') as $path) {
            (require $path)->up();
        }

        return $app;
    }

    public function test_all_fresh_migrations_and_the_empty_seeder_create_no_application_data(): void
    {
        app(DatabaseSeeder::class)->run();
        foreach (DB::getSchemaBuilder()->getTableListing() as $table) {
            $this->assertDatabaseCount($table, 0);
        }
        $this->assertSame(1, (int) DB::selectOne('PRAGMA foreign_keys')->foreign_keys);
        $this->get(route('home'))->assertOk();
        $this->get(route('trains.search'))->assertOk()->assertSee('Your next chapter awaits.');
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('waitlist_prediction_snapshots', 0);
    }
}
