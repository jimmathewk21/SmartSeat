<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        return $request->user()->isAdministrator()
            ? redirect()->route('administrator.dashboard')
            : view('dashboard');
    }
}
