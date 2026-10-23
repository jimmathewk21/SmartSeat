<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\FrontendPageAccessSettings;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FrontendPageAccessController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.frontend.page-access', [
            'definitions' => FrontendPageAccessSettings::definitions(),
            'settings'    => FrontendPageAccessSettings::get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('setting_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $pages = $request->input('pages', []);

        $data = [];
        foreach (array_keys(FrontendPageAccessSettings::definitions()) as $key) {
            $data[$key] = [
                'user'   => isset($pages[$key]['user']),
                'member' => isset($pages[$key]['member']),
                'guest'  => isset($pages[$key]['guest']),
            ];
        }

        FrontendPageAccessSettings::set($data);

        return redirect()
            ->route('admin.frontend.page-access.edit')
            ->with('message', 'Frontend page access settings updated successfully.');
    }
}
