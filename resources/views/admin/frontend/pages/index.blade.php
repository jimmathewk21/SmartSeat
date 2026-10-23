@extends('layouts.admin')
@section('content')
    <div class="row page-actions">
        <div class="col-lg-12">
            <a class="btn btn-primary" href="{{ route('admin.frontend.pages.create') }}">Add Page</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Pages List</div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.frontend.pages.index') }}" class="filter-form mb-3">
                <div class="filter-panel">
                    <div class="filter-panel__title">Filters</div>
                    <div class="form-row filter-panel__grid">
                        <div class="col-md-6 mb-2">
                            <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="{{ trans('global.search') }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <select class="form-control" name="is_active">
                                <option value="">All Status</option>
                                <option value="1" @selected(request('is_active')==='1')>{{ trans('global.yes') }}</option>
                                <option value="0" @selected(request('is_active')==='0')>{{ trans('global.no') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="filter-actions">
                    <button class="btn btn-primary" type="submit">{{ trans('global.search') }}</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.frontend.pages.index') }}">{{ trans('global.clear') }}</a>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.frontend.pages.massDestroy') }}" onsubmit="return confirm('{{ trans('global.areYouSure') }}');">
                @csrf
                @method('DELETE')
                <div class="table-actions">
                    <button class="btn btn-danger" type="submit">{{ trans('global.delete') }}</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                        <tr>
                            <th>{{ trans('global.actions') }}</th>
                            <th width="10"></th>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Active</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($pages as $page)
                            <tr>
                                <td>@include('partials.datatablesActions', ['viewGate' => 'setting_access', 'editGate' => 'setting_edit', 'deleteGate' => 'setting_delete', 'crudRoutePart' => 'frontend.pages', 'row' => $page])</td>
                                <td><input type="checkbox" name="ids[]" value="{{ $page->id }}"></td>
                                <td>{{ $page->id }}</td>
                                <td>{{ $page->title }}</td>
                                <td>{{ $page->slug }}</td>
                                <td>{{ $page->is_active ? trans('global.yes') : trans('global.no') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">{{ trans('global.no_entries_in_table') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $pages->links() }}
            </form>
        </div>
    </div>
@endsection

