<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\VenuePageSettings;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VenuePageSettingsController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $settings = VenuePageSettings::get();

        return view('admin.venue.page-settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'hero_label' => ['nullable', 'string', 'max:120'],
            'hero_title' => ['required', 'string', 'max:160'],
            'hero_title_suffix' => ['nullable', 'string', 'max:160'],
            'hero_description' => ['nullable', 'string', 'max:5000'],
            'empty_state_text' => ['nullable', 'string', 'max:255'],
        ]);

        VenuePageSettings::set($validated);

        return redirect()
            ->route('admin.venue.page-settings.edit')
            ->with('message', 'Venue page settings updated successfully.');
    }

    public function editReservation()
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $settings = VenuePageSettings::get();

        return view('admin.venue.reservation-settings', compact('settings'));
    }

    public function updateReservation(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'reservation_eyebrow_text' => ['nullable', 'string', 'max:120'],
            'reservation_heading' => ['required', 'string', 'max:160'],
            'reservation_heading_suffix' => ['nullable', 'string', 'max:160'],
            'reservation_description' => ['nullable', 'string', 'max:5000'],
        ]);

        VenuePageSettings::set($validated);

        return redirect()
            ->route('admin.venue.reservation-settings.edit')
            ->with('message', 'Reservation page settings updated successfully.');
    }

    public function editTax()
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $settings = VenuePageSettings::get();

        return view('admin.venue.tax-settings', compact('settings'));
    }

    public function updateTax(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'tax_rate' => ['nullable', 'numeric', 'min:0'],
            'tax_note_text' => ['required', 'string', 'max:5000'],
            'tax_badge_text' => ['required', 'string', 'max:5000'],
            'tax_summary_note_text' => ['required', 'string', 'max:5000'],
        ]);

        if ($request->filled('tax_rate')) {
            $validated['tax_rate'] = rtrim(rtrim(number_format((float) $validated['tax_rate'], 2, '.', ''), '0'), '.');
        } else {
            unset($validated['tax_rate']);
        }

        VenuePageSettings::set($validated);

        return redirect()
            ->route('admin.venue.tax-settings.edit')
            ->with('message', 'Venue tax settings updated successfully.');
    }
}
