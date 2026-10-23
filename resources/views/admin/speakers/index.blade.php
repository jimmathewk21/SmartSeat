@extends('layouts.admin')

@section('styles')
    @parent
    <style>
        .speakers-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .speakers-toolbar__actions,
        .speakers-bulk-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .speakers-filters-layout {
            width: 100%;
            margin-bottom: 1rem;
        }

        .speakers-filters-layout > .filter-panel {
            width: 100%;
            min-width: 0;
            padding: 1rem;
            border: 1px solid #dce2ed;
            border-radius: 14px;
            background: #f8fafc;
        }

        .speakers-filters-layout > .filter-panel .form-row {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 12px;
        }

        .speakers-sort-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .speaker-sort-option {
            position: relative;
            display: block;
            margin: 0;
        }

        .speaker-sort-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .speaker-sort-option span {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 10px 12px;
            border: 1px solid #d8e0eb;
            border-radius: 10px;
            background: #fff;
            color: #334155;
            font-weight: 600;
            text-align: center;
            transition: border-color 0.15s ease, background-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
            cursor: pointer;
            user-select: none;
        }

        .speaker-sort-option input:checked + span {
            border-color: #2563eb;
            background: rgba(37, 99, 235, 0.08);
            color: #1d4ed8;
            box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.12) inset;
        }

        .speaker-sort-option input:focus-visible + span {
            outline: 2px solid rgba(37, 99, 235, 0.45);
            outline-offset: 2px;
        }

        .speaker-sort-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: inherit;
            text-decoration: none;
            white-space: nowrap;
        }

        .speaker-sort-link:hover,
        .speaker-sort-link:focus {
            color: #1d4ed8;
            text-decoration: none;
        }

        .speaker-sort-icons {
            display: inline-flex;
            flex-direction: column;
            gap: 2px;
            line-height: 1;
            font-size: 10px;
            color: #94a3b8;
        }

        .speaker-sort-icons .is-active {
            color: #2563eb;
        }

        .speakers-sort-panel .filter-panel__title {
            margin-bottom: 10px;
        }

        .speakers-search-row {
            display: grid;
            gap: 12px;
        }

        .speakers-search-row {
            grid-template-columns: minmax(240px, 2fr) repeat(7, minmax(0, 1fr)) auto auto;
            margin-bottom: 12px;
        }

        .speakers-search-row .form-group {
            margin-bottom: 0;
            min-width: 0;
        }

        .speakers-search-row label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .speakers-action-group {
            display: flex;
            align-items: center;
            gap: 10px;
            height: 100%;
            align-self: end;
        }

        .speakers-action-group .btn {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .speakers-panel-actions {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 10px;
            margin-top: 18px;
            flex-wrap: wrap;
        }

        .speakers-sort-panel .speakers-panel-actions {
            margin-top: 18px;
        }

        .speakers-table-scroll {
            max-height: 68vh;
            overflow: auto;
            border: 1px solid #dce2ed;
            border-radius: 12px;
            background: #fff;
        }

        .speakers-table-scroll .table {
            margin-bottom: 0;
        }

        .speakers-table-scroll thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f8fafc;
            box-shadow: inset 0 -1px 0 #dce2ed;
        }

        .speaker-key-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
        }

        .speaker-key-badge--yes {
            background: rgba(22, 163, 74, 0.12);
            color: #166534;
        }

        .speaker-key-badge--no {
            background: rgba(71, 85, 105, 0.12);
            color: #334155;
        }

        .speaker-icon-badge {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid #d0d7e2;
            font-size: 14px;
        }

        .speaker-icon-badge--on {
            color: #2563eb;
            background: rgba(37, 99, 235, 0.08);
            border-color: rgba(37, 99, 235, 0.28);
        }

        .speaker-icon-badge--off {
            color: #64748b;
            background: rgba(100, 116, 139, 0.1);
            border-color: rgba(100, 116, 139, 0.24);
        }

        .speaker-home-cell {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .speaker-status-btn {
            min-width: 118px;
        }

        .speaker-home-toggle {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .speaker-topic-warning {
            font-weight: 700;
            color: #b45309;
        }

        .speaker-photo-thumb {
            width: 56px;
            height: 56px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid #dce2ed;
            background: #f8fafc;
        }

        .speaker-text-clamp {
            max-width: 240px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .speaker-link-cell {
            text-align: center;
        }

        .speaker-yesno-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            padding: 4px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
        }

        .speaker-yesno-badge--yes {
            background: rgba(22, 163, 74, 0.12);
            color: #166534;
        }

        .speaker-yesno-badge--no {
            background: rgba(100, 116, 139, 0.12);
            color: #475569;
        }

        .speaker-sort-cell {
            min-width: 80px;
            text-align: center;
            font-weight: 800;
        }

        @media (max-width: 1199.98px) {
            .speakers-search-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .speakers-sort-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 991.98px) {
            .speakers-search-row {
                grid-template-columns: 1fr;
            }

            .speakers-action-group {
                align-self: stretch;
            }

            .speakers-sort-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .speakers-table-scroll {
                max-height: none;
                overflow: visible;
                border: 0;
                border-radius: 0;
            }

            .speakers-table-scroll thead th {
                position: static;
                box-shadow: none;
            }
        }
    </style>
@endsection

@section('content')
    @can('speaker_create')
        <div class="speakers-toolbar">
            <a class="btn btn-primary" href="{{ route('admin.speakers.create') }}">{{ trans('global.add') }} {{ trans('cruds.speaker.title_singular') }}</a>
            <div class="speakers-toolbar__actions">
                <a class="btn btn-outline-success" href="{{ route('admin.speakers.export', request()->query()) }}">Export CSV</a>
                @can('speaker_delete')
                    <button class="btn btn-danger" type="submit" form="speaker-bulk-form" onclick="return confirm('{{ trans('global.areYouSure') }}');">{{ trans('global.delete') }}</button>
                @endcan
                @can('speaker_edit')
                    <button class="btn btn-primary" type="submit" form="speaker-bulk-activate-form">Publish</button>
                    <button class="btn btn-warning" type="submit" form="speaker-bulk-deactivate-form">Unpublish</button>
                @endcan
            </div>
        </div>
    @else
        <div class="speakers-toolbar">
            <div></div>
            <div class="speakers-toolbar__actions">
                <a class="btn btn-outline-success" href="{{ route('admin.speakers.export', request()->query()) }}">Export CSV</a>
                @can('speaker_delete')
                    <button class="btn btn-danger" type="submit" form="speaker-bulk-form" onclick="return confirm('{{ trans('global.areYouSure') }}');">{{ trans('global.delete') }}</button>
                @endcan
                @can('speaker_edit')
                    <button class="btn btn-primary" type="submit" form="speaker-bulk-activate-form">Publish</button>
                    <button class="btn btn-warning" type="submit" form="speaker-bulk-deactivate-form">Unpublish</button>
                @endcan
            </div>
        </div>
    @endcan

    @php
        $speakerSortBy = (string) request('sort_by', '');
        $speakerSortDir = (string) request('sort_dir', 'asc');
        $speakerSortBaseQuery = request()->except(['page', 'sort_by', 'sort_dir']);
        $speakerSortLink = function (string $field) use ($speakerSortBaseQuery, $speakerSortBy, $speakerSortDir) {
            $nextDir = $speakerSortBy === $field && $speakerSortDir === 'asc' ? 'desc' : 'asc';

            return route('admin.speakers.index', array_merge($speakerSortBaseQuery, [
                'sort_by' => $field,
                'sort_dir' => $nextDir,
            ]));
        };
    @endphp

    <div class="card">
        <div class="card-header">{{ trans('cruds.speaker.title') }} {{ trans('global.list') }}</div>
        <div class="card-body">
            <div class="speakers-filters-layout mb-3">
                <form method="GET" action="{{ route('admin.speakers.index') }}" class="filter-form">
                    @if(request()->filled('sort_by'))
                        <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                    @endif
                    @if(request()->filled('sort_dir'))
                        <input type="hidden" name="sort_dir" value="{{ request('sort_dir') }}">
                    @endif
                    <div class="filter-panel">
                        <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                            <div class="filter-panel__title mb-0">Filters</div>
                        </div>
                        <div class="speakers-search-row">
                            <div class="form-group">
                                <label for="speaker-search">Search</label>
                                <input
                                    class="form-control"
                                    type="text"
                                    name="search"
                                    id="speaker-search"
                                    value="{{ request('search') }}"
                                    placeholder="Search by name, company, designation, email, or topic"
                                >
                            </div>
                            <div class="form-group">
                                <select class="form-control" name="is_key_figure">
                                    <option value="">Key Speaker</option>
                                    <option value="1" @selected(request('is_key_figure') === '1')>Yes</option>
                                    <option value="0" @selected(request('is_key_figure') === '0')>No</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <select class="form-control" name="is_active">
                                    <option value="">Status</option>
                                    <option value="1" @selected(request('is_active') === '1')>Published</option>
                                    <option value="0" @selected(request('is_active') === '0')>Unpublished</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <select class="form-control" name="country">
                                    <option value="">Country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country }}" @selected(request('country') === $country)>{{ $country }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <select class="form-control" name="category">
                                    <option value="">All Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <select class="form-control" name="speaker_type">
                                    <option value="">Gender</option>
                                    <option value="male" @selected(request('speaker_type') === 'male')>Male</option>
                                    <option value="female" @selected(request('speaker_type') === 'female')>Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <select class="form-control" name="speaker_role">
                                    <option value="">Speaker / Moderator</option>
                                    <option value="speaker" @selected(request('speaker_role') === 'speaker')>Speaker</option>
                                    <option value="moderator" @selected(request('speaker_role') === 'moderator')>Moderator</option>
                                    <option value="both" @selected(request('speaker_role') === 'both')>Speaker & Moderator</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <select class="form-control" name="topic_allotted">
                                    <option value="">Topic Status</option>
                                    <option value="1" @selected(request('topic_allotted') === '1')>Yes</option>
                                    <option value="0" @selected(request('topic_allotted') === '0')>No</option>
                                </select>
                            </div>
                            <div class="form-group speakers-action-group">
                                <button class="btn btn-primary" type="submit">{{ trans('global.search') }}</button>
                                <a class="btn btn-outline-secondary" href="{{ route('admin.speakers.index') }}">{{ trans('global.clear') }}</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @canany(['speaker_delete', 'speaker_edit'])
                <form id="speaker-bulk-form" method="POST" action="{{ route('admin.speakers.massDestroy') }}">
                    @csrf
                    @method('DELETE')
                </form>
                <form id="speaker-bulk-activate-form" method="POST" action="{{ route('admin.speakers.bulkActivate') }}">
                    @csrf
                </form>
                <form id="speaker-bulk-deactivate-form" method="POST" action="{{ route('admin.speakers.bulkDeactivate') }}">
                    @csrf
                </form>
            @endcanany

            <div class="table-responsive speakers-table-scroll">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>{{ trans('global.actions') }}</th>
                            @canany(['speaker_delete', 'speaker_edit'])
                                <th width="10">
                                    <input type="checkbox" id="select-all-speakers" aria-label="Select all speakers">
                                </th>
                            @endcanany
                            <th>Picture</th>
                            <th>
                                <a class="speaker-sort-link" href="{{ $speakerSortLink('name') }}" aria-label="Sort by name">
                                    <span>{{ trans('cruds.speaker.fields.name') }}</span>
                                    <span class="speaker-sort-icons" aria-hidden="true">
                                        <i class="fas fa-arrow-up {{ $speakerSortBy === 'name' && $speakerSortDir === 'asc' ? 'is-active' : '' }}"></i>
                                        <i class="fas fa-arrow-down {{ $speakerSortBy === 'name' && $speakerSortDir === 'desc' ? 'is-active' : '' }}"></i>
                                    </span>
                                </a>
                            </th>
                            <th>
                                <a class="speaker-sort-link" href="{{ $speakerSortLink('company_name') }}" aria-label="Sort by company name">
                                    <span>{{ trans('cruds.speaker.fields.company_name') }}</span>
                                    <span class="speaker-sort-icons" aria-hidden="true">
                                        <i class="fas fa-arrow-up {{ $speakerSortBy === 'company_name' && $speakerSortDir === 'asc' ? 'is-active' : '' }}"></i>
                                        <i class="fas fa-arrow-down {{ $speakerSortBy === 'company_name' && $speakerSortDir === 'desc' ? 'is-active' : '' }}"></i>
                                    </span>
                                </a>
                            </th>
                            <th>
                                <a class="speaker-sort-link" href="{{ $speakerSortLink('designation') }}" aria-label="Sort by designation">
                                    <span>{{ trans('cruds.speaker.fields.designation') }}</span>
                                    <span class="speaker-sort-icons" aria-hidden="true">
                                        <i class="fas fa-arrow-up {{ $speakerSortBy === 'designation' && $speakerSortDir === 'asc' ? 'is-active' : '' }}"></i>
                                        <i class="fas fa-arrow-down {{ $speakerSortBy === 'designation' && $speakerSortDir === 'desc' ? 'is-active' : '' }}"></i>
                                    </span>
                                </a>
                            </th>
                            <th>{{ trans('cruds.speaker.fields.country') }}</th>
                            <th>
                                <a class="speaker-sort-link" href="{{ $speakerSortLink('email') }}" aria-label="Sort by email">
                                    <span>{{ trans('cruds.speaker.fields.email') }}</span>
                                    <span class="speaker-sort-icons" aria-hidden="true">
                                        <i class="fas fa-arrow-up {{ $speakerSortBy === 'email' && $speakerSortDir === 'asc' ? 'is-active' : '' }}"></i>
                                        <i class="fas fa-arrow-down {{ $speakerSortBy === 'email' && $speakerSortDir === 'desc' ? 'is-active' : '' }}"></i>
                                    </span>
                                </a>
                            </th>
                            <th>{{ trans('cruds.speaker.fields.phone') }}</th>
                            <th>LinkedIn</th>
                            <th>
                                <a class="speaker-sort-link" href="{{ $speakerSortLink('topic') }}" aria-label="Sort by topic">
                                    <span>{{ trans('cruds.speaker.fields.topic') }}</span>
                                    <span class="speaker-sort-icons" aria-hidden="true">
                                        <i class="fas fa-arrow-up {{ $speakerSortBy === 'topic' && $speakerSortDir === 'asc' ? 'is-active' : '' }}"></i>
                                        <i class="fas fa-arrow-down {{ $speakerSortBy === 'topic' && $speakerSortDir === 'desc' ? 'is-active' : '' }}"></i>
                                    </span>
                                </a>
                            </th>
                            <th>{{ trans('cruds.speaker.fields.topic_category') }}</th>
                            <th>{{ trans('cruds.speaker.fields.bio') }}</th>
                            <th>Advert</th>
                            <th>Key Figure</th>
                            <th>Home Page – Yes No</th>
                            <th>Scroll Bar – Yes No</th>
                            <th>Publish</th>
                            <th>{{ trans('cruds.speaker.fields.sort_order') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($speakers as $speaker)
                            @php
                                $hasBio = trim((string) ($speaker->resource_url ?? '')) !== '' || trim((string) ($speaker->bio ?? '')) !== '';
                                $hasAdvert = trim((string) ($speaker->advert_url ?? '')) !== '';
                            @endphp
                            <tr>
                                <td>
                                    <div class="action-buttons">
                                        @can('speaker_show')
                                            <a class="btn btn-sm btn-outline-primary btn-icon action-view" href="{{ route('admin.speakers.show', $speaker->id) }}" title="{{ trans('global.view') }}" aria-label="{{ trans('global.view') }}">
                                                <i class="fas fa-eye" aria-hidden="true"></i>
                                            </a>
                                        @endcan
                                        @can('speaker_edit')
                                            <a class="btn btn-sm btn-outline-secondary btn-icon action-edit" href="{{ route('admin.speakers.edit', $speaker->id) }}" title="{{ trans('global.edit') }}" aria-label="{{ trans('global.edit') }}">
                                                <i class="fas fa-pen" aria-hidden="true"></i>
                                            </a>
                                        @endcan
                                        @can('speaker_delete')
                                            <form action="{{ route('admin.speakers.destroy', $speaker->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ trans('global.areYouSure') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger btn-icon" type="submit" title="{{ trans('global.delete') }}" aria-label="{{ trans('global.delete') }}">
                                                    <i class="fas fa-trash" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                                @canany(['speaker_delete', 'speaker_edit'])
                                    <td><input type="checkbox" name="ids[]" value="{{ $speaker->id }}" class="speaker-row-checkbox" form="speaker-bulk-form"></td>
                                @endcanany
                                <td>
                                    <img src="{{ $speaker->display_photo_url }}" alt="{{ $speaker->name }}" class="speaker-photo-thumb">
                                </td>
                                <td>{{ $speaker->name }}</td>
                                <td>{{ $speaker->company_name ?: '-' }}</td>
                                <td>{{ $speaker->designation ?: '-' }}</td>
                                <td>{{ $speaker->country ?: '-' }}</td>
                                <td>{{ $speaker->email ?: '-' }}</td>
                                <td>{{ $speaker->phone ?: '-' }}</td>
                                <td>
                                    @if($speaker->linkedin_url)
                                        <a
                                            href="{{ $speaker->linkedin_url }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="speaker-icon-badge speaker-icon-badge--on"
                                            title="LinkedIn"
                                            aria-label="LinkedIn"
                                        >
                                            <i class="fab fa-linkedin-in" aria-hidden="true"></i>
                                        </a>
                                    @else
                                        <span class="speaker-icon-badge speaker-icon-badge--off" title="No LinkedIn">
                                            <i class="fab fa-linkedin-in" aria-hidden="true"></i>
                                        </span>
                                    @endif
                                </td>
                                <td class="speaker-text-clamp" title="{{ $speaker->topic ?: '' }}">{{ $speaker->topic ?: '-' }}</td>
                                <td class="speaker-text-clamp" title="{{ $speaker->topic_category ?: '' }}">{{ $speaker->topic_category ?: '-' }}</td>
                                <td class="speaker-link-cell">
                                    @if($speaker->resource_url)
                                        @php
                                            $bioResourceUrl = trim((string) $speaker->resource_url);
                                            $bioResourceHref = str_starts_with($bioResourceUrl, 'http://')
                                                || str_starts_with($bioResourceUrl, 'https://')
                                                || str_starts_with($bioResourceUrl, '//')
                                                || str_starts_with($bioResourceUrl, '/')
                                                ? $bioResourceUrl
                                                : asset(ltrim($bioResourceUrl, '/'));
                                        @endphp
                                        <a
                                            href="{{ $bioResourceHref }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="speaker-icon-badge speaker-icon-badge--on"
                                            title="Bio"
                                            aria-label="Bio"
                                        >
                                            <i class="fas fa-link" aria-hidden="true"></i>
                                        </a>
                                    @endif
                                </td>
                                <td class="speaker-link-cell">
                                    @if($speaker->advert_url)
                                        <a
                                            href="{{ str_starts_with($speaker->advert_url, 'http://') || str_starts_with($speaker->advert_url, 'https://') || str_starts_with($speaker->advert_url, '//') || str_starts_with($speaker->advert_url, '/') ? $speaker->advert_url : asset(ltrim($speaker->advert_url, '/')) }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="speaker-icon-badge speaker-icon-badge--on"
                                            title="Advert"
                                            aria-label="Advert"
                                        >
                                            <i class="fas fa-link" aria-hidden="true"></i>
                                        </a>
                                    @endif
                                </td>
                                <td class="speaker-link-cell">
                                    <span class="speaker-icon-badge {{ $speaker->is_key_figure ? 'speaker-icon-badge--on' : 'speaker-icon-badge--off' }}" title="{{ $speaker->is_key_figure ? 'Key Figure' : 'Not Key Figure' }}">
                                        <i class="fas fa-crown" aria-hidden="true"></i>
                                    </span>
                                </td>
                                <td class="speaker-link-cell">
                                    <span class="speaker-yesno-badge {{ $speaker->show_on_homepage ? 'speaker-yesno-badge--yes' : 'speaker-yesno-badge--no' }}">
                                        {{ $speaker->show_on_homepage ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td class="speaker-link-cell">
                                    <span class="speaker-yesno-badge {{ $speaker->show_on_scrollbar ? 'speaker-yesno-badge--yes' : 'speaker-yesno-badge--no' }}">
                                        {{ $speaker->show_on_scrollbar ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td class="speaker-link-cell">
                                    <span class="speaker-yesno-badge {{ $speaker->is_active ? 'speaker-yesno-badge--yes' : 'speaker-yesno-badge--no' }}">
                                        {{ $speaker->is_active ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td class="speaker-sort-cell">{{ $speaker->sort_order }}</td>
                            </tr>
                        @empty
                            <tr>
                                @canany(['speaker_delete', 'speaker_edit'])
                                    <td colspan="19">{{ trans('global.no_entries_in_table') }}</td>
                                @else
                                    <td colspan="18">{{ trans('global.no_entries_in_table') }}</td>
                                @endcanany
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $speakers->links() }}
        </div>
    </div>

    @canany(['speaker_delete', 'speaker_edit'])
        <script>
            (function () {
                const selectAll = document.getElementById('select-all-speakers');
                if (!selectAll) {
                    return;
                }

                const rowCheckboxes = Array.from(document.querySelectorAll('.speaker-row-checkbox'));
                const bulkForms = ['speaker-bulk-form', 'speaker-bulk-activate-form', 'speaker-bulk-deactivate-form']
                    .map(function (id) {
                        return document.getElementById(id);
                    })
                    .filter(Boolean);

                function syncBulkForms() {
                    bulkForms.forEach(function (form) {
                        form.querySelectorAll('input[name="ids[]"]').forEach(function (input) {
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
                            form.appendChild(hidden);
                        });
                    });
                }

                selectAll.addEventListener('change', function () {
                    rowCheckboxes.forEach(function (checkbox) {
                        checkbox.checked = selectAll.checked;
                    });
                    syncBulkForms();
                });

                rowCheckboxes.forEach(function (checkbox) {
                    checkbox.addEventListener('change', syncBulkForms);
                });
            })();
        </script>
    @endcanany
@endsection
