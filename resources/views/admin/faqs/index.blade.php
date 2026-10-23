@extends('layouts.admin')

@section('styles')
    @parent
    <style>
        .faq-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .faq-toolbar__actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .faq-filter-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            align-items: end;
        }

        .faq-filter-row > .filter-panel {
            min-width: 0;
        }

        .faq-filter-row > .filter-panel:first-child .form-row {
            grid-template-columns: repeat(4, minmax(140px, 210px));
            justify-content: start;
            gap: 10px;
        }

        .faq-filter-row .filter-actions {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            white-space: normal;
            align-self: flex-start;
            margin: 0;
        }

        .faq-filter-row .form-control {
            min-height: 58px;
            font-size: 1.1rem;
            border-radius: 16px;
        }

        @media (max-width: 1199.98px) {
            .faq-filter-row > .filter-panel:first-child .form-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .faq-filter-row > .filter-panel:first-child .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    @can('faq_create')
        <div class="faq-toolbar">
            <a class="btn btn-primary" href="{{ route('admin.faqs.create') }}">{{ trans('global.add') }} {{ trans('cruds.faq.title_singular') }}</a>
            <div class="faq-toolbar__actions">
                <a class="btn btn-outline-secondary" href="{{ route('admin.faqs.page-settings.edit') }}">Page Settings</a>
                <a class="btn btn-outline-primary" href="{{ route('admin.faq-questions.index') }}">Asked Questions</a>
                <a class="btn btn-outline-success" href="{{ route('admin.faqs.export', request()->query()) }}">Export CSV</a>
                @can('faq_delete')
                    <button class="btn btn-danger" type="submit" form="faqDeleteForm" onclick="return confirm('{{ trans('global.areYouSure') }}');">{{ trans('global.delete') }}</button>
                @endcan
                @can('faq_edit')
                    <button class="btn btn-success" type="button" data-bulk-status="publish">Publish Selected</button>
                    <button class="btn btn-warning" type="button" data-bulk-status="unpublish">Unpublish Selected</button>
                @endcan
            </div>
        </div>
    @else
        <div class="faq-toolbar">
            <div></div>
            <div class="faq-toolbar__actions">
                <a class="btn btn-outline-secondary" href="{{ route('admin.faqs.page-settings.edit') }}">Page Settings</a>
                <a class="btn btn-outline-primary" href="{{ route('admin.faq-questions.index') }}">Asked Questions</a>
                <a class="btn btn-outline-success" href="{{ route('admin.faqs.export', request()->query()) }}">Export CSV</a>
                @can('faq_delete')
                    <button class="btn btn-danger" type="submit" form="faqDeleteForm" onclick="return confirm('{{ trans('global.areYouSure') }}');">{{ trans('global.delete') }}</button>
                @endcan
                @can('faq_edit')
                    <button class="btn btn-success" type="button" data-bulk-status="publish">Publish Selected</button>
                    <button class="btn btn-warning" type="button" data-bulk-status="unpublish">Unpublish Selected</button>
                @endcan
            </div>
        </div>
    @endcan

    <div class="card">
        <div class="card-header">{{ trans('cruds.faq.title') }} {{ trans('global.list') }}</div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.faqs.index') }}" class="filter-form mb-3">
                <div class="faq-filter-row">
                    <div class="filter-panel">
                        <div class="filter-panel__title">Filters</div>
                        <div class="form-row filter-panel__grid">
                            <div class="col-md-4 mb-2"><input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="{{ trans('global.search') }}"></div>
                            <div class="col-md-3 mb-2"><select class="form-control" name="faq_category_id"><option value="">{{ trans('global.all') }} {{ trans('cruds.faq.fields.category') }}</option>@foreach($faqCategories as $faqCategory)<option value="{{ $faqCategory->id }}" @selected((string)$faqCategory->id === (string)request('faq_category_id'))>{{ $faqCategory->name }}</option>@endforeach</select></div>
                            <div class="col-md-2 mb-2"><select class="form-control" name="is_active"><option value="">{{ trans('global.all') }} {{ trans('global.status') }}</option><option value="1" @selected(request('is_active')==='1')>{{ trans('global.yes') }}</option><option value="0" @selected(request('is_active')==='0')>{{ trans('global.no') }}</option></select></div>
                            <div class="col-md-2 mb-2"><select class="form-control" name="show_on_homepage"><option value="">{{ trans('global.all') }} {{ trans('cruds.faq.fields.show_on_homepage') }}</option><option value="1" @selected(request('show_on_homepage')==='1')>{{ trans('global.yes') }}</option><option value="0" @selected(request('show_on_homepage')==='0')>{{ trans('global.no') }}</option></select></div>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button class="btn btn-primary" type="submit">{{ trans('global.search') }}</button>
                        <a class="btn btn-outline-secondary" href="{{ route('admin.faqs.index') }}">{{ trans('global.clear') }}</a>
                    </div>
                </div>
            </form>

            @canany(['faq_delete', 'faq_edit'])
                <form id="faqBulkForm" method="POST" action="{{ route('admin.faqs.bulkStatus') }}">
                    @csrf
                    <input type="hidden" name="status" id="faqBulkStatus" value="">
                </form>
                <form id="faqDeleteForm" method="POST" action="{{ route('admin.faqs.massDestroy') }}">
                    @csrf
                    @method('DELETE')
                </form>
            @endcanany

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>{{ trans('global.actions') }}</th>
                            @canany(['faq_delete', 'faq_edit'])
                                <th width="10">
                                    <input type="checkbox" id="select-all-faqs" aria-label="Select all FAQs">
                                </th>
                            @endcanany
                            <th>{{ trans('cruds.faq.fields.id') }}</th>
                            <th>{{ trans('cruds.faq.fields.category') }}</th>
                            <th>{{ trans('cruds.faq.fields.question') }}</th>
                            <th>{{ trans('cruds.faq.fields.sort_order') }}</th>
                            <th>{{ trans('cruds.faq.fields.show_on_homepage') }}</th>
                            <th>{{ trans('cruds.faq.fields.is_active') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($faqs as $faq)
                            <tr>
                                <td>@include('partials.datatablesActions', ['viewGate' => 'faq_show', 'editGate' => 'faq_edit', 'deleteGate' => 'faq_delete', 'crudRoutePart' => 'faqs', 'row' => $faq])</td>
                                @canany(['faq_delete', 'faq_edit'])
                                    <td><input type="checkbox" name="ids[]" value="{{ $faq->id }}" class="faq-row-checkbox" form="faqBulkForm"></td>
                                @endcanany
                                <td>{{ $faq->id }}</td>
                                <td>{{ $faq->category?->name }}</td>
                                <td>{{ $faq->question }}</td>
                                <td>{{ $faq->sort_order }}</td>
                                <td>{{ $faq->show_on_homepage ? trans('global.yes') : trans('global.no') }}</td>
                                <td>{{ $faq->is_active ? trans('global.yes') : trans('global.no') }}</td>
                            </tr>
                        @empty
                            <tr>@canany(['faq_delete', 'faq_edit'])<td colspan="8">{{ trans('global.no_entries_in_table') }}</td>@else<td colspan="7">{{ trans('global.no_entries_in_table') }}</td>@endcanany</tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $faqs->links() }}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            const bulkForm = document.getElementById('faqBulkForm');
            const deleteForm = document.getElementById('faqDeleteForm');
            const selectAll = document.getElementById('select-all-faqs');
            const statusInput = document.getElementById('faqBulkStatus');
            const actionButtons = document.querySelectorAll('[data-bulk-status]');
            const rowCheckboxes = Array.from(document.querySelectorAll('.faq-row-checkbox'));

            if (!bulkForm || rowCheckboxes.length === 0) {
                return;
            }

            function syncDeleteForm() {
                if (!deleteForm) {
                    return;
                }

                deleteForm.querySelectorAll('input[name="ids[]"]').forEach(function (input) {
                    input.remove();
                });

                rowCheckboxes.forEach(function (checkbox) {
                    if (!checkbox.checked) {
                        return;
                    }

                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'ids[]';
                    hidden.value = checkbox.value;
                    deleteForm.appendChild(hidden);
                });
            }

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    rowCheckboxes.forEach(function (checkbox) {
                        checkbox.checked = selectAll.checked;
                    });
                    syncDeleteForm();
                });
            }

            rowCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    if (selectAll) {
                        selectAll.checked = rowCheckboxes.every(function (item) {
                            return item.checked;
                        });
                    }
                    syncDeleteForm();
                });
            });

            actionButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    if (!rowCheckboxes.some(function (checkbox) { return checkbox.checked; })) {
                        window.alert('Please select at least one FAQ.');
                        return;
                    }

                    statusInput.value = button.getAttribute('data-bulk-status');
                    bulkForm.querySelectorAll('input[name="ids[]"]').forEach(function (input) {
                        input.remove();
                    });

                    rowCheckboxes.forEach(function (checkbox) {
                        if (!checkbox.checked) {
                            return;
                        }

                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'ids[]';
                        hidden.value = checkbox.value;
                        bulkForm.appendChild(hidden);
                    });

                    bulkForm.submit();
                });
            });
        })();
    </script>
@endsection
