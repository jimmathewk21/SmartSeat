<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\TicketCheckoutSettings;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TicketCheckoutSettingsController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $settings = TicketCheckoutSettings::get();
        return view('admin.settings.ticket-checkout', compact('settings'));
    }

    public function update(Request $request)
    {
        abort_if(Gate::denies('setting_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'gateway_charge_type' => ['required', 'in:none,percent,flat'],
            'gateway_charge_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'gateway_charge_inr' => ['nullable', 'numeric', 'min:0'],
            'gateway_charge_usd' => ['nullable', 'numeric', 'min:0'],
            'min_ticket_quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'max_ticket_quantity' => ['required', 'integer', 'min:1', 'max:1000', 'gte:min_ticket_quantity'],
            'plan_caption' => ['nullable', 'string', 'max:255'],
        ]);

        TicketCheckoutSettings::set([
            'gateway_charge_type' => $validated['gateway_charge_type'],
            'gateway_charge_percent' => (float) ($validated['gateway_charge_percent'] ?? 0),
            'gateway_charge_inr' => number_format((float) ($validated['gateway_charge_inr'] ?? 0), 2, '.', ''),
            'gateway_charge_usd' => number_format((float) ($validated['gateway_charge_usd'] ?? 0), 2, '.', ''),
            'min_ticket_quantity' => (int) $validated['min_ticket_quantity'],
            'max_ticket_quantity' => (int) $validated['max_ticket_quantity'],
            'plan_caption' => trim((string) ($validated['plan_caption'] ?? '')),
        ]);

        return redirect()->route('admin.settings.ticket-checkout.edit')->with('message', 'Ticket checkout settings updated.');
    }
}
