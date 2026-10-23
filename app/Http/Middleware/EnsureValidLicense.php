<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidLicense
{
    public function __construct(private readonly LicenseService $licenseService)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment(['local', 'testing'])) {
            return $next($request);
        }

        $bundle = $this->licenseService->getBundle();

        if (! $bundle) {
            return response()->json([
                'success' => false,
                'message' => 'License not set. Please run: php artisan license:set',
            ], 403);
        }

        $domain = $this->resolveDomain($request);

        $cacheKey = $this->cacheKey($bundle, $domain);

        $valid = Cache::remember($cacheKey, now()->addDay(), function () use ($bundle, $domain) {
            return $domain && $this->licenseService->isBundleValidForDomain($bundle, $domain);
        });

        if (! $valid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid license for this domain (dev hosts: localhost / dev.<domain> allowed).',
            ], 403);
        }

        return $next($request);
    }

    private function resolveDomain(Request $request): ?string
    {
        $host = $request->getHost();

        if ($host) {
            return $host;
        }

        $appUrl = config('app.url');

        if (! $appUrl) {
            return null;
        }

        return parse_url($appUrl, PHP_URL_HOST) ?: null;
    }

    private function cacheKey(array $bundle, ?string $domain): string
    {
        $sig = $bundle['signature'] ?? 'none';
        $dom = $domain ?: 'unknown';

        return 'license_valid:' . $sig . ':' . $dom;
    }
}
