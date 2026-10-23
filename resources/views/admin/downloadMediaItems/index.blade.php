@extends('layouts.admin')

@section('content')
<div class="row page-actions mb-2">
    <div class="col-lg-12 d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('admin.download-media-items.create') }}" class="btn btn-primary">Add Download Item</a>
        <button type="submit" form="download-items-reorder-form" class="btn btn-success">Save Order</button>
        <button type="button" class="btn btn-success" data-download-item-status="publish">Publish Selected</button>
        <button type="button" class="btn btn-warning" data-download-item-status="unpublish">Unpublish Selected</button>
        <button type="submit" class="btn btn-danger" form="downloadItemDeleteForm" onclick="return confirm('Delete selected records?');">Delete Selected</button>
    </div>
</div>

<div class="card">
    <div class="card-header">Download Items</div>
    <div class="card-body">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="GET" action="{{ route('admin.download-media-items.index') }}" class="mb-3">
            <div class="row align-items-end">
                <div class="col-md-4 form-group">
                    <label for="audience_type">Sorting Group</label>
                    <select class="form-control" name="audience_type" id="audience_type">
                        <option value="">All Sorting Groups</option>
                        <option value="general" @selected(request('audience_type') === 'general')>General</option>
                        <option value="speaker" @selected(request('audience_type') === 'speaker')>Speaker</option>
                        <option value="sponsor" @selected(request('audience_type') === 'sponsor')>Sponsor</option>
                        <option value="delegate" @selected(request('audience_type') === 'delegate')>Delegate</option>
                    </select>
                </div>
                <div class="col-md-4 form-group d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.download-media-items.index') }}" class="btn btn-outline-secondary ml-2">Reset</a>
                </div>
            </div>
        </form>

        <p class="text-muted mb-3">Edit sort numbers directly and save order. Lower numbers show first on the frontend, so item <strong>1</strong> appears before item <strong>2</strong>.</p>

        <form id="download-items-reorder-form" method="POST" action="{{ route('admin.download-media-items.reorder') }}">
            @csrf
        </form>
        <form id="downloadItemBulkForm" method="POST" action="{{ route('admin.download-media-items.bulkStatus') }}">
            @csrf
            <input type="hidden" name="status" id="downloadItemBulkStatusAction" value="">
        </form>
        <form id="downloadItemDeleteForm" method="POST" action="{{ route('admin.download-media-items.massDestroy') }}">
            @csrf
            @method('DELETE')
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Actions</th>
                        <th width="10">
                            <input type="checkbox" id="select-all-download-items" aria-label="Select all download items">
                        </th>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Section</th>
                        <th>Sorting Group</th>
                        <th>Title</th>
                        <th>Sort Order</th>
                        <th>Active</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php
                            $imagePath = trim((string) ($item->image_url ?? ''));
                            $imageSrc = $imagePath !== '' && !str_starts_with($imagePath, 'http://') && !str_starts_with($imagePath, 'https://') && !str_starts_with($imagePath, '//') && !str_starts_with($imagePath, '/')
                                ? asset(ltrim($imagePath, '/'))
                                : $imagePath;
                        @endphp
                        <tr data-id="{{ $item->id }}">
                            <td>
                                <div class="action-buttons">
                                    <a class="btn btn-sm btn-outline-secondary btn-icon action-edit" href="{{ route('admin.download-media-items.edit', $item) }}" title="{{ trans('global.edit') }}" aria-label="{{ trans('global.edit') }}">
                                        <i class="fas fa-pen" aria-hidden="true"></i>
                                    </a>
                                    <form action="{{ route('admin.download-media-items.destroy', $item) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon action-delete" title="{{ trans('global.delete') }}" aria-label="{{ trans('global.delete') }}">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            <td class="text-center align-middle">
                                <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="download-item-row-checkbox" form="downloadItemBulkForm" aria-label="Select download item {{ $item->id }}">
                            </td>
                            <td>{{ $item->id }}</td>
                            <td>
                                @if($imageSrc !== '')
                                    <img src="{{ $imageSrc }}" alt="{{ $item->item_title }}" style="width:84px; height:48px; object-fit:cover; border-radius:6px; border:1px solid #d8dbe4;">
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $item->section_title }}</td>
                            <td>{{ ucfirst((string) $item->audience_type) }}</td>
                            <td>{{ $item->item_title }}</td>
                            <td width="120">
                                <input
                                    type="number"
                                    class="form-control form-control-sm"
                                    min="1"
                                    name="sort_orders[{{ $item->id }}]"
                                    value="{{ old('sort_orders.' . $item->id, max(1, (int) $item->sort_order)) }}"
                                    form="download-items-reorder-form"
                                >
                            </td>
                            <td>{{ $item->is_active ? 'Yes' : 'No' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">No download items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
    (function () {
        var bulkForm = document.getElementById('downloadItemBulkForm');
        var deleteForm = document.getElementById('downloadItemDeleteForm');
        var selectAll = document.getElementById('select-all-download-items');
        var statusInput = document.getElementById('downloadItemBulkStatusAction');
        var statusButtons = Array.prototype.slice.call(document.querySelectorAll('[data-download-item-status]'));
        var rowCheckboxes = Array.prototype.slice.call(document.querySelectorAll('.download-item-row-checkbox'));

        if (!bulkForm || !deleteForm || !rowCheckboxes.length) {
            return;
        }

        function syncDeleteForm() {
            deleteForm.querySelectorAll('input[name="ids[]"]').forEach(function (input) {
                input.remove();
            });

            rowCheckboxes.forEach(function (checkbox) {
                if (!checkbox.checked) {
                    return;
                }

                var hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'ids[]';
                hidden.value = checkbox.value;
                deleteForm.appendChild(hidden);
            });
        }

        function syncSelectAll() {
            if (!selectAll) {
                return;
            }

            selectAll.checked = rowCheckboxes.length > 0 && rowCheckboxes.every(function (checkbox) {
                return checkbox.checked;
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
                syncSelectAll();
                syncDeleteForm();
            });
        });

        statusButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                if (!rowCheckboxes.some(function (checkbox) { return checkbox.checked; })) {
                    window.alert('Please select at least one download item.');
                    return;
                }

                statusInput.value = button.getAttribute('data-download-item-status');
                bulkForm.querySelectorAll('input[name="ids[]"]').forEach(function (input) {
                    input.remove();
                });

                rowCheckboxes.forEach(function (checkbox) {
                    if (!checkbox.checked) {
                        return;
                    }

                    var hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'ids[]';
                    hidden.value = checkbox.value;
                    bulkForm.appendChild(hidden);
                });

                bulkForm.submit();
            });
        });

        syncSelectAll();
        syncDeleteForm();
    })();
</script>
@endsection
