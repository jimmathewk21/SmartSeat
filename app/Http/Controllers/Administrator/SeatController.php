<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Requests\Administrator\SeatRequest;
use App\Models\Coach;
use App\Models\Seat;
use App\Models\Train;
use App\Services\RailwayCatalogue;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SeatController extends CatalogueController
{
    public function edit(Train $train, Coach $coach, Seat $seat): View
    {
        return view('administrator.edit', ['type' => 'seat', 'record' => $seat, 'train' => $train, 'action' => route('administrator.trains.coaches.seats.update', [$train, $coach, $seat])]);
    }

    public function index(Train $train, Coach $coach): View
    {
        return view('administrator.seats', ['train' => $train, 'coach' => $coach, 'seats' => $coach->seats()->orderBy('number')->paginate(25)]);
    }

    public function store(SeatRequest $request, Train $train, Coach $coach, RailwayCatalogue $catalogue): RedirectResponse
    {
        return $this->mutate(fn () => $catalogue->saveSeat($train, $coach, $request->validated()), 'administrator.trains.coaches.seats.index', [$train, $coach]);
    }

    public function update(SeatRequest $request, Train $train, Coach $coach, Seat $seat, RailwayCatalogue $catalogue): RedirectResponse
    {
        return $this->mutate(fn () => $catalogue->saveSeat($train, $coach, $request->validated(), $seat), 'administrator.trains.coaches.seats.index', [$train, $coach]);
    }
}
