@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        Sponsor Inquiries List
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('admin.sponsor-inquiries.index') }}" class="filter-form mb-3">
            <div class="filter-panel">
                <div class="filter-panel__title">Filters</div>
                <div class="form-row filter-panel__grid">
                    <div class="col-md-6 mb-2">
                        <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, company, category">
                    </div>
                </div>
            </div>
            <div class="filter-actions">
                <button class="btn btn-primary" type="submit">{{ trans('global.search') }}</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.sponsor-inquiries.index') }}">{{ trans('global.clear') }}</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ trans('global.actions') }}</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Category</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inquiries as $inquiry)
                        <tr>
                            <td>
                                <a class="btn btn-xs btn-primary" href="{{ route('admin.sponsor-inquiries.show', $inquiry) }}">{{ trans('global.view') }}</a>
                                <form method="POST" action="{{ route('admin.sponsor-inquiries.destroy', $inquiry) }}" style="display:inline-block;" onsubmit="return confirm('{{ trans('global.areYouSure') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-xs btn-danger" type="submit">{{ trans('global.delete') }}</button>
                                </form>
                            </td>
                            <td>{{ $inquiry->id }}</td>
                            <td>{{ $inquiry->name }}</td>
                            <td>{{ $inquiry->email }}</td>
                            <td>{{ $inquiry->company_name }}</td>
                            <td>{{ $inquiry->category ?: '-' }}</td>
                            <td>{{ optional($inquiry->created_at)->format('d-M-Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">{{ trans('global.no_entries_in_table') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $inquiries->links() }}
    </div>
</div>
@endsection

