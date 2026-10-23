<?php

namespace App\Console\Commands;

use App\Services\LicenseService;
use Illuminate\Console\Command;

class SetLicenseCommand extends Command
{
    protected $signature = 'license:set 
                            {license_key? : License key provided by vendor}
                            {--domain= : Domain the license is locked to (defaults to APP_URL host)}
                            {--force-api : Fail if the remote API cannot be reached}';

    protected $description = 'Validate license (optionally via API), encrypt it with domain, and store locally';

    public function __construct(private readonly LicenseService $licenseService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $licenseKey = $this->argument('license_key') ?? $this->secret('License key');

        if (! $licenseKey) {
            $this->error('License key is required.');
            return Command::FAILURE;
        }

        $domain = $this->option('domain') ?: $this->defaultDomain();

        if (! $domain) {
            $this->error('Could not resolve domain. Provide it explicitly with --domain=');
            return Command::FAILURE;
        }

        try {
            $this->licenseService->validateAndStore($licenseKey, $domain, $this->option('force-api'));
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return Command::FAILURE;
        }

        $this->info(sprintf('License stored for domain: %s', $domain));

        return Command::SUCCESS;
    }

    private function defaultDomain(): ?string
    {
        $appUrl = config('app.url');

        if (! $appUrl) {
            return null;
        }

        return parse_url($appUrl, PHP_URL_HOST) ?: null;
    }
}
