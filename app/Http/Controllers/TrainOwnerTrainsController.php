<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrainOwnerTrainsController extends Controller
{
    public function index(Request $request)
    {
        $trains = $request->user()->ownedTrains()->orderBy('name')->paginate(20);

        return view('train-owner.trains.index', compact('trains'));
    }

    public function edit(Request $request, int $train)
    {
        $train = $request->user()->ownedTrains()->findOrFail($train);

        return view('train-owner.trains.edit', compact('train'));
    }

    public function update(Request $request, int $train)
    {
        $train = $request->user()->ownedTrains()->findOrFail($train);
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:trains,code,'.$train->id],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        $train->update($data);

        return redirect()->route('train-owner.trains.index')->with('status', 'Train information updated.');
    }
}
