@csrf
<div class="row">
    <div class="col-md-3 form-group">
        <label>Code (6 chars) *</label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $promoCode->code ?? '') }}" maxlength="6" required>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-5 form-group">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $promoCode->name ?? '') }}">
    </div>
    <div class="col-md-4 form-group">
        <label>Discount Type *</label>
        <select name="discount_type" class="form-control" required>
            @php $type = old('discount_type', $promoCode->discount_type ?? 'percentage'); @endphp
            <option value="percentage" @selected($type==='percentage')>% Discount</option>
            <option value="flat" @selected($type==='flat')>Flat Discount</option>
            <option value="quantity" @selected($type==='quantity')>Quantity Discount</option>
        </select>
    </div>
</div>
<div class="row">
    <div class="col-md-3 form-group">
        <label>Discount %</label>
        <input type="number" min="0" max="100" name="discount_percent" class="form-control" value="{{ old('discount_percent', $promoCode->discount_percent ?? 0) }}">
    </div>
    <div class="col-md-3 form-group">
        <label>Flat INR</label>
        <input type="number" step="0.01" min="0" name="flat_inr" class="form-control" value="{{ old('flat_inr', isset($promoCode) ? number_format(($promoCode->flat_inr_paise ?? 0)/100,2,'.','') : '0.00') }}">
    </div>
    <div class="col-md-3 form-group">
        <label>Flat USD</label>
        <input type="number" step="0.01" min="0" name="flat_usd" class="form-control" value="{{ old('flat_usd', isset($promoCode) ? number_format(($promoCode->flat_usd_cents ?? 0)/100,2,'.','') : '0.00') }}">
    </div>
    <div class="col-md-3 form-group d-flex align-items-center">
        <div class="form-check mt-4">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $promoCode->is_active ?? true))>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3 form-group">
        <label>Quantity Buy</label>
        <input type="number" min="0" name="quantity_buy" class="form-control" value="{{ old('quantity_buy', $promoCode->quantity_buy ?? 0) }}">
    </div>
    <div class="col-md-3 form-group">
        <label>Quantity Pay</label>
        <input type="number" min="0" name="quantity_pay" class="form-control" value="{{ old('quantity_pay', $promoCode->quantity_pay ?? 0) }}">
    </div>
    <div class="col-md-3 form-group">
        <label>Max Uses</label>
        <input type="number" min="1" name="max_uses" class="form-control" value="{{ old('max_uses', $promoCode->max_uses ?? '') }}">
    </div>
    <div class="col-md-3 form-group">
        <label>Used Count</label>
        <input type="number" min="0" class="form-control" value="{{ $promoCode->used_count ?? 0 }}" disabled>
    </div>
</div>
<div class="row">
    <div class="col-md-3 form-group">
        <label>Starts At</label>
        <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', isset($promoCode) && $promoCode->starts_at ? \Illuminate\Support\Carbon::parse($promoCode->starts_at)->format('Y-m-d\\TH:i') : '') }}">
    </div>
    <div class="col-md-3 form-group">
        <label>Ends At</label>
        <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', isset($promoCode) && $promoCode->ends_at ? \Illuminate\Support\Carbon::parse($promoCode->ends_at)->format('Y-m-d\\TH:i') : '') }}">
    </div>
</div>
<div class="form-group">
    <button class="btn btn-primary" type="submit">Save</button>
    <a class="btn btn-default" href="{{ route('admin.promo-codes.index') }}">Cancel</a>
</div>
