<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Requests\MassDestroyStateRequest;
use App\Http\Requests\StoreStateRequest;
use App\Http\Requests\UpdateStateRequest;
use App\Models\Country;
use App\Models\State;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StateController extends Controller
{
    use CsvImportTrait;

    public function index(Request $request)
    {
        abort_if(Gate::denies('state_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = State::with(['country'])
            ->select(sprintf('%s.*', (new State)->table));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereHas('country', function ($countryQuery) use ($search) {
                        $countryQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->input('country_id'));
        }

        $allowedSorts = ['id', 'name'];
        $sortBy = $request->input('sort_by', 'id');
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }
        $sortDir = $request->input('sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';

        $states = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate(25)
            ->appends($request->query());

        $countries = Country::orderBy('name')->get();

        return view('admin.states.index', compact('states', 'countries'));
    }

    public function create()
    {
        abort_if(Gate::denies('state_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $countries = Country::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.states.create', compact('countries'));
    }

    public function store(StoreStateRequest $request)
    {
        $state = State::create($request->all());

        return redirect()->route('admin.states.index');
    }

    public function edit(State $state)
    {
        abort_if(Gate::denies('state_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $countries = Country::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $state->load('country');

        return view('admin.states.edit', compact('countries', 'state'));
    }

    public function update(UpdateStateRequest $request, State $state)
    {
        $state->update($request->all());

        return redirect()->route('admin.states.index');
    }

    public function show(State $state)
    {
        abort_if(Gate::denies('state_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $state->load('country');

        return view('admin.states.show', compact('state'));
    }

    public function destroy(State $state)
    {
        abort_if(Gate::denies('state_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $state->delete();

        return back();
    }

    public function massDestroy(MassDestroyStateRequest $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back();
        }

        State::whereIn('id', $ids)->delete();

        return back();
    }
}
