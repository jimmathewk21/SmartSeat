<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Requests\Administrator\CoachRequest;
use App\Models\Coach;
use App\Models\Train;
use App\Services\RailwayCatalogue;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CoachController extends CatalogueController
{
    public function edit(Train $train, Coach $coach): View
    {
        return view('administrator.edit', ['type' => 'coach', 'record' => $coach, 'train' => $train, 'action' => route('administrator.trains.coaches.update', [$train, $coach])]);
    }

    public function index(Train $train): View
    {
        return view('administrator.coaches', ['train' => $train, 'coaches' => $train->coaches()->withCount('seats')->orderBy('label')->paginate(20)]);
    }

    public function store(CoachRequest $request, Train $train, RailwayCatalogue $catalogue): RedirectResponse
    {
        return $this->mutate(fn () => $catalogue->saveCoach($train, $request->validated()), 'administrator.trains.coaches.index', [$train]);
    }

    public function update(CoachRequest $request, Train $train, Coach $coach, RailwayCatalogue $catalogue): RedirectResponse
    {
        return $this->mutate(fn () => $catalogue->saveCoach($train, $request->validated(), $coach), 'administrator.trains.coaches.index', [$train]);
    }
}
