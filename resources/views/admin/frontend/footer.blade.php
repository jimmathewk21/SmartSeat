@extends('layouts.admin')

@section('content')
@php
    $socialLinks = old('social_links', $footer['social_links'] ?? []);
    $upcomingEvents = old('upcoming_events', $footer['upcoming_events'] ?? []);
    $quickLinks = old('quick_links', $footer['quick_links'] ?? []);
    $companyLinks = old('company_links', $footer['company_links'] ?? []);
    $bottomLinks = old('bottom_links', $footer['bottom_links'] ?? []);
@endphp

<form method="POST" action="{{ route('admin.frontend.footer.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header">Frontend Footer</div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="logo_url">Logo URL/Path</label>
                    <input class="form-control" type="text" name="logo_url" id="logo_url" value="{{ old('logo_url', $footer['logo_url'] ?? 'images/logo.webp') }}">
                </div>
                <div class="form-group col-md-6">
                    <label for="logo_image">Choose Logo Image</label>
                    <input class="form-control-file" type="file" name="logo_image" id="logo_image" accept="image/*">
                    <small class="form-text text-muted">Recommended image size: 240x80px.</small>
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Social Media Links</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" data-repeater-add="#social-links-list" data-template="#social-links-template">+ Add</button>
            </div>
            <div id="social-links-list">
                @forelse($socialLinks as $index => $item)
                    <div class="form-row repeater-item align-items-end mb-2">
                        <div class="form-group col-md-5 mb-2">
                            <label>Icon Class</label>
                            <input class="form-control" type="text" name="social_links[{{ $index }}][icon]" value="{{ $item['icon'] ?? '' }}" placeholder="fa-brands fa-facebook-f">
                        </div>
                        <div class="form-group col-md-6 mb-2">
                            <label>URL</label>
                            <input class="form-control" type="text" name="social_links[{{ $index }}][url]" value="{{ $item['url'] ?? '' }}" placeholder="https://example.com">
                        </div>
                        <div class="form-group col-md-1 mb-2 text-md-right">
                            <button type="button" class="btn btn-sm btn-outline-danger" data-repeater-remove>Remove</button>
                        </div>
                    </div>
                @empty
                    <div class="form-row repeater-item align-items-end mb-2">
                        <div class="form-group col-md-5 mb-2">
                            <label>Icon Class</label>
                            <input class="form-control" type="text" name="social_links[0][icon]" value="fa-brands fa-facebook-f" placeholder="fa-brands fa-facebook-f">
                        </div>
                        <div class="form-group col-md-6 mb-2">
                            <label>URL</label>
                            <input class="form-control" type="text" name="social_links[0][url]" value="#" placeholder="https://example.com">
                        </div>
                        <div class="form-group col-md-1 mb-2 text-md-right">
                            <button type="button" class="btn btn-sm btn-outline-danger" data-repeater-remove>Remove</button>
                        </div>
                    </div>
                @endforelse
            </div>
            <template id="social-links-template">
                <div class="form-row repeater-item align-items-end mb-2">
                    <div class="form-group col-md-5 mb-2">
                        <label>Icon Class</label>
                        <input class="form-control" type="text" data-name="icon" placeholder="fa-brands fa-facebook-f">
                    </div>
                    <div class="form-group col-md-6 mb-2">
                        <label>URL</label>
                        <input class="form-control" type="text" data-name="url" placeholder="https://example.com">
                    </div>
                    <div class="form-group col-md-1 mb-2 text-md-right">
                        <button type="button" class="btn btn-sm btn-outline-danger" data-repeater-remove>Remove</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Sitemap Column 1</div>
        <div class="card-body">
            <div class="form-group">
                <label for="upcoming_events_title">Column Title</label>
                <input class="form-control" type="text" name="upcoming_events_title" id="upcoming_events_title" value="{{ old('upcoming_events_title', $footer['upcoming_events_title'] ?? 'Upcoming Events') }}">
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Events</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" data-repeater-add="#upcoming-events-list" data-template="#upcoming-events-template">+ Add</button>
            </div>
            <div id="upcoming-events-list">
                @foreach($upcomingEvents as $index => $item)
                    <div class="repeater-item border rounded p-3 mb-2">
                        <div class="form-row align-items-end">
                            <div class="form-group col-md-4 mb-2">
                                <label>Date/Time</label>
                                <input class="form-control" type="text" name="upcoming_events[{{ $index }}][datetime]" value="{{ $item['datetime'] ?? '' }}">
                            </div>
                            <div class="form-group col-md-4 mb-2">
                                <label>Event Name</label>
                                <input class="form-control" type="text" name="upcoming_events[{{ $index }}][name]" value="{{ $item['name'] ?? '' }}">
                            </div>
                            <div class="form-group col-md-2 mb-2">
                                <label>CTA Text</label>
                                <input class="form-control" type="text" name="upcoming_events[{{ $index }}][cta_text]" value="{{ $item['cta_text'] ?? '' }}">
                            </div>
                            <div class="form-group col-md-2 mb-2">
                                <label>CTA Link</label>
                                <input class="form-control" type="text" name="upcoming_events[{{ $index }}][cta_link]" value="{{ $item['cta_link'] ?? '' }}">
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-repeater-remove>Remove</button>
                    </div>
                @endforeach
            </div>
            <template id="upcoming-events-template">
                <div class="repeater-item border rounded p-3 mb-2">
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-4 mb-2">
                            <label>Date/Time</label>
                            <input class="form-control" type="text" data-name="datetime">
                        </div>
                        <div class="form-group col-md-4 mb-2">
                            <label>Event Name</label>
                            <input class="form-control" type="text" data-name="name">
                        </div>
                        <div class="form-group col-md-2 mb-2">
                            <label>CTA Text</label>
                            <input class="form-control" type="text" data-name="cta_text">
                        </div>
                        <div class="form-group col-md-2 mb-2">
                            <label>CTA Link</label>
                            <input class="form-control" type="text" data-name="cta_link">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-repeater-remove>Remove</button>
                </div>
            </template>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Quick Links And Company Columns</div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="quick_links_title">Quick Links Title</label>
                    <input class="form-control" type="text" name="quick_links_title" id="quick_links_title" value="{{ old('quick_links_title', $footer['quick_links_title'] ?? 'Quick links') }}">
                </div>
                <div class="form-group col-md-6">
                    <label for="company_links_title">Company Links Title</label>
                    <input class="form-control" type="text" name="company_links_title" id="company_links_title" value="{{ old('company_links_title', $footer['company_links_title'] ?? 'Our Company') }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Quick Links</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-repeater-add="#quick-links-list" data-template="#quick-links-template">+ Add</button>
                    </div>
                    <div id="quick-links-list">
                        @foreach($quickLinks as $index => $item)
                            <div class="form-row repeater-item align-items-end mb-2">
                                <div class="form-group col-md-6 mb-2">
                                    <label>Text</label>
                                    <input class="form-control" type="text" name="quick_links[{{ $index }}][text]" value="{{ $item['text'] ?? '' }}">
                                </div>
                                <div class="form-group col-md-6 mb-2">
                                    <label>URL</label>
                                    <input class="form-control" type="text" name="quick_links[{{ $index }}][url]" value="{{ $item['url'] ?? '' }}">
                                </div>
                                <div class="form-group col-12 mb-2 text-md-right">
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-repeater-remove>Remove</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <template id="quick-links-template">
                        <div class="form-row repeater-item align-items-end mb-2">
                            <div class="form-group col-md-6 mb-2">
                                <label>Text</label>
                                <input class="form-control" type="text" data-name="text">
                            </div>
                            <div class="form-group col-md-6 mb-2">
                                <label>URL</label>
                                <input class="form-control" type="text" data-name="url">
                            </div>
                            <div class="form-group col-12 mb-2 text-md-right">
                                <button type="button" class="btn btn-sm btn-outline-danger" data-repeater-remove>Remove</button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="col-md-6 mt-3 mt-md-0">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Company Links</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-repeater-add="#company-links-list" data-template="#company-links-template">+ Add</button>
                    </div>
                    <div id="company-links-list">
                        @foreach($companyLinks as $index => $item)
                            <div class="form-row repeater-item align-items-end mb-2">
                                <div class="form-group col-md-6 mb-2">
                                    <label>Text</label>
                                    <input class="form-control" type="text" name="company_links[{{ $index }}][text]" value="{{ $item['text'] ?? '' }}">
                                </div>
                                <div class="form-group col-md-6 mb-2">
                                    <label>URL</label>
                                    <input class="form-control" type="text" name="company_links[{{ $index }}][url]" value="{{ $item['url'] ?? '' }}">
                                </div>
                                <div class="form-group col-12 mb-2 text-md-right">
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-repeater-remove>Remove</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <template id="company-links-template">
                        <div class="form-row repeater-item align-items-end mb-2">
                            <div class="form-group col-md-6 mb-2">
                                <label>Text</label>
                                <input class="form-control" type="text" data-name="text">
                            </div>
                            <div class="form-group col-md-6 mb-2">
                                <label>URL</label>
                                <input class="form-control" type="text" data-name="url">
                            </div>
                            <div class="form-group col-12 mb-2 text-md-right">
                                <button type="button" class="btn btn-sm btn-outline-danger" data-repeater-remove>Remove</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Contact And Bottom Bar</div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="contact_title">Contact Title</label>
                    <input class="form-control" type="text" name="contact_title" id="contact_title" value="{{ old('contact_title', $footer['contact_title'] ?? 'Contact') }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="contact_email">Contact Email</label>
                    <input class="form-control" type="text" name="contact_email" id="contact_email" value="{{ old('contact_email', $footer['contact_email'] ?? '') }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="contact_email_icon">Email Icon Class</label>
                    <input class="form-control" type="text" name="contact_email_icon" id="contact_email_icon" value="{{ old('contact_email_icon', $footer['contact_email_icon'] ?? 'fa fa-envelope') }}" placeholder="fa fa-envelope">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="contact_address">Contact Address</label>
                    <input class="form-control" type="text" name="contact_address" id="contact_address" value="{{ old('contact_address', $footer['contact_address'] ?? '') }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="contact_address_icon">Address Icon Class</label>
                    <input class="form-control" type="text" name="contact_address_icon" id="contact_address_icon" value="{{ old('contact_address_icon', $footer['contact_address_icon'] ?? 'fa fa-map-marker') }}" placeholder="fa fa-map-marker">
                </div>
                <div class="form-group col-md-3">
                    <label for="contact_phone">Contact Phone</label>
                    <input class="form-control" type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $footer['contact_phone'] ?? '') }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="contact_phone_icon">Phone Icon Class</label>
                    <input class="form-control" type="text" name="contact_phone_icon" id="contact_phone_icon" value="{{ old('contact_phone_icon', $footer['contact_phone_icon'] ?? 'fa fa-phone') }}" placeholder="fa fa-phone">
                </div>
            </div>

            <div class="form-group">
                <label for="copyright_text">Copyright Text</label>
                <textarea class="form-control" name="copyright_text" id="copyright_text" rows="3">{{ old('copyright_text', $footer['copyright_text'] ?? '') }}</textarea>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Bottom Links</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" data-repeater-add="#bottom-links-list" data-template="#bottom-links-template">+ Add</button>
            </div>
            <div id="bottom-links-list">
                @foreach($bottomLinks as $index => $item)
                    <div class="form-row repeater-item align-items-end mb-2">
                        <div class="form-group col-md-5 mb-2">
                            <label>Text</label>
                            <input class="form-control" type="text" name="bottom_links[{{ $index }}][text]" value="{{ $item['text'] ?? '' }}">
                        </div>
                        <div class="form-group col-md-6 mb-2">
                            <label>URL</label>
                            <input class="form-control" type="text" name="bottom_links[{{ $index }}][url]" value="{{ $item['url'] ?? '' }}">
                        </div>
                        <div class="form-group col-md-1 mb-2 text-md-right">
                            <button type="button" class="btn btn-sm btn-outline-danger" data-repeater-remove>Remove</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <template id="bottom-links-template">
                <div class="form-row repeater-item align-items-end mb-2">
                    <div class="form-group col-md-5 mb-2">
                        <label>Text</label>
                        <input class="form-control" type="text" data-name="text">
                    </div>
                    <div class="form-group col-md-6 mb-2">
                        <label>URL</label>
                        <input class="form-control" type="text" data-name="url">
                    </div>
                    <div class="form-group col-md-1 mb-2 text-md-right">
                        <button type="button" class="btn btn-sm btn-outline-danger" data-repeater-remove>Remove</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Actions</div>
        <div class="card-body">
            <button class="btn btn-primary" type="submit">Save</button>
        </div>
    </div>
</form>

<script>
(function () {
    function reindexRows(container) {
        var items = container.querySelectorAll('.repeater-item');
        items.forEach(function (row, index) {
            row.querySelectorAll('[data-name]').forEach(function (input) {
                var parent = container.id.replace('-list', '').replace(/-/g, '_');
                input.name = parent + '[' + index + '][' + input.getAttribute('data-name') + ']';
            });

            row.querySelectorAll('input[name], textarea[name], select[name]').forEach(function (input) {
                if (!input.hasAttribute('data-name')) {
                    input.name = input.name.replace(/\[\d+\]/, '[' + index + ']');
                }
            });
        });
    }

    document.querySelectorAll('[data-repeater-add]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var listSelector = btn.getAttribute('data-repeater-add');
            var templateSelector = btn.getAttribute('data-template');
            var list = document.querySelector(listSelector);
            var template = document.querySelector(templateSelector);
            if (!list || !template) {
                return;
            }

            var wrapper = document.createElement('div');
            wrapper.innerHTML = template.innerHTML.trim();
            var node = wrapper.firstElementChild;
            list.appendChild(node);
            reindexRows(list);
        });
    });

    document.addEventListener('click', function (event) {
        if (!event.target.matches('[data-repeater-remove]')) {
            return;
        }

        var row = event.target.closest('.repeater-item');
        var list = row ? row.parentElement : null;
        if (row) {
            row.remove();
        }
        if (list) {
            reindexRows(list);
        }
    });

    ['#social-links-list', '#upcoming-events-list', '#quick-links-list', '#company-links-list', '#bottom-links-list'].forEach(function (selector) {
        var list = document.querySelector(selector);
        if (list) {
            reindexRows(list);
        }
    });
})();
</script>
@endsection

