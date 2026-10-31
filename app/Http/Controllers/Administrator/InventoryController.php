<?php

namespace App\Http\Controllers\Administrator;

use App\Models\TrainTrip;
use App\Services\RailwayCatalogue;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class InventoryController extends CatalogueController
{
    public function index(TrainTrip $trip): View
    {
        $trip->load('train');
        $inventory = $trip->inventory()->with(['seat.coach', 'activeAssignment'])->orderBy('id')->paginate(30);

        return view('administrator.inventory', ['trip' => $trip, 'train' => $trip->train, 'inventory' => $inventory]);
    }

    public function store(TrainTrip $trip, RailwayCatalogue $catalogue): RedirectResponse
    {
        return $this->mutate(fn () => $catalogue->initializeInventory($trip), 'administrator.inventory.index', [$trip]);
    }
}
