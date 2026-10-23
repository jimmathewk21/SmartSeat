@extends('layouts.admin')

@section('styles')
<style>
    .contact-toolbar,
    .contact-card {
        border-radius: 22px;
        overflow: hidden;
    }

    .contact-toolbar {
        border: 1px solid #d8dde9;
        background: linear-gradient(135deg, #f5f7fd 0%, #eef2fb 100%);
    }

    .contact-card {
        border: 1px solid #d4d8e3;
        box-shadow: 0 20px 40px rgba(16, 24, 40, 0.08);
    }

    .contact-card--default .contact-card__header,
    .contact-card--default .contact-card__footer {
        background: linear-gradient(90deg, #5e2bbb 0%, #4a2199 100%);
        color: #fff;
    }

    .contact-card--unsubscribed .contact-card__header,
    .contact-card--unsubscribed .contact-card__footer {
        background: linear-gradient(90deg, #e30b05 0%, #c90404 100%);
        color: #fff;
    }

    .contact-card--known .contact-card__header,
    .contact-card--known .contact-card__footer {
        background: linear-gradient(90deg, #128d12 0%, #0d7610 100%);
        color: #fff;
    }

    .contact-card--blacklisted .contact-card__header,
    .contact-card--blacklisted .contact-card__footer {
        background: linear-gradient(90deg, #111827 0%, #000000 100%);
        color: #fff;
    }

    .contact-card__header,
    .contact-card__footer {
        padding: 0.78rem 1.1rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    .contact-card__body {
        background: #fff;
        padding: 1.15rem 1.2rem 1rem;
    }

    .contact-card__header-line,
    .contact-card__footer-line {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .contact-card__header-left,
    .contact-card__header-right {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.95rem;
    }

    .contact-card__header-meta {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.92rem;
        line-height: 1;
        font-weight: 700;
    }

    .contact-card__header-box {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 5px;
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(17, 24, 39, 0.16);
        box-shadow: inset 0 1px 2px rgba(17, 24, 39, 0.08);
    }

    .contact-card__header-box--checked::after {
        content: "\2713";
        color: #128d12;
        font-size: 13px;
        font-weight: 900;
        line-height: 1;
    }

    .contact-check {
        transform: scale(1.2);
    }

    .contact-body-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(0, 1.6fr) minmax(0, 0.85fr) minmax(0, 0.85fr);
        gap: 0;
    }

    .contact-body-col {
        min-width: 0;
        overflow: hidden;
        padding: 0 0.85rem;
    }

    .contact-body-col + .contact-body-col {
        border-left: 1px solid #cfd5e3;
    }

    .contact-body-col--profile {
        padding-left: 0;
    }

    .contact-profile-wrap {
        display: grid;
        grid-template-columns: 162px minmax(0, 1fr);
        gap: 0.85rem;
        align-items: start;
    }

    .contact-avatar {
        width: 128px;
        height: 128px;
        border-radius: 8px;
        object-fit: cover;
        background: #f2f4f7;
        border: 1px solid #d7dbe6;
    }

    .contact-profile-name {
        margin: 0 0 0.3rem;
        font-size: 1.95rem;
        line-height: 1.08;
        font-weight: 500;
        color: #1a1a1a;
    }

    .contact-profile-crown {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.1rem;
        height: 1.1rem;
        margin-left: 0.35rem;
        vertical-align: middle;
        color: #c08a00;
    }

    .contact-profile-crown svg {
        display: block;
        width: 100%;
        height: 100%;
        fill: currentColor;
    }

    .contact-profile-sub,
    .contact-profile-company,
    .contact-profile-country {
        margin: 0 0 0.22rem;
        font-size: 0.95rem;
        line-height: 1.3;
        color: #1f2937;
    }

    .contact-profile-company {
        font-weight: 700;
    }

    .contact-links {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        margin-top: 0.65rem;
    }

    .contact-link-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        color: #111827;
        font-size: 1.2rem;
        text-decoration: none;
    }

    .contact-link-icon:hover,
    .contact-link-icon:focus {
        color: #4a2199;
        text-decoration: none;
    }

    .contact-info-list,
    .contact-side-list {
        display: grid;
        gap: 0.45rem;
        font-size: 0.94rem;
        line-height: 1.35;
        color: #1f2937;
        overflow: hidden;
    }

    .contact-info-row,
    .contact-side-row {
        overflow-wrap: break-word;
        word-break: break-word;
        min-width: 0;
    }

    .contact-info-row strong,
    .contact-side-row strong {
        display: block;
        margin-bottom: 0.08rem;
        font-size: 0.9rem;
        color: #111827;
    }

    .contact-side-group {
        display: grid;
        gap: 0.3rem;
    }

    .contact-side-group + .contact-side-group {
        margin-top: 0.45rem;
    }

    .contact-inline-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1rem;
        margin-right: 0.2rem;
        color: #4a2199;
        font-size: 0.95rem;
        vertical-align: text-bottom;
    }

    .contact-link-value {
        word-break: break-word;
    }

    .contact-detail-divider {
        margin-top: 0.8rem;
        padding-top: 0.8rem;
        border-top: 1px solid #d7dbe6;
    }

    .contact-pill-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .contact-pill-stack {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
    }

    .contact-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 24px;
        padding: 0.25rem 0.8rem;
        border-radius: 999px;
        background: #eef2ff;
        color: #2b2f38;
        font-size: 0.78rem;
        font-weight: 700;
        text-align: center;
        border: 1px solid rgba(17, 24, 39, 0.08);
    }

    .contact-pill--status {
        background: #f04438;
        color: #fff;
        border-color: #f04438;
    }

    .contact-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
        padding: 0.25rem 0.8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.28);
        color: #fff;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.04em;
    }

    .contact-badge-grid {
        display: grid;
        grid-template-columns: repeat(14, minmax(0, 1fr));
        gap: 0.45rem;
        width: 100%;
        margin-top: 0.9rem;
    }

    .contact-tag {
        min-height: 20px;
        padding: 0.25rem 0.4rem;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 700;
        text-align: center;
        color: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .contact-tag:nth-child(6n+1) { background: #d92d20; }
    .contact-tag:nth-child(6n+2) { background: #5f3dc4; }
    .contact-tag:nth-child(6n+3) { background: #1971c2; }
    .contact-tag:nth-child(6n+4) { background: #2b8a3e; }
    .contact-tag:nth-child(6n+5) { background: #c0a500; color: #111827; }
    .contact-tag:nth-child(6n+6) { background: #0c8599; }

    .contact-tag--empty {
        background: rgba(255, 255, 255, 0.92) !important;
        color: transparent !important;
        border: 1px solid rgba(17, 24, 39, 0.08);
    }

    .contact-footer-actions {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        flex-wrap: wrap;
    }

    .contact-action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 0.75rem;
    }

    .contact-bulk-inline {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .contact-bulk-inline__select {
        flex: 1 1 320px;
        max-width: 430px;
    }

    .contact-bulk-inline .form-check {
        margin-left: auto;
        white-space: nowrap;
    }

    @media (max-width: 767.98px) {
        .contact-bulk-inline .form-check {
            margin-left: 0;
            width: 100%;
        }
    }

    .contact-summary-legend {
        display: flex;
        flex-wrap: nowrap;
        gap: 1rem;
        align-items: center;
    }

    .legend-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
    }

    .legend-chip__swatch {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        border: 1px solid rgba(16, 24, 40, 0.12);
    }

    .contact-btn {
        min-width: 90px;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .contact-btn.btn-light {
        background: rgba(255, 255, 255, 0.92);
        border-color: rgba(255, 255, 255, 0.92);
        color: #475467;
    }

    .contact-btn.btn-dark {
        background: rgba(67, 78, 102, 0.95);
        border-color: rgba(67, 78, 102, 0.95);
    }

    .contact-btn.btn-outline-light {
        background: rgba(255, 255, 255, 0.14);
    }

    @media (max-width: 1199.98px) {
        .contact-body-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem 0;
        }

        .contact-body-col:nth-child(3) {
            border-left: 0;
        }
    }

    @media (max-width: 767.98px) {
        .contact-body-grid {
            grid-template-columns: 1fr;
        }

        .contact-body-col {
            padding: 0;
        }

        .contact-body-col + .contact-body-col {
            border-left: 0;
            border-top: 1px solid #cfd5e3;
            padding-top: 0.85rem;
            margin-top: 0.85rem;
        }

        .contact-profile-wrap {
            grid-template-columns: 1fr;
        }

        .contact-profile-name {
            font-size: 1.55rem;
        }

        .contact-badge-grid {
            grid-template-columns: repeat(6, minmax(0, 1fr));
        }
    }
</style>
@endsection

@section('content')
<div class="row page-actions">
    <div class="col-lg-12 d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <a class="btn btn-primary" href="{{ route('admin.my-contacts.create') }}">Add My Contact</a>
            <a class="btn btn-outline-secondary" href="{{ route('admin.my-contacts.page-settings.edit') }}">Page Settings</a>
            <a class="btn btn-outline-primary" href="{{ route('admin.my-contacts.export', request()->query()) }}">Export CSV</a>
        </div>
        <form method="POST" action="{{ route('admin.my-contacts.import') }}" enctype="multipart/form-data" class="form-inline mt-2 mt-lg-0">
            @csrf
            <input type="file" name="import_file" class="form-control mr-2" accept=".csv,.txt" required>
            <button class="btn btn-outline-secondary" type="submit">Import CSV</button>
        </form>
    </div>
</div>

<div class="card contact-toolbar mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.my-contacts.index') }}">
            <div class="form-row">
                <div class="col-md-3 mb-3">
                    <label>Search By</label>
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Name, company, email, mobile, event">
                </div>
                <div class="col-md-2 mb-3">
                    <label>Category</label>
                    <select class="form-control" name="category">
                        <option value="">All</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label>Country</label>
                    <select class="form-control" name="country">
                        <option value="">All</option>
                        @foreach($countries as $country)
                            <option value="{{ $country }}" @selected(request('country') === $country)>{{ $country }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label>Area of Expertise</label>
                    <select class="form-control" name="specialization">
                        <option value="">All</option>
                        @foreach($specializations as $specialization)
                            <option value="{{ $specialization }}" @selected(request('specialization') === $specialization)>{{ $specialization }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Speaker Tag / Event</label>
                    <div class="form-row">
                        <div class="col-6">
                            <select class="form-control" name="speaker_tag">
                                <option value="">Any Tag</option>
                                @foreach($speakerTags as $speakerTag)
                                    <option value="{{ $speakerTag }}" @selected(request('speaker_tag') === $speakerTag)>{{ $speakerTag }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <select class="form-control" name="event_name">
                                <option value="">Any Event</option>
                                @foreach($events as $event)
                                    <option value="{{ $event }}" @selected(request('event_name') === $event)>{{ $event }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-2 mb-3">
                    <label>Status</label>
                    <select class="form-control" name="contact_status">
                        <option value="">All</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('contact_status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 mb-3">
                    <label>Stars</label>
                    <input class="form-control" type="number" min="0" max="5" name="stars" value="{{ request('stars') }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label>Experience</label>
                    <select class="form-control" name="experience">
                        <option value="">All</option>
                        @foreach($experiences as $experience)
                            <option value="{{ $experience }}" @selected(request('experience') === $experience)>{{ $experience }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label>Frontend</label>
                    <select class="form-control" name="is_published">
                        <option value="">All</option>
                        <option value="1" @selected(request('is_published') === '1')>Published</option>
                        <option value="0" @selected(request('is_published') === '0')>Unpublished</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label>Sort</label>
                    <select class="form-control" name="sort_by">
                        <option value="created_at" @selected(request('sort_by', 'created_at') === 'created_at')>Latest</option>
                        <option value="name" @selected(request('sort_by') === 'name')>Name A-Z</option>
                        <option value="company_name" @selected(request('sort_by') === 'company_name')>Company A-Z</option>
                        <option value="country" @selected(request('sort_by') === 'country')>Country</option>
                        <option value="speaker_tag" @selected(request('sort_by') === 'speaker_tag')>Speaker Tag</option>
                        <option value="experience" @selected(request('sort_by') === 'experience')>Experience</option>
                        <option value="event_name" @selected(request('sort_by') === 'event_name')>Event Name</option>
                        <option value="stars" @selected(request('sort_by') === 'stars')>Stars</option>
                        <option value="is_published" @selected(request('sort_by') === 'is_published')>Frontend</option>
                    </select>
                </div>
                <div class="col-md-1 mb-3">
                    <label>Dir</label>
                    <select class="form-control" name="sort_dir">
                        <option value="desc" @selected(request('sort_dir', 'desc') === 'desc')>Desc</option>
                        <option value="asc" @selected(request('sort_dir') === 'asc')>Asc</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button class="btn btn-primary mr-2" type="submit">Apply</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.my-contacts.index') }}">Reset</a>
                </div>
                <div class="col-md-12 mb-3 d-flex align-items-end">
                    <div class="contact-summary-legend">
                        <span class="legend-chip"><span class="legend-chip__swatch" style="background:#edf1fb;border:1px solid #c5cde8;"></span>Default</span>
                        <span class="legend-chip"><span class="legend-chip__swatch" style="background:#ff2c1f;"></span>Unsubscribed</span>
                        <span class="legend-chip"><span class="legend-chip__swatch" style="background:#1f8a4d;"></span>Known Contact</span>
                        <span class="legend-chip"><span class="legend-chip__swatch" style="background:#5e2bbb;"></span>Subscribed</span>
                        <span class="legend-chip"><span class="legend-chip__swatch" style="background:#111827;"></span>Blacklisted</span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<form id="bulk-contact-action-form" method="POST" action="{{ route('admin.my-contacts.bulkAction') }}">
    @csrf
</form>

<div class="card mb-4">
    <div class="card-body py-3">
        <div class="contact-bulk-inline">
            <select class="form-control contact-bulk-inline__select" name="action" form="bulk-contact-action-form" required>
                <option value="">Select action</option>
                <option value="publish">Publish</option>
                <option value="unpublish">Unpublish</option>
                <option value="subscribe">Mark Default / Subscribed</option>
                <option value="unsubscribe">Mark Unsubscribed</option>
                <option value="mark_known">Mark Known Contact</option>
                <option value="blacklist">Mark Blacklisted</option>
                <option value="mark_default">Reset to Default</option>
                <option value="delete">Delete</option>
            </select>
            <button class="btn btn-danger" type="submit" form="bulk-contact-action-form" onclick="return confirm('Apply this action to selected contacts?');">Run Bulk Action</button>
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" id="select-all-contacts">
                <label class="form-check-label" for="select-all-contacts">Select all visible contacts</label>
            </div>
        </div>
    </div>
</div>

    @forelse($myContacts as $myContact)
        @php
            $statusClass = match($myContact->contact_status) {
                \App\Models\MyContact::STATUS_UNSUBSCRIBED => 'contact-card--unsubscribed',
                \App\Models\MyContact::STATUS_KNOWN => 'contact-card--known',
                \App\Models\MyContact::STATUS_BLACKLISTED => 'contact-card--blacklisted',
                default => 'contact-card--default',
            };
            $gender = strtolower(trim((string) $myContact->gender));
            $genderIcon = $gender === 'female' ? 'fa-venus' : ($gender === 'male' ? 'fa-mars' : 'fa-user');
            $linkedinDisplay = trim((string) parse_url((string) $myContact->linkedin_profile, PHP_URL_PATH), '/');
            $linkedinDisplay = $linkedinDisplay !== '' ? preg_replace('#^in/#i', '', $linkedinDisplay) : '';
            $picture = $myContact->picture_url
                ? asset(ltrim($myContact->picture_url, '/'))
                : asset(($myContact->gender ?? '') === 'Female' ? 'images/placeholders/contact-female.svg' : 'images/placeholders/contact-male.svg');
        @endphp

        <div class="card contact-card {{ $statusClass }} mb-4">
            <div class="contact-card__header">
                <div class="contact-card__header-line">
                    <div class="contact-card__header-left">
                        <input class="contact-check" type="checkbox" name="contact_ids[]" value="{{ $myContact->id }}" form="bulk-contact-action-form">
                        <span class="contact-card__header-meta"><span class="contact-card__header-box {{ $myContact->show_email ? 'contact-card__header-box--checked' : '' }}"></span>Email-id</span>
                        <span class="contact-card__header-meta"><span class="contact-card__header-box {{ $myContact->show_whatsapp ? 'contact-card__header-box--checked' : '' }}"></span>Whatsapp</span>
                        <span class="contact-card__header-meta"><span class="contact-card__header-box {{ $myContact->show_linkedin ? 'contact-card__header-box--checked' : '' }}"></span>Linked-In</span>
                        <span class="contact-card__header-meta"><span class="contact-card__header-box {{ $myContact->show_website ? 'contact-card__header-box--checked' : '' }}"></span>Website</span>
                        <span class="contact-card__header-meta"><span class="contact-card__header-box {{ $myContact->show_category ? 'contact-card__header-box--checked' : '' }}"></span>Category</span>
                        <span class="contact-card__header-meta"><span class="contact-card__header-box {{ $myContact->show_expertise ? 'contact-card__header-box--checked' : '' }}"></span>Expertise</span>
                        <span class="contact-card__header-meta"><span class="contact-card__header-box {{ $myContact->experience ? 'contact-card__header-box--checked' : '' }}"></span>Experience</span>
                    </div>
                    <div class="contact-card__header-right">
                        <span class="contact-status-badge">{{ $myContact->status_label }}</span>
                        <span class="contact-status-badge">{{ $myContact->is_published ? 'Published' : 'Unpublished' }}</span>
                        @php($starCount = max(0, (int) $myContact->stars))
                        <span>Stars: {!! $starCount > 0 ? str_repeat('&#9733;', $starCount) : '-' !!}</span>
                        <div class="contact-footer-actions">
                            <a class="btn btn-sm btn-light contact-btn" href="{{ route('admin.my-contacts.show', $myContact) }}">View</a>
                            <a class="btn btn-sm btn-dark contact-btn" href="{{ route('admin.my-contacts.edit', $myContact) }}">Edit</a>
                            <form action="{{ route('admin.my-contacts.destroy', $myContact) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this record?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-light contact-btn" type="submit">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="contact-card__body">
                <div class="contact-body-grid">
                    <div class="contact-body-col contact-body-col--profile">
                        <div class="contact-profile-wrap">
                            <div>
                                <img src="{{ $picture }}" alt="{{ $myContact->name }}" class="contact-avatar">
                            </div>
                            <div>
                                <h4 class="contact-profile-name">{{ $myContact->name }}</h4>
                                <p class="contact-profile-sub">{{ $myContact->designation ?: 'No designation' }}</p>
                                <p class="contact-profile-company">{{ $myContact->company_name ?: 'No company' }}</p>
                                <p class="contact-profile-country">
                                    {{ $myContact->country ?: 'No country' }}
                                </p>

                                <div class="contact-links">
                                    @if($myContact->official_email)
                                        <a href="mailto:{{ $myContact->official_email }}" class="contact-link-icon" title="Email"><i class="fa fa-envelope"></i></a>
                                    @endif
                                    @if($myContact->whatsapp_number)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $myContact->whatsapp_number) }}" target="_blank" rel="noopener" class="contact-link-icon" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                                    @endif
                                    @if($myContact->linkedin_profile)
                                        <a href="{{ $myContact->linkedin_profile }}" target="_blank" rel="noopener" class="contact-link-icon" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                                    @endif
                                    @if($myContact->gender)
                                        <span class="contact-link-icon" title="{{ $myContact->gender }}"><i class="fa {{ $genderIcon }}"></i></span>
                                    @endif
                                    @if($myContact->is_whitelisted)
                                        <span class="contact-link-icon" title="Key Person" aria-label="Key Person">
                                            <i class="fas fa-crown"></i>
                                        </span>
                                    @endif
                                    @if($myContact->web_url)
                                        <a href="{{ $myContact->web_url }}" target="_blank" rel="noopener" class="contact-link-icon" title="Website"><i class="fa fa-globe"></i></a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="contact-body-col">
                        <div class="contact-info-list">
                            <div class="contact-info-row">
                                <strong>Official:</strong>
                                <span>{{ $myContact->official_email ?: '-' }}</span>
                            </div>
                            <div class="contact-info-row">
                                <strong>Personal:</strong>
                                <span>{{ $myContact->personal_email ?: '-' }}</span>
                            </div>
                            <div class="contact-info-row">
                                <strong>Alternate:</strong>
                                <span>{{ $myContact->alternate_email ?: '-' }}</span>
                            </div>
                            <div class="contact-info-row">
                                <strong>LinkedIn:</strong>
                                @if($myContact->linkedin_profile)
                                    <a href="{{ $myContact->linkedin_profile }}" target="_blank" rel="noopener" class="contact-link-value">{{ $linkedinDisplay ?: $myContact->linkedin_profile }}</a>
                                @else
                                    <span>-</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="contact-body-col">
                        <div class="contact-info-list">
                            <div class="contact-info-row">
                                <strong>WhatsApp:</strong>
                                <span>{{ $myContact->whatsapp_number ?: '-' }}</span>
                            </div>
                            <div class="contact-info-row">
                                <strong>Mobile:</strong>
                                <span>{{ $myContact->mobile_number ?: '-' }}</span>
                            </div>
                            <div class="contact-info-row">
                                <strong>Alt. Mobile:</strong>
                                <span>{{ $myContact->alternate_mobile_number ?: '-' }}</span>
                            </div>
                            <div class="contact-info-row">
                                <strong>Key Role:</strong>
                                <span>{{ $myContact->role ?: '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="contact-body-col">
                        <div class="contact-side-list">
                            <div class="contact-side-group">
                                <div class="contact-side-row"><strong>Category:</strong> {{ $myContact->category ?: '-' }}</div>
                                <div class="contact-side-row"><strong>Area of Expertise:</strong> {{ $myContact->specialization ?: '-' }}</div>
                                <div class="contact-side-row"><strong>Speaker / Delegate:</strong> {{ $myContact->speaker_tag ?: ($myContact->role ?: '-') }}</div>
                            </div>
                            <div class="contact-side-group">
                                <div class="contact-side-row"><strong>Experience:</strong> {{ $myContact->experience ?: '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="contact-detail-divider">
                    <div class="contact-pill-row">
                        <div class="contact-pill-stack">
                            @forelse($myContact->event_list as $event)
                                <span class="contact-pill">{{ $event }}</span>
                            @empty
                                <span class="contact-pill">No events assigned</span>
                            @endforelse
                        </div>
                        <div class="contact-pill-stack">
                            @if($myContact->is_blacklisted)
                                <span class="contact-pill contact-pill--status">Blacklisted</span>
                            @endif
                            @if($myContact->is_whitelisted)
                                <span class="contact-pill">Key Person</span>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            <div class="contact-card__footer">
                <div class="contact-card__footer-line">
                    <div>{{ $myContact->official_email ?: 'No primary email' }}</div>
                    <div>{{ $myContact->mobile_number ?: ($myContact->whatsapp_number ?: 'No direct phone') }}</div>
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body">{{ trans('global.no_entries_in_table') }}</div>
        </div>
    @endforelse
{{ $myContacts->links() }}
@endsection

@section('scripts')
@parent
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var selectAll = document.getElementById('select-all-contacts');

        if (!selectAll) {
            return;
        }

        selectAll.addEventListener('change', function () {
            document.querySelectorAll('input[name="contact_ids[]"]').forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });
        });
    });
</script>
@endsection
