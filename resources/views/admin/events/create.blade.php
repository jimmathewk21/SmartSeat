@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">{{ trans('global.create') }} {{ trans('cruds.event.title_singular') }}</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group"><label class="required" for="title">{{ trans('cruds.event.fields.title') }}</label><input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" type="text" name="title" id="title" value="{{ old('title') }}" required>@if($errors->has('title'))<div class="invalid-feedback">{{ $errors->first('title') }}</div>@endif</div>
            <div class="form-group"><label class="required" for="slug">{{ trans('cruds.event.fields.slug') }}</label><input class="form-control {{ $errors->has('slug') ? 'is-invalid' : '' }}" type="text" name="slug" id="slug" value="{{ old('slug') }}" required>@if($errors->has('slug'))<div class="invalid-feedback">{{ $errors->first('slug') }}</div>@endif<small class="form-text text-muted">Used in frontend event URLs, for example: <code>/events/your-slug</code> and ticket page <code>/events/your-slug/tickets</code>. Use lowercase letters, numbers, and hyphens only.</small></div>
            <div class="form-group"><label for="venue">{{ trans('cruds.event.fields.venue') }}</label><input class="form-control {{ $errors->has('venue') ? 'is-invalid' : '' }}" type="text" name="venue" id="venue" value="{{ old('venue') }}">@if($errors->has('venue'))<div class="invalid-feedback">{{ $errors->first('venue') }}</div>@endif</div>
            <div class="form-group"><label for="location">{{ trans('cruds.event.fields.location') }}</label><input class="form-control {{ $errors->has('location') ? 'is-invalid' : '' }}" type="text" name="location" id="location" value="{{ old('location') }}" placeholder="City, Country">@if($errors->has('location'))<div class="invalid-feedback">{{ $errors->first('location') }}</div>@endif</div>
            <div class="form-group"><label for="start_at">{{ trans('cruds.event.fields.start_at') }}</label><input class="form-control {{ $errors->has('start_at') ? 'is-invalid' : '' }}" type="date" name="start_at" id="start_at" value="{{ old('start_at') ? \Carbon\Carbon::parse(old('start_at'))->format('Y-m-d') : '' }}">@if($errors->has('start_at'))<div class="invalid-feedback">{{ $errors->first('start_at') }}</div>@endif</div>
            <div class="form-group"><label for="end_at">{{ trans('cruds.event.fields.end_at') }}</label><input class="form-control {{ $errors->has('end_at') ? 'is-invalid' : '' }}" type="date" name="end_at" id="end_at" value="{{ old('end_at') ? \Carbon\Carbon::parse(old('end_at'))->format('Y-m-d') : '' }}">@if($errors->has('end_at'))<div class="invalid-feedback">{{ $errors->first('end_at') }}</div>@endif</div>
            <div class="form-group"><label for="description">{{ trans('cruds.event.fields.description') }}</label><textarea class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" name="description" id="description">{{ old('description') }}</textarea>@if($errors->has('description'))<div class="invalid-feedback">{{ $errors->first('description') }}</div>@endif</div>
            <div class="form-row">
                <div class="col-md-6 form-group">
                    <label for="image_url">Card Image URL/Path</label>
                    <input class="form-control {{ $errors->has('image_url') ? 'is-invalid' : '' }}" type="text" name="image_url" id="image_url" value="{{ old('image_url') }}" placeholder="https:// or uploads/events/file.webp">
                    @if($errors->has('image_url'))<div class="invalid-feedback">{{ $errors->first('image_url') }}</div>@endif
                    <small class="form-text text-muted">Use an image URL or upload a file below.</small>
                </div>
                <div class="col-md-6 form-group">
                    <label for="image_file">Upload Card Image</label>
                    <input class="form-control-file {{ $errors->has('image_file') ? 'is-invalid' : '' }}" type="file" name="image_file" id="image_file" accept=".jpg,.jpeg,.png,.webp,.svg">
                    @if($errors->has('image_file'))<div class="invalid-feedback d-block">{{ $errors->first('image_file') }}</div>@endif
                    <small class="form-text text-muted">Recommended image size: 533x437px.</small>
                </div>
            </div>
            <div class="form-row">
                <div class="col-md-6 form-group">
                    <label for="gallery_url">View Gallery Link</label>
                    <input class="form-control {{ $errors->has('gallery_url') ? 'is-invalid' : '' }}" type="text" name="gallery_url" id="gallery_url" value="{{ old('gallery_url') }}" placeholder="https://">
                    @if($errors->has('gallery_url'))<div class="invalid-feedback">{{ $errors->first('gallery_url') }}</div>@endif
                </div>
                <div class="col-md-6 form-group">
                    <label for="brochure_url">View Brochure Link</label>
                    <input class="form-control {{ $errors->has('brochure_url') ? 'is-invalid' : '' }}" type="text" name="brochure_url" id="brochure_url" value="{{ old('brochure_url') }}" placeholder="https://">
                    @if($errors->has('brochure_url'))<div class="invalid-feedback">{{ $errors->first('brochure_url') }}</div>@endif
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-header">Ticket Page Hero Content</div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="col-md-4 form-group">
                            <label for="ticket_hero_label">Ticket Page Top Label</label>
                            <input class="form-control {{ $errors->has('ticket_hero_label') ? 'is-invalid' : '' }}" type="text" name="ticket_hero_label" id="ticket_hero_label" value="{{ old('ticket_hero_label', 'TICKET BOOKING') }}" maxlength="120" placeholder="TICKET BOOKING">
                            @if($errors->has('ticket_hero_label'))<div class="invalid-feedback">{{ $errors->first('ticket_hero_label') }}</div>@endif
                            <small class="form-text text-muted">Shown above the ticket page title.</small>
                        </div>
                        <div class="col-md-8 form-group">
                            <label for="ticket_hero_suffix">Ticket Page Title Suffix</label>
                            <input class="form-control {{ $errors->has('ticket_hero_suffix') ? 'is-invalid' : '' }}" type="text" name="ticket_hero_suffix" id="ticket_hero_suffix" value="{{ old('ticket_hero_suffix', '/ reserve your seat') }}" maxlength="255" placeholder="/ reserve your seat">
                            @if($errors->has('ticket_hero_suffix'))<div class="invalid-feedback">{{ $errors->first('ticket_hero_suffix') }}</div>@endif
                            <small class="form-text text-muted">Shown after the event title on the ticket page hero.</small>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label for="ticket_hero_description">Ticket Page Description</label>
                        <textarea class="form-control {{ $errors->has('ticket_hero_description') ? 'is-invalid' : '' }}" name="ticket_hero_description" id="ticket_hero_description" rows="3" maxlength="500" placeholder="Choose the best registration package for this event, review the included benefits, and continue to a secure checkout experience.">{{ old('ticket_hero_description', 'Choose the best registration package for this event, review the included benefits, and continue to a secure checkout experience.') }}</textarea>
                        @if($errors->has('ticket_hero_description'))<div class="invalid-feedback">{{ $errors->first('ticket_hero_description') }}</div>@endif
                        <small class="form-text text-muted">Shown below the ticket page title on the ticket page hero.</small>
                    </div>
                </div>
            </div>
            <div class="form-group"><label class="required" for="sort_order">{{ trans('cruds.event.fields.sort_order') }}</label><input class="form-control {{ $errors->has('sort_order') ? 'is-invalid' : '' }}" type="number" min="0" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" required>@if($errors->has('sort_order'))<div class="invalid-feedback">{{ $errors->first('sort_order') }}</div>@endif</div>
            <div class="form-group"><label class="required" for="is_published">{{ trans('cruds.event.fields.is_published') }}</label><select class="form-control" name="is_published" id="is_published"><option value="1" @selected(old('is_published', '1') === '1')>{{ trans('global.yes') }}</option><option value="0" @selected(old('is_published', '1') === '0')>{{ trans('global.no') }}</option></select><small class="form-text text-muted">This single publish control decides whether the event appears on the public Past & Upcoming Events listing.</small></div>

            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Ticket Plans (INR + USD) - Optional</span>
                    <button class="btn btn-sm btn-primary" type="button" id="add-plan-btn">+ Add Plan</button>
                </div>
                <div class="card-body" id="plans-wrap" data-errors='@json($errors->toArray())'></div>
            </div>

            <div class="form-group mt-3"><button class="btn btn-danger" type="submit">{{ trans('global.save') }}</button></div>
        </form>
    </div>
</div>

<script>
    (function () {
        const wrapper = document.getElementById('plans-wrap');
        const addBtn = document.getElementById('add-plan-btn');
        const plans = @json(array_values(old('plans', $planDefaults)));
        const errors = wrapper ? JSON.parse(wrapper.dataset.errors || '{}') : {};

        if (!wrapper) return;

        const fieldError = (index, key) => (errors['plans.' + index + '.' + key] || [])[0] || '';

        function rowTemplate(plan, index) {
            const checked = Number(plan.is_active) === 1 || plan.is_active === true ? 'checked' : '';
            const eName = fieldError(index, 'name');
            const eCaption = fieldError(index, 'caption');
            const eBackground = fieldError(index, 'background_image_url');
            const eMrpInr = fieldError(index, 'mrp_inr');
            const eSaleInr = fieldError(index, 'sale_inr');
            const eMrpUsd = fieldError(index, 'mrp_usd');
            const eSaleUsd = fieldError(index, 'sale_usd');
            const eBenefits = fieldError(index, 'benefits');
            const eSort = fieldError(index, 'sort_order');

            return `
                <div class="border rounded p-3 mb-3 plan-row" data-index="${index}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Plan ${index + 1}</h5>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-plan-btn">Remove</button>
                    </div>
                    <input type="hidden" name="plans[${index}][id]" value="${plan.id || ''}">
                    <div class="form-row">
                        <div class="col-md-4 form-group">
                            <label class="required">Plan Name</label>
                            <input class="form-control ${eName ? 'is-invalid' : ''}" type="text" name="plans[${index}][name]" value="${plan.name || ''}" required>
                            ${eName ? `<div class="invalid-feedback">${eName}</div>` : ''}
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Background Image URL/Path</label>
                            <input class="form-control ${eBackground ? 'is-invalid' : ''}" type="text" name="plans[${index}][background_image_url]" value="${plan.background_image_url || ''}" placeholder="https:// or uploads/event-plans/file.webp">
                            ${eBackground ? `<div class="invalid-feedback">${eBackground}</div>` : ''}
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Upload Background Image</label>
                            <input class="form-control-file" type="file" name="plan_background_image_file_${index}" accept=".jpg,.jpeg,.png,.webp,.svg">
                            <small class="form-text text-muted">Used on the home ticket card background.</small>
                            <small class="form-text text-muted">Recommended image size: 1200x700px.</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-12 form-group">
                            <label>Plan Caption</label>
                            <input class="form-control ${eCaption ? 'is-invalid' : ''}" type="text" name="plans[${index}][caption]" value="${plan.caption || ''}" maxlength="255" placeholder="Conference access with curated benefits and member-ready pricing.">
                            <small class="form-text text-muted">Shown under the plan name on the frontend. Leave blank to use the checkout default caption.</small>
                            ${eCaption ? `<div class="invalid-feedback">${eCaption}</div>` : ''}
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-2 form-group">
                            <label class="required">MRP INR</label>
                            <input class="form-control ${eMrpInr ? 'is-invalid' : ''}" type="number" min="0" step="0.01" name="plans[${index}][mrp_inr]" value="${plan.mrp_inr || '0.00'}" required>
                            ${eMrpInr ? `<div class="invalid-feedback">${eMrpInr}</div>` : ''}
                        </div>
                        <div class="col-md-2 form-group">
                            <label class="required">Sale INR</label>
                            <input class="form-control ${eSaleInr ? 'is-invalid' : ''}" type="number" min="0" step="0.01" name="plans[${index}][sale_inr]" value="${plan.sale_inr || '0.00'}" required>
                            ${eSaleInr ? `<div class="invalid-feedback">${eSaleInr}</div>` : ''}
                        </div>
                        <div class="col-md-2 form-group">
                            <label class="required">MRP USD</label>
                            <input class="form-control ${eMrpUsd ? 'is-invalid' : ''}" type="number" min="0" step="0.01" name="plans[${index}][mrp_usd]" value="${plan.mrp_usd || '0.00'}" required>
                            ${eMrpUsd ? `<div class="invalid-feedback">${eMrpUsd}</div>` : ''}
                        </div>
                        <div class="col-md-2 form-group">
                            <label class="required">Sale USD</label>
                            <input class="form-control ${eSaleUsd ? 'is-invalid' : ''}" type="number" min="0" step="0.01" name="plans[${index}][sale_usd]" value="${plan.sale_usd || '0.00'}" required>
                            ${eSaleUsd ? `<div class="invalid-feedback">${eSaleUsd}</div>` : ''}
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-8 form-group">
                            <label>Benefits (one per line)</label>
                            <textarea class="form-control ${eBenefits ? 'is-invalid' : ''}" rows="3" name="plans[${index}][benefits]">${plan.benefits || ''}</textarea>
                            ${eBenefits ? `<div class="invalid-feedback">${eBenefits}</div>` : ''}
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Valid Till Text</label>
                            <input class="form-control" type="text" name="plans[${index}][valid_till_text]" value="${plan.valid_till_text || ''}">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Sort Order</label>
                            <input class="form-control ${eSort ? 'is-invalid' : ''}" type="number" min="0" name="plans[${index}][sort_order]" value="${plan.sort_order ?? index}">
                            ${eSort ? `<div class="invalid-feedback">${eSort}</div>` : ''}
                        </div>
                    </div>
                    <div class="form-group form-check mb-0">
                        <input type="hidden" name="plans[${index}][is_active]" value="0">
                        <input class="form-check-input" type="checkbox" name="plans[${index}][is_active]" value="1" ${checked}>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
            `;
        }

        function renderRows() {
            wrapper.innerHTML = plans.map((plan, index) => rowTemplate(plan, index)).join('');
            wrapper.querySelectorAll('.remove-plan-btn').forEach((btn) => {
                btn.addEventListener('click', function () {
                    const row = btn.closest('.plan-row');
                    const idx = Number(row.dataset.index);
                    plans.splice(idx, 1);
                    renderRows();
                });
            });
        }

        addBtn.addEventListener('click', function () {
            plans.push({
                id: '',
                name: '',
                caption: '',
                mrp_inr: '0.00',
                sale_inr: '0.00',
                mrp_usd: '0.00',
                sale_usd: '0.00',
                background_image_url: '',
                benefits: '',
                valid_till_text: '',
                is_active: 1,
                sort_order: plans.length
            });
            renderRows();
        });

        renderRows();
    })();
</script>
@endsection
