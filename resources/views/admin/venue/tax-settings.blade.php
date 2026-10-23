@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        Venue Tax Settings
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.venue.tax-settings.update') }}">
            @csrf
            @method('PUT')

            @php
                $stripTaxRateToken = static function ($value) {
                    return trim(str_replace([':tax_rate% ', ':tax_rate%', ':tax_rate'], '', (string) $value));
                };
            @endphp

            <div class="form-group">
                <label for="tax_note_text">Tax Note Text</label>
                <textarea
                    name="tax_note_text"
                    id="tax_note_text"
                    rows="4"
                    class="form-control @error('tax_note_text') is-invalid @enderror"
                    required
                >{{ old('tax_note_text', $stripTaxRateToken($settings['tax_note_text'] ?? 'GST will be applicable extra on the above rates.')) }}</textarea>
                @error('tax_note_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="tax_badge_text">Tax Badge Text</label>
                <textarea
                    name="tax_badge_text"
                    id="tax_badge_text"
                    rows="3"
                    class="form-control @error('tax_badge_text') is-invalid @enderror"
                    required
                >{{ old('tax_badge_text', $stripTaxRateToken($settings['tax_badge_text'] ?? 'GST extra')) }}</textarea>
                @error('tax_badge_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="tax_summary_note_text">Summary Note Text</label>
                <textarea
                    name="tax_summary_note_text"
                    id="tax_summary_note_text"
                    rows="3"
                    class="form-control @error('tax_summary_note_text') is-invalid @enderror"
                    required
                >{{ old('tax_summary_note_text', $settings['tax_summary_note_text'] ?? '* Taxes will be additionally charged as applied.') }}</textarea>
                @error('tax_summary_note_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="alert alert-light border">
                Preview: {{ $stripTaxRateToken(old('tax_note_text', $settings['tax_note_text'] ?? 'GST will be applicable extra on the above rates.')) }}
            </div>

            <div class="alert alert-light border">
                Badge Preview: {{ $stripTaxRateToken(old('tax_badge_text', $settings['tax_badge_text'] ?? 'GST extra')) }}
            </div>

            <div class="alert alert-light border mb-4">
                Summary Preview: {{ $stripTaxRateToken(old('tax_summary_note_text', $settings['tax_summary_note_text'] ?? '* Taxes will be additionally charged as applied.')) }}
            </div>

            <button class="btn btn-primary" type="submit">Save</button>
        </form>
    </div>
</div>
@endsection
