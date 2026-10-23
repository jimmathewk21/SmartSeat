@extends('layouts.admin')
@section('content')
<div class="row page-actions">
    <div class="col-lg-12">
        <a class="btn btn-primary" href="{{ route('admin.promo-codes.create') }}">Add Promo Code</a>
    </div>
</div>

<div class="card">
    <div class="card-header">Promo Codes</div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="form-row">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search code/name">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary">Search</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.promo-codes.index') }}">Clear</a>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                <tr>
                    <th>Actions</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Used</th>
                    <th>Active</th>
                </tr>
                </thead>
                <tbody>
                @forelse($promoCodes as $promoCode)
                    <tr>
                        <td>
                            <a class="btn btn-xs btn-info" href="{{ route('admin.promo-codes.edit', $promoCode) }}">Edit</a>
                            <form class="d-inline-block" method="POST" action="{{ route('admin.promo-codes.destroy', $promoCode) }}" onsubmit="return confirm('Delete this promo code?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" type="submit">Delete</button>
                            </form>
                        </td>
                        <td>{{ $promoCode->code }}</td>
                        <td>{{ $promoCode->name ?: '-' }}</td>
                        <td>{{ ucfirst($promoCode->discount_type) }}</td>
                        <td>{{ $promoCode->used_count }} / {{ $promoCode->max_uses ?: '∞' }}</td>
                        <td>{{ $promoCode->is_active ? 'Yes' : 'No' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">{{ trans('global.no_entries_in_table') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $promoCodes->links() }}
    </div>
</div>
@endsection

