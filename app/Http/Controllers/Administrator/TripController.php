<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Requests\Administrator\TripRequest;
use App\Models\Train;
use App\Models\TrainTrip;
use App\Services\RailwayCatalogue;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TripController extends CatalogueController
{
    public function index(): View
    {
        return $this->listing();
    }

    public function forTrain(Train $train): View
    {
        return $this->listing($train);
    }

    private function listing(?Train $train = null): View
    {
        $trips = TrainTrip::with('train:id,number,name')->withCount('inventory')
            ->when($train, fn ($query) => $query->where('train_id', $train->id))
            ->orderByDesc('service_date')->orderByDesc('id')->paginate(20);

        return view('administrator.trips', compact('train', 'trips'));
    }

    public function store(TripRequest $request, Train $train, RailwayCatalogue $catalogue): RedirectResponse
    {
        return $this->mutate(fn () => $catalogue->schedule($train, $request->validated()), 'administrator.trains.trips.index', [$train]);
    }
}
