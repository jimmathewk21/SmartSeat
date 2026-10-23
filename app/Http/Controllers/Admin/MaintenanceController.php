<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\FrontendMaintenanceSettings;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceController extends Controller
{
    public function migrate(): RedirectResponse
    {
        abort_if(Gate::denies('developer_options'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Artisan::call('migrate', ['--force' => true]);

        return back()->with('message', 'Migrations completed successfully.');
    }

    public function cacheRefresh(): RedirectResponse
    {
        abort_if(Gate::denies('developer_options'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Artisan::call('cache:clear');

        return back()->with('message', 'Application cache cleared successfully.');
    }

    public function optimize(): RedirectResponse
    {
        abort_if(Gate::denies('developer_options'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Artisan::call('optimize');
        Artisan::call('optimize:clear');

        return back()->with('message', 'Optimize and optimize:clear completed successfully.');
    }

    public function toggleFrontend(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('developer_options'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        FrontendMaintenanceSettings::set([
            'enabled' => $request->boolean('enabled'),
        ]);

        return back()->with('message', 'Frontend maintenance mode updated successfully.');
    }
}
