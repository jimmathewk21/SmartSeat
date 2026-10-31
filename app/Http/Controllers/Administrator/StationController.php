<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Requests\Administrator\StationRequest;
use App\Models\Station;
use App\Services\RailwayCatalogue;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class StationController extends CatalogueController
{
    public function edit(Station $station): View
    {
        return view('administrator.edit', ['type' => 'station', 'record' => $station, 'action' => route('administrator.stations.update', $station)]);
    }

    public function index(): View
    {
        return view('administrator.stations', ['stations' => Station::orderBy('code')->paginate(20)]);
    }

    public function store(StationRequest $request, RailwayCatalogue $catalogue): RedirectResponse
    {
        return $this->mutate(fn () => $catalogue->saveStation($request->validated()), 'administrator.stations.index');
    }

    public function update(StationRequest $request, Station $station, RailwayCatalogue $catalogue): RedirectResponse
    {
        return $this->mutate(fn () => $catalogue->saveStation($request->validated(), $station), 'administrator.stations.index');
    }
}
