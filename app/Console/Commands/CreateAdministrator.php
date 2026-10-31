<?php

namespace App\Console\Commands;

use App\Actions\CreateAdministratorAccount;
use App\Services\AdministratorTarget;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CreateAdministrator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-administrator {--database= : Exact, explicitly permitted local rebuild database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interactively create a new administrator on an approved local rebuild database';

    /**
     * Execute the console command.
     */
    public function handle(AdministratorTarget $target, CreateAdministratorAccount $accounts): int
    {
        $database = $this->option('database');

        if (! $this->input->isInteractive() || ! is_string($database) || $database === '') {
            $this->error('An interactive terminal and an explicit --database name are required.');

            return self::FAILURE;
        }

        if (! $this->confirm("Create a new administrator in [{$database}]?", false)) {
            $this->info('Administrator creation cancelled.');

            return self::FAILURE;
        }

        try {
            $target->verify($database);

            $this->info('Use at least 12 characters with uppercase, lowercase, a number and a symbol (maximum 72 bytes).');
            $details = [
                'name' => $this->ask('Administrator name'),
                'email' => $this->ask('Administrator email'),
                // Never fall back to visible input if terminal hiding is unavailable.
                'password' => $this->secret('Password', false),
                'password_confirmation' => $this->secret('Confirm password', false),
            ];

            $accounts->create($details);
            unset($details);
        } catch (ValidationException $exception) {
            foreach ($exception->validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        } catch (QueryException) {
            $this->error('The account could not be inserted. No existing account was promoted or overwritten.');

            return self::FAILURE;
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Administrator account created.');

        return self::SUCCESS;
    }
}
