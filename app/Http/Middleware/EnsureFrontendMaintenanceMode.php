<?php

namespace App\Http\Middleware;

use App\Support\FrontendMaintenanceSettings;
use App\Support\SiteMetaSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFrontendMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! FrontendMaintenanceSettings::enabled()) {
            return $next($request);
        }

        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        return response()->view('frontend.maintenance', [
            'siteMeta' => SiteMetaSettings::resolveForPath($request->path()),
            'maintenanceSettings' => FrontendMaintenanceSettings::get(),
        ], 503);
    }

    private function shouldBypass(Request $request): bool
    {
        return $request->is('admin')
            || $request->is('admin/*')
            || $request->is('login')
            || $request->is('logout')
            || $request->is('password/*')
            || $request->is('payments/razorpay/webhook');
    }
}
