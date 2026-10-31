<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrainSearchRequest;
use App\Services\TrainSearch;
use Illuminate\Contracts\View\View;

class TrainSearchController extends Controller
{
    public function __invoke(TrainSearchRequest $request, TrainSearch $search): View
    {
        $criteria = $request->validated();
        $trips = $criteria ? $search->find($criteria) : null;
        $coaches = $trips ? $search->coaches($trips->getCollection()->pluck('id')->all()) : collect();

        return view('trains.search', compact('criteria', 'trips', 'coaches'));
    }
}
