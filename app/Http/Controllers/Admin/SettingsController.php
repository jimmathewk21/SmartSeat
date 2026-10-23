<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Requests\MassDestroySettingRequest;
use App\Http\Requests\StoreSettingRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SettingsController extends Controller
{
    use CsvImportTrait;

    public function index(Request $request)
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Setting::query()->select(sprintf('%s.*', (new Setting)->table));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('key', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter_key')) {
            $filter = $request->input('filter_key');
            $query->where('key', 'like', "%{$filter}%");
        }

        $allowedSorts = ['id', 'key'];
        $sortBy = $request->input('sort_by', 'id');
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }
        $sortDir = $request->input('sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';

        $settings = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate(25)
            ->appends($request->query());

        return view('admin.settings.index', compact('settings'));
    }

    public function create()
    {
        abort_if(Gate::denies('setting_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.settings.create');
    }

    public function store(StoreSettingRequest $request)
    {
        $setting = Setting::create($request->all());

        return redirect()->route('admin.settings.index');
    }

    public function edit(Setting $setting)
    {
        abort_if(Gate::denies('setting_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.settings.edit', compact('setting'));
    }

    public function update(UpdateSettingRequest $request, Setting $setting)
    {
        $setting->update($request->all());

        return redirect()->route('admin.settings.index');
    }

    public function show(Setting $setting)
    {
        abort_if(Gate::denies('setting_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.settings.show', compact('setting'));
    }

    public function destroy(Setting $setting)
    {
        abort_if(Gate::denies('setting_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $setting->delete();

        return back();
    }

    public function massDestroy(MassDestroySettingRequest $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back();
        }

        Setting::whereIn('id', $ids)->delete();

        return back();
    }
}
