@extends('layouts.admin')

@section('styles')
    <style>
        .users-filter-form {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(220px, 280px) auto;
            gap: 8px;
            align-items: stretch;
        }

        .users-filter-form .filter-panel {
            min-width: 0;
            flex: initial;
        }

        .users-filter-form .filter-panel__grid {
            min-width: 0;
        }

        .users-filter-form .filter-panel select.form-control,
        .users-filter-form .filter-panel input.form-control {
            width: 100%;
            min-width: 0;
        }

        .users-filter-form .users-filters-panel .form-control {
            min-width: 0;
        }

        .users-filter-form .users-sorting-panel .form-control {
            min-width: 0;
        }

        .users-filter-form .filter-panel .form-row {
            display: grid;
            gap: 8px;
            margin: 0;
        }

        .users-filter-form .filter-panel:first-child .form-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .users-filter-form .filter-panel:nth-child(2) .form-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .users-filter-form .users-sorting-panel .form-row > [class*="col-"]:first-child {
            min-width: 0;
        }

        .users-filter-form .users-sorting-panel .form-row > [class*="col-"]:last-child {
            min-width: 0;
        }

        .users-filter-form .form-row > [class*="col-"] {
            min-width: 0;
            max-width: none;
            width: 100%;
            padding: 0;
            margin: 0;
            flex: initial;
        }

        .users-filter-form .filter-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
            min-width: 0;
            flex-wrap: nowrap;
            white-space: nowrap;
            align-self: center;
        }

        @media (max-width: 1499.98px) {
            .users-filter-form {
                grid-template-columns: minmax(0, 1fr) minmax(200px, 260px) auto;
            }
        }

        @media (max-width: 1199.98px) {
            .users-filter-form {
                grid-template-columns: minmax(0, 1fr);
            }

            .users-filter-form .filter-actions {
                justify-content: center;
            }
        }

        @media (max-width: 767.98px) {
            .users-filter-form .filter-panel:first-child .form-row,
            .users-filter-form .filter-panel:nth-child(2) .form-row {
                grid-template-columns: 1fr;
            }
        }

        .users-page-actions {
            gap: 12px;
        }

        .users-page-actions .action-group {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
        }

        .users-page-actions .btn,
        .users-filter-form .btn {
            border-radius: 9999px;
            padding: .42rem .95rem;
            font-weight: 600;
            box-shadow: none;
        }

        .users-page-actions .btn-primary,
        .users-filter-form .btn-primary {
            background: #2f6df6;
            border-color: #2f6df6;
        }

        .users-page-actions .btn-outline-success {
            color: #3cbf57;
            border-color: #3cbf57;
        }

        .users-page-actions .btn-outline-success:hover {
            background: #3cbf57;
            color: #fff;
        }

        .users-page-actions .btn-danger {
            background: #ef3f3f;
            border-color: #ef3f3f;
        }

        .users-page-actions .btn-success {
            background: #36c16b;
            border-color: #36c16b;
        }

        .users-page-actions .btn-warning {
            background: #f2b324;
            border-color: #f2b324;
            color: #1f1f1f;
        }

        .users-page-actions .btn-warning:hover {
            color: #1f1f1f;
        }

        .users-filter-form .btn-outline-secondary {
            color: #495057;
            border-color: #ced4da;
        }

        .users-filter-form .btn-outline-secondary:hover {
            background: #f1f3f5;
            color: #212529;
        }

        .users-filter-form .filter-panel {
            border-radius: 14px;
            border: 1px solid #e3e6ef;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
            padding: 10px 12px 12px;
        }

        .users-filter-form .filter-panel__title {
            font-size: 12px;
            letter-spacing: .12em;
            font-weight: 700;
            color: #8a8fa3;
            margin-bottom: 10px;
        }

        .users-filter-form .form-control {
            border-radius: 12px;
            border-color: #dee2ee;
            min-height: 48px;
        }

        .users-filter-form .form-control:focus {
            border-color: #2f6df6;
            box-shadow: 0 0 0 .15rem rgba(47, 109, 246, .12);
        }

        .users-table-wrap {
            border-radius: 16px;
            overflow: hidden;
        }

        .users-page-card {
            border: 1px solid #e4e7f0;
            border-radius: 16px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .03);
            overflow: hidden;
        }

        .users-page-card .card-header {
            background: #fff;
            border-bottom: 1px solid #e4e7f0;
            padding: 16px 20px;
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
        }

        .users-page-card .card-body {
            padding: 18px 20px 20px;
        }

        .users-table-wrap .table {
            margin-bottom: 0;
        }

        .users-table-wrap table thead th {
            background: #f6f7fb;
            color: #2d3340;
            font-weight: 700;
            border-bottom-width: 1px;
            vertical-align: middle;
        }

        .users-table-wrap table tbody td {
            vertical-align: middle;
        }
    </style>
@endsection

@section('content')
    <div class="row page-actions users-page-actions">
        <div class="col-lg-12 d-flex flex-wrap align-items-center justify-content-between">
            <div class="mb-2 mb-lg-0 action-group">
                @can('user_create')
                    <a class="btn btn-primary" href="{{ route('admin.users.create') }}">
                        {{ trans('global.add') }} {{ trans('cruds.user.title_singular') }}
                    </a>
                @endcan
            </div>
            <div class="action-group justify-content-end">
                @canany(['user_delete', 'user_edit'])
                    <form id="bulk-user-action-form" method="POST" action="{{ route('admin.users.bulkApproval') }}" onsubmit="return confirm('{{ trans('global.areYouSure') }}');">
                        @csrf
                    </form>
                @endcanany
                <a class="btn btn-outline-success" href="{{ route('admin.users.export', request()->query()) }}">Export Excel</a>
                @can('user_delete')
                    <button class="btn btn-danger" type="submit" name="status" value="delete" form="bulk-user-action-form">{{ trans('global.delete') }}</button>
                @endcan
                @can('user_edit')
                    <button class="btn btn-success" type="submit" name="status" value="approved" form="bulk-user-action-form">Approve</button>
                    <button class="btn btn-warning" type="submit" name="status" value="blocked" form="bulk-user-action-form">Block</button>
                @endcan
            </div>
        </div>
    </div>
    <div class="card users-page-card">
        <div class="card-header">
            {{ trans('cruds.user.title_singular') }} {{ trans('global.list') }}
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}" class="filter-form users-filter-form mb-3">
                <div class="filter-panel users-filters-panel">
                    <div class="filter-panel__title">Filters</div>
                    <div class="form-row filter-panel__grid">
                        <div class="col-md-4 mb-2">
                            <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="{{ trans('global.search') }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <select class="form-control" name="approval_status">
                                <option value="">{{ trans('global.all') }} Approval</option>
                                <option value="approved" @selected(request('approval_status') === 'approved')>Approved</option>
                                <option value="blocked" @selected(request('approval_status') === 'blocked')>Blocked</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="filter-panel users-sorting-panel">
                    <div class="filter-panel__title">Sorting</div>
                    <div class="form-row filter-panel__grid">
                        <div class="col-md-3 mb-2">
                            <select class="form-control users-sort-by" name="sort_by">
                                <option value="id" @selected(request('sort_by', 'id') === 'id')>{{ trans('cruds.user.fields.id') }}</option>
                                <option value="name" @selected(request('sort_by') === 'name')>{{ trans('cruds.user.fields.name') }}</option>
                                <option value="email" @selected(request('sort_by') === 'email')>{{ trans('cruds.user.fields.email') }}</option>
                                <option value="mobile" @selected(request('sort_by') === 'mobile')>{{ trans('cruds.user.fields.mobile') }}</option>
                                <option value="company_name" @selected(request('sort_by') === 'company_name')>Company Name</option>
                                <option value="ip_address" @selected(request('sort_by') === 'ip_address')>IP Address</option>
                                <option value="created_at" @selected(request('sort_by') === 'created_at')>Date & Time</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <select class="form-control users-sort-dir" name="sort_dir">
                                <option value="asc" @selected(request('sort_dir', 'asc') === 'asc')>Ascending</option>
                                <option value="desc" @selected(request('sort_dir') === 'desc')>Descending</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="filter-actions">
                    <button class="btn btn-primary" type="submit">{{ trans('global.search') }}</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">{{ trans('global.clear') }}</a>
                </div>
            </form>

            <div class="d-md-none">
                @forelse($users as $user)
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ trans('cruds.user.fields.name') }}:</strong>
                                    {{ $user->name ?? '' }}
                                </div>
                                @canany(['user_delete', 'user_edit'])
                                    <div>
                                        <input type="checkbox" name="ids[]" value="{{ $user->id }}" form="bulk-user-action-form">
                                    </div>
                                @endcanany
                            </div>
                            <div>
                                <strong>{{ trans('cruds.user.fields.id') }}:</strong>
                                {{ $user->id ?? '' }}
                            </div>
                            <div>
                                <strong>{{ trans('cruds.user.fields.email') }}:</strong>
                                {{ $user->email ?? '' }}
                            </div>
                            <div>
                                <strong>IP Address:</strong>
                                {{ optional($user->latestLoginActivity)->ip_address ?: '-' }}
                            </div>
                            <div>
                                <strong>City:</strong>
                                {{ $user->geo_location['city'] ?? '-' }}
                            </div>
                            <div>
                                <strong>Country:</strong>
                                {{ $user->geo_location['country'] ?? '-' }}
                            </div>
                            <div>
                                <strong>Date & Time:</strong>
                                @php
                                    $dateTime = optional($user->latestLoginActivity)->attempted_at ?? $user->created_at;
                                @endphp
                                {{ $dateTime ? \Carbon\Carbon::parse($dateTime)->format('jS F Y \a\t g:i A') : '-' }}
                            </div>
                            <div>
                                <strong>{{ trans('cruds.user.fields.roles') }}:</strong>
                                @foreach($user->roles as $role)
                                    <span class="badge badge-info">{{ $role->title }}</span>
                                @endforeach
                            </div>
                            <div>
                                <strong>Approval:</strong>
                                {{ $user->participant ? ($user->participant->is_approved ? 'Approved' : 'Blocked') : 'N/A' }}
                            </div>
                            <div class="mt-2">
                                @include('partials.datatablesActions', [
                                    'viewGate' => 'user_show',
                                    'editGate' => 'user_edit',
                                    'deleteGate' => 'user_delete',
                                    'crudRoutePart' => 'users',
                                    'row' => $user,
                                ])
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted">{{ trans('global.no_entries_in_table') }}</div>
                @endforelse
            </div>

            <div class="table-responsive d-none d-md-block users-table-wrap">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>{{ trans('global.actions') }}</th>
                            @canany(['user_delete', 'user_edit'])
                                <th width="10">
                                    <input type="checkbox" id="select-all-users">
                                </th>
                            @endcanany
                            <th>{{ trans('cruds.user.fields.id') }}</th>
                            <th>{{ trans('cruds.user.fields.name') }}</th>
                            <th>{{ trans('cruds.user.fields.email') }}</th>
                            <th>Company Name</th>
                            <th>IP Address</th>
                            <th>City</th>
                            <th>Country</th>
                            <th>Date & Time</th>
                            <th>Approval</th>
                            <th>{{ trans('cruds.user.fields.roles') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    @include('partials.datatablesActions', [
                                        'viewGate' => 'user_show',
                                        'editGate' => 'user_edit',
                                        'deleteGate' => 'user_delete',
                                        'crudRoutePart' => 'users',
                                        'row' => $user,
                                    ])
                                </td>
                                @canany(['user_delete', 'user_edit'])
                                    <td>
                                        <input type="checkbox" class="user-row-checkbox" name="ids[]" value="{{ $user->id }}" form="bulk-user-action-form">
                                    </td>
                                @endcanany
                                <td>{{ $user->id ?? '' }}</td>
                                <td>{{ $user->name ?? '' }}</td>
                                <td>{{ $user->email ?? '' }}</td>
                                <td>{{ $user->company_name ?? '-' }}</td>
                                <td>{{ optional($user->latestLoginActivity)->ip_address ?: '-' }}</td>
                                <td>{{ $user->geo_location['city'] ?? '-' }}</td>
                                <td>{{ $user->geo_location['country'] ?? '-' }}</td>
                                @php
                                    $dateTime = optional($user->latestLoginActivity)->attempted_at ?? $user->created_at;
                                @endphp
                                <td>{{ $dateTime ? \Carbon\Carbon::parse($dateTime)->format('jS F Y \a\t g:i A') : '-' }}</td>
                                <td>{{ $user->participant ? ($user->participant->is_approved ? 'Approved' : 'Blocked') : 'N/A' }}</td>
                                <td>
                                    @foreach($user->roles as $role)
                                        <span class="badge badge-info">{{ $role->title }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        @empty
                        <tr>
                            @canany(['user_delete', 'user_edit'])
                                    <td colspan="12">{{ trans('global.no_entries_in_table') }}</td>
                                @else
                                    <td colspan="11">{{ trans('global.no_entries_in_table') }}</td>
                                @endcanany
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAllCheckbox = document.getElementById('select-all-users');
            const rowCheckboxes = Array.from(document.querySelectorAll('.user-row-checkbox'));

            if (!selectAllCheckbox || rowCheckboxes.length === 0) {
                return;
            }

            selectAllCheckbox.addEventListener('change', function () {
                rowCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });

            rowCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    selectAllCheckbox.checked = rowCheckboxes.every(function (item) {
                        return item.checked;
                    });
                });
            });
        });
    </script>
@endsection
