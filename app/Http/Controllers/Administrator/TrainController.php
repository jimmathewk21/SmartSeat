<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Requests\Administrator\TrainRequest;
use App\Models\Train;
use App\Services\RailwayCatalogue;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TrainController extends CatalogueController
{
    public function edit(Train $train): View
    {
        return view('administrator.edit', ['type' => 'train', 'record' => $train, 'train' => $train, 'action' => route('administrator.trains.update', $train)]);
    }

    public function index(): View
    {
        return view('administrator.trains', ['trains' => Train::withCount(['routeStops', 'coaches', 'seats', 'trips'])->orderBy('number')->paginate(20)]);
    }

    public function store(TrainRequest $request, RailwayCatalogue $catalogue): RedirectResponse
    {
        return $this->mutate(fn () => $catalogue->saveTrain($request->validated()), 'administrator.trains.index');
    }

    public function update(TrainRequest $request, Train $train, RailwayCatalogue $catalogue): RedirectResponse
    {
        return $this->mutate(fn () => $catalogue->saveTrain($request->validated(), $train), 'administrator.trains.index');
    }
}
