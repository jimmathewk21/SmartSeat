@extends('layouts.admin')

@section('content')
    <div class="row page-actions">
        <div class="col-lg-12">
            <a class="btn btn-primary" href="{{ route('admin.frontend.seo-titles.create') }}">Add Page Title</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Page Titles</div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.frontend.seo-titles.index') }}" class="filter-form mb-3">
                <div class="filter-panel">
                    <div class="filter-panel__title">Filters</div>
                    <div class="form-row filter-panel__grid">
                        <div class="col-md-6 mb-2">
                            <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="{{ trans('global.search') }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <select class="form-control" name="is_active">
                                <option value="">All Status</option>
                                <option value="1" @selected(request('is_active') === '1')>{{ trans('global.yes') }}</option>
                                <option value="0" @selected(request('is_active') === '0')>{{ trans('global.no') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="filter-actions">
                    <button class="btn btn-primary" type="submit">{{ trans('global.search') }}</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.frontend.seo-titles.index') }}">{{ trans('global.clear') }}</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                    <tr>
                        <th>{{ trans('global.actions') }}</th>
                        <th>ID</th>
                        <th>Page Link</th>
                        <th>Page Title</th>
                        <th>Active</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($seoTitles as $seoTitle)
                        <tr>
                            <td>
                                @can('setting_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.frontend.seo-titles.edit', $seoTitle) }}">{{ trans('global.edit') }}</a>
                                @endcan
                                @can('setting_delete')
                                    <form action="{{ route('admin.frontend.seo-titles.destroy', $seoTitle) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-xs btn-danger" type="submit">{{ trans('global.delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                            <td>{{ $seoTitle->id }}</td>
                            <td>{{ $seoTitle->path }}</td>
                            <td>{{ $seoTitle->title }}</td>
                            <td>{{ $seoTitle->is_active ? trans('global.yes') : trans('global.no') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">{{ trans('global.no_entries_in_table') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{ $seoTitles->links() }}
        </div>
    </div>
@endsection
