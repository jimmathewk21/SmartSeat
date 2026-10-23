<?php

namespace App\Console\Commands;

use App\Services\LocalAdminBootstrapGuard;
use App\Services\LocalAdminBootstrapService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

class BootstrapLocalAdminCommand extends Command
{
    protected $signature = 'smartseat:bootstrap-local-admin';

    protected $description = 'Create a narrowly permissioned administrator in the local smartseat_dev database';

    public function __construct(
        private readonly LocalAdminBootstrapGuard $guard,
        private readonly LocalAdminBootstrapService $bootstrapService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $this->guard->assertAllowed();
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Administrator name'));
        $email = strtolower(trim((string) $this->ask('Administrator email')));

        $validator = Validator::make(compact('name', 'email'), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        try {
            if ($this->bootstrapService->isAlreadyProvisioned($name, $email)) {
                $this->info('This administrator is already provisioned. No changes were made.');

                return self::SUCCESS;
            }
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $password = (string) $this->secret('Administrator password (minimum 12 characters)');
        $confirmation = (string) $this->secret('Confirm administrator password');

        if ($password !== $confirmation) {
            $this->error('The password confirmation did not match. No changes were made.');

            return self::FAILURE;
        }

        if (strlen($password) < 12) {
            $this->error('The password must contain at least 12 characters. No changes were made.');

            return self::FAILURE;
        }

        try {
            $created = $this->bootstrapService->provision($name, $email, $password);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } catch (Throwable) {
            $this->error('Administrator bootstrap failed. No changes were committed; inspect application logs before retrying.');

            return self::FAILURE;
        } finally {
            unset($password, $confirmation);
        }

        $this->info($created
            ? 'Local administrator provisioned. Sign in through the existing /login page.'
            : 'This administrator is already provisioned. No changes were made.');

        return self::SUCCESS;
    }
}
