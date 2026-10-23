<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginActivity;
use App\Services\LoginActivityGeoService;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginActivitiesController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('login_activity_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $geoService = app(LoginActivityGeoService::class);
        $query = LoginActivity::with([
            'user:id,name,email,company_name',
        ])->select(sprintf('%s.*', (new LoginActivity)->table));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('user_agent', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $allowedSorts = ['id', 'attempted_at', 'created_at'];
        $sortBy = $request->input('sort_by', 'attempted_at');
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'attempted_at';
        }
        $sortDir = $request->input('sort_dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $loginActivities = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate(25)
            ->appends($request->query());

        $loginActivities->getCollection()->transform(function (LoginActivity $loginActivity) use ($geoService) {
            $loginActivity->setAttribute('geo_location', $geoService->lookup($loginActivity->ip_address));

            return $loginActivity;
        });

        return view('admin.loginActivities.index', compact('loginActivities'));
    }

    public function show(LoginActivity $loginActivity)
    {
        abort_if(Gate::denies('login_activity_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $loginActivity->load(['user:id,name,email,company_name']);
        $loginActivity->setAttribute('geo_location', app(LoginActivityGeoService::class)->lookup($loginActivity->ip_address));

        return view('admin.loginActivities.show', compact('loginActivity'));
    }

    public function clearAll()
    {
        abort_if(Gate::denies('login_activity_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        LoginActivity::query()->delete();

        return redirect()
            ->route('admin.login-activities.index')
            ->with('message', 'All login attempts deleted successfully.');
    }
}
