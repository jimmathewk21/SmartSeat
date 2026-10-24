<?php

namespace App\Console\Commands;

use App\Services\Demo\RailwayDemoInstaller;
use App\Services\Demo\RailwayDemoPlan;
use App\Services\Demo\RailwayDemoValidator;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Throwable;

class GenerateRailwayDemoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smartseat:railway-demo
        {--as-of= : Required fixed reference date, YYYY-MM-DD in Asia/Kolkata}
        {--seed=27001 : Deterministic business-data seed}
        {--install : Explicitly install into the isolated local smartseat_demo database}
        {--approve-target= : Must be exactly smartseat_demo when installing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Preview the synthetic Indian railway dataset offline; installation requires explicit local-only gates';

    /**
     * Execute the console command.
     */
    public function handle(RailwayDemoPlan $planner, RailwayDemoValidator $validator, RailwayDemoInstaller $installer): int
    {
        try {
            if (Config::get('telescope.enabled') !== false || Config::get('pulse.enabled') !== false) {
                throw new InvalidArgumentException('Invoke the full smartseat:railway-demo command in a fresh Artisan process; telemetry must be disabled before providers initialize.');
            }
            $seed = filter_var($this->option('seed'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 2147483647]]);
            if ($seed === false) {
                throw new InvalidArgumentException('Seed must be an integer from 0 to 2147483647.');
            }
            $plan = $planner->build(trim((string) $this->option('as-of')), $seed, (string) Config::get('app.timezone', 'UTC'));
            $validator->validate($plan);
            $this->info('SYNTHETIC DATA ONLY — not official Indian Railways services, station codes or timetables.');
            $this->table(['Table', 'Planned rows'], collect($plan['tables'])->map(fn ($rows, $table) => [$table, count($rows)])->values()->all());
            $this->line('Bookings: 1300 confirmed + 200 cancelled. Trips: 240 completed + 60 scheduled.');
            $this->line('Waitlist: 100 waiting + 30 promoted + 20 cancelled. Owners: 5 pending administrator approval.');
            $this->line('Plan fingerprint: '.RailwayDemoPlan::fingerprint($plan));
            if (! $this->option('install')) {
                $this->info('PREVIEW ONLY. No database connection, credential generation or data writes.');

                return self::SUCCESS;
            }
            $result = $installer->install($plan, (string) $this->option('approve-target'));
            $this->info($result['installed'] ? 'Synthetic snapshot installed. Owner approval is still required.' : 'Identical snapshot is already installed. No records or passwords were changed.');
            if ($result['credentials'] !== null) {
                $this->line('Private development-only credential reference: '.$result['credentials']);
            }

            return self::SUCCESS;
        } catch (QueryException) {
            $this->error('Installation database operation failed. Credential cleanup was attempted; review transaction/marker state before retrying.');

            return self::FAILURE;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
