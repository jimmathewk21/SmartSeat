<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Requests\Administrator\RouteStopRequest;
use App\Models\Train;
use App\Models\TrainRouteStop;
use App\Services\RailwayCatalogue;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class RouteStopController extends CatalogueController
{
    public function edit(Train $train, TrainRouteStop $routeStop): View
    {
        return view('administrator.edit', ['type' => 'stop', 'record' => $routeStop->load('station'), 'train' => $train, 'action' => route('administrator.trains.stops.update', [$train, $routeStop])]);
    }

    public function index(Train $train): View
    {
        return view('administrator.route-stops', [
            'train' => $train, 'stops' => $train->routeStops()->with('station:id,code,name')->orderBy('stop_order')->paginate(20),
        ]);
    }

    public function store(RouteStopRequest $request, Train $train, RailwayCatalogue $catalogue): RedirectResponse
    {
        return $this->mutate(fn () => $catalogue->saveStop($train, $request->validated()), 'administrator.trains.stops.index', [$train]);
    }

    public function update(RouteStopRequest $request, Train $train, TrainRouteStop $routeStop, RailwayCatalogue $catalogue): RedirectResponse
    {
        return $this->mutate(fn () => $catalogue->saveStop($train, $request->validated(), $routeStop), 'administrator.trains.stops.index', [$train]);
    }
}
