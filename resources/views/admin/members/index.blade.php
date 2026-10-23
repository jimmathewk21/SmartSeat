@extends('layouts.admin')
@section('content')
    @can('member_create')
        <div class="row page-actions">
            <div class="col-lg-12">
                <a class="btn btn-primary" href="{{ route('admin.members.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.member.title_singular') }}
                </a>
            </div>
        </div>
    @endcan

    <div class="card">
        <div class="card-header">
            {{ trans('cruds.member.title') }} {{ trans('global.list') }}
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('admin.members.index') }}" class="filter-form mb-3">
                <div class="filter-panel">
                    <div class="filter-panel__title">Filters</div>
                    <div class="form-row filter-panel__grid">
                        <div class="col-md-4 mb-2">
                            <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="{{ trans('global.search') }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <select class="form-control" name="is_active">
                                <option value="">{{ trans('global.all') }} {{ trans('global.status') }}</option>
                                <option value="1" @selected(request('is_active') === '1')>{{ trans('global.yes') }}</option>
                                <option value="0" @selected(request('is_active') === '0')>{{ trans('global.no') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="filter-panel">
                    <div class="filter-panel__title">Sorting</div>
                    <div class="form-row filter-panel__grid">
                        <div class="col-md-3 mb-2">
                            <select class="form-control" name="sort_by">
                                <option value="sort_order" @selected(request('sort_by', 'sort_order') === 'sort_order')>{{ trans('cruds.member.fields.sort_order') }}</option>
                                <option value="name" @selected(request('sort_by') === 'name')>{{ trans('cruds.member.fields.name') }}</option>
                                <option value="id" @selected(request('sort_by') === 'id')>{{ trans('cruds.member.fields.id') }}</option>
                                <option value="company" @selected(request('sort_by') === 'company')>{{ trans('cruds.member.fields.company') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <select class="form-control" name="sort_dir">
                                <option value="asc" @selected(request('sort_dir', 'asc') === 'asc')>Ascending</option>
                                <option value="desc" @selected(request('sort_dir') === 'desc')>Descending</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="filter-actions">
                    <button class="btn btn-primary" type="submit">{{ trans('global.search') }}</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.members.index') }}">{{ trans('global.clear') }}</a>
                </div>
            </form>

            @can('member_delete')
                <form method="POST" action="{{ route('admin.members.massDestroy') }}" onsubmit="return confirm('{{ trans('global.areYouSure') }}');">
                    @csrf
                    @method('DELETE')
                    <div class="table-actions">
                        <button class="btn btn-danger" type="submit">{{ trans('global.delete') }}</button>
                    </div>
            @endcan

            <div class="d-md-none">
                @forelse($members as $member)
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    @if($member->photo_url)
                                        <img src="{{ $member->photo_url }}" alt="{{ $member->name }}" class="img-fluid" style="max-width:50px; margin-right:8px;">
                                    @endif
                                    <strong>{{ $member->name }}</strong>
                                </div>
                                @can('member_delete')
                                    <div>
                                        <input type="checkbox" name="ids[]" value="{{ $member->id }}">
                                    </div>
                                @endcan
                            </div>
                            <div>
                                <strong>{{ trans('cruds.member.fields.company') }}:</strong>
                                {{ $member->company ?? '-' }}
                            </div>
                            <div>
                                <strong>{{ trans('global.status') }}:</strong>
                                {{ $member->is_active ? 'Published' : 'Unpublished' }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted">{{ trans('global.no_entries_in_table') }}</div>
                @endforelse
            </div>

            <div class="table-responsive d-none d-md-block">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>{{ trans('global.actions') }}</th>
                            @can('member_delete')
                                <th width="10"></th>
                            @endcan
                            <th>{{ trans('cruds.member.fields.id') }}</th>
                            <th>{{ trans('cruds.member.fields.photo_url') }}</th>
                            <th>{{ trans('cruds.member.fields.name') }}</th>
                            <th>{{ trans('cruds.member.fields.designation') }}</th>
                            <th>{{ trans('cruds.member.fields.company') }}</th>
                            <th>{{ trans('cruds.member.fields.sort_order') }}</th>
                            <th>{{ trans('global.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $member)
                            <tr>
                                <td>
                                    @include('partials.datatablesActions', [
                                        'viewGate' => 'member_show',
                                        'editGate' => 'member_edit',
                                        'deleteGate' => 'member_delete',
                                        'crudRoutePart' => 'members',
                                        'row' => $member,
                                    ])
                                </td>
                                @can('member_delete')
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $member->id }}">
                                    </td>
                                @endcan
                                <td>{{ $member->id ?? '' }}</td>
                                <td>
                                    @if($member->photo_url)
                                        <img src="{{ $member->photo_url }}" alt="{{ $member->name }}" class="img-fluid" style="max-width:70px;">
                                    @else
                                        <span class="text-muted small">No image</span>
                                    @endif
                                </td>
                                <td>{{ $member->name ?? '' }}</td>
                                <td>{{ $member->designation ?? '' }}</td>
                                <td>{{ $member->company ?? '' }}</td>
                                <td>{{ $member->sort_order ?? 0 }}</td>
                                <td>{{ $member->is_active ? 'Published' : 'Unpublished' }}</td>
                            </tr>
                        @empty
                            <tr>
                                @can('member_delete')
                                    <td colspan="10">{{ trans('global.no_entries_in_table') }}</td>
                                @else
                                    <td colspan="9">{{ trans('global.no_entries_in_table') }}</td>
                                @endcan
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $members->links() }}

            @can('member_delete')
                </form>
            @endcan
        </div>
    </div>
@endsection
