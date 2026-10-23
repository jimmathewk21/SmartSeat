@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Ticket Checkout Settings</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.ticket-checkout.update') }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Min Ticket Quantity</label>
                    <input type="number" step="1" min="1" max="1000" name="min_ticket_quantity" class="form-control" value="{{ old('min_ticket_quantity', $settings['min_ticket_quantity']) }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Max Ticket Quantity</label>
                    <input type="number" step="1" min="1" max="1000" name="max_ticket_quantity" class="form-control" value="{{ old('max_ticket_quantity', $settings['max_ticket_quantity']) }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Gateway Charge Type</label>
                    <select name="gateway_charge_type" class="form-control">
                        <option value="none" @selected(old('gateway_charge_type', $settings['gateway_charge_type'])==='none')>None</option>
                        <option value="percent" @selected(old('gateway_charge_type', $settings['gateway_charge_type'])==='percent')>Percent</option>
                        <option value="flat" @selected(old('gateway_charge_type', $settings['gateway_charge_type'])==='flat')>Flat</option>
                    </select>
                    <small class="form-text text-muted">Use <strong>Flat</strong> to apply different fixed gateway charges for INR and USD checkouts.</small>
                </div>
                <div class="col-md-3 form-group">
                    <label>Gateway Charge %</label>
                    <input type="number" step="0.01" min="0" max="100" name="gateway_charge_percent" class="form-control" value="{{ old('gateway_charge_percent', $settings['gateway_charge_percent']) }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Gateway Charge INR</label>
                    <input type="number" step="0.01" min="0" name="gateway_charge_inr" class="form-control" value="{{ old('gateway_charge_inr', $settings['gateway_charge_inr']) }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Gateway Charge USD</label>
                    <input type="number" step="0.01" min="0" name="gateway_charge_usd" class="form-control" value="{{ old('gateway_charge_usd', $settings['gateway_charge_usd']) }}">
                </div>
                <div class="col-md-12 form-group">
                    <label for="plan_caption">Ticket Plan Caption (Frontend)</label>
                    <input type="text" name="plan_caption" id="plan_caption" class="form-control" value="{{ old('plan_caption', $settings['plan_caption'] ?? '') }}" maxlength="255" placeholder="Conference access with curated benefits and member-ready pricing.">
                    <small class="form-text text-muted">Shown below each plan title on the frontend ticket page.</small>
                </div>
            </div>
            <button class="btn btn-primary" type="submit">Save</button>
        </form>
    </div>
</div>
@endsection
