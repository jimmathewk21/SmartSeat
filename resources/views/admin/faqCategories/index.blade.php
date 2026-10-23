@extends('layouts.admin')
@section('content')
    @can('faq_category_create')
        <div class="row page-actions">
            <div class="col-lg-12">
                <a class="btn btn-primary" href="{{ route('admin.faq-categories.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.faqCategory.title_singular') }}
                </a>
            </div>
        </div>
    @endcan

    <div class="card">
        <div class="card-header">
            {{ trans('cruds.faqCategory.title') }} {{ trans('global.list') }}
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('admin.faq-categories.index') }}" class="filter-form mb-3">
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
                                <option value="sort_order" @selected(request('sort_by', 'sort_order') === 'sort_order')>{{ trans('cruds.faqCategory.fields.sort_order') }}</option>
                                <option value="name" @selected(request('sort_by') === 'name')>{{ trans('cruds.faqCategory.fields.name') }}</option>
                                <option value="id" @selected(request('sort_by') === 'id')>{{ trans('cruds.faqCategory.fields.id') }}</option>
                                <option value="is_active" @selected(request('sort_by') === 'is_active')>{{ trans('cruds.faqCategory.fields.is_active') }}</option>
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
                    <a class="btn btn-outline-secondary" href="{{ route('admin.faq-categories.index') }}">{{ trans('global.clear') }}</a>
                </div>
            </form>

            @can('faq_category_delete')
                <form method="POST" action="{{ route('admin.faq-categories.massDestroy') }}" onsubmit="return confirm('{{ trans('global.areYouSure') }}');">
                    @csrf
                    @method('DELETE')
                    <div class="table-actions">
                        <button class="btn btn-danger" type="submit">{{ trans('global.delete') }}</button>
                    </div>
            @endcan

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>{{ trans('global.actions') }}</th>
                            @can('faq_category_delete')
                                <th width="10"></th>
                            @endcan
                            <th>{{ trans('cruds.faqCategory.fields.id') }}</th>
                            <th>{{ trans('cruds.faqCategory.fields.name') }}</th>
                            <th>{{ trans('cruds.faqCategory.fields.sort_order') }}</th>
                            <th>{{ trans('cruds.faqCategory.fields.is_active') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($faqCategories as $faqCategory)
                            <tr>
                                <td>
                                    @include('partials.datatablesActions', [
                                        'viewGate' => 'faq_category_show',
                                        'editGate' => 'faq_category_edit',
                                        'deleteGate' => 'faq_category_delete',
                                        'crudRoutePart' => 'faq-categories',
                                        'row' => $faqCategory,
                                    ])
                                </td>
                                @can('faq_category_delete')
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $faqCategory->id }}">
                                    </td>
                                @endcan
                                <td>{{ $faqCategory->id }}</td>
                                <td>{{ $faqCategory->name }}</td>
                                <td>{{ $faqCategory->sort_order }}</td>
                                <td>{{ $faqCategory->is_active ? trans('global.yes') : trans('global.no') }}</td>
                            </tr>
                        @empty
                            <tr>
                                @can('faq_category_delete')
                                    <td colspan="6">{{ trans('global.no_entries_in_table') }}</td>
                                @else
                                    <td colspan="5">{{ trans('global.no_entries_in_table') }}</td>
                                @endcan
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $faqCategories->links() }}

            @can('faq_category_delete')
                </form>
            @endcan
        </div>
    </div>
@endsection

