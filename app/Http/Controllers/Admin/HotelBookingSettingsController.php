<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\HotelBookingSettings;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HotelBookingSettingsController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('hotel_booking_settings_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $settings = HotelBookingSettings::get();

        return view('admin.settings.hotel-booking', compact('settings'));
    }

    public function update(Request $request)
    {
        abort_if(Gate::denies('hotel_booking_settings_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'hotel_name' => ['nullable', 'string', 'max:255'],
            'hotel_address' => ['nullable', 'string', 'max:1000'],
            'available_from_date' => ['nullable', 'date'],
            'available_to_date' => ['nullable', 'date', 'after_or_equal:available_from_date'],
            'direction_from' => ['nullable', 'string', 'max:255'],
            'direction_to' => ['nullable', 'string', 'max:255'],
            'admin_notification_email' => ['nullable', 'email:rfc,dns', 'max:255'],
        ]);

        HotelBookingSettings::set([
            'hotel_name' => (string) ($validated['hotel_name'] ?? ''),
            'hotel_address' => (string) ($validated['hotel_address'] ?? ''),
            'available_from_date' => (string) ($validated['available_from_date'] ?? ''),
            'available_to_date' => (string) ($validated['available_to_date'] ?? ''),
            'direction_from' => (string) ($validated['direction_from'] ?? ''),
            'direction_to' => (string) ($validated['direction_to'] ?? ''),
            'admin_notification_email' => (string) ($validated['admin_notification_email'] ?? ''),
        ]);

        return redirect()->route('admin.settings.hotel-booking.edit')->with('message', 'Hotel booking settings updated.');
    }
}
