@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        Frontend Header
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.frontend.header.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="logo_url">Logo URL/Path</label>
                <input class="form-control" type="text" name="logo_url" id="logo_url" value="{{ old('logo_url', $header['logo_url']) }}" placeholder="images/logo.webp or https://example.com/logo.png">
                <small class="form-text text-muted">Use a public path like <code>images/logo.webp</code> to keep existing design.</small>
            </div>
            <div class="form-group">
                <label for="logo_image">Choose Logo Image</label>
                <input class="form-control-file" type="file" name="logo_image" id="logo_image" accept="image/*">
                <small class="form-text text-muted">If selected, uploaded image will be used as logo.</small>
                <small class="form-text text-muted">Recommended image size: 240x80px.</small>
            </div>

            <hr>
            <h5>Menu Items</h5>
            <div id="menu-items-wrapper">
                @forelse(old('menu_items', $menuRows) as $index => $item)
                    <div class="menu-item-row border rounded p-3 mb-3">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Menu Name</label>
                                <input type="text" class="form-control" name="menu_items[{{ $index }}][name]" value="{{ $item['name'] ?? '' }}" placeholder="Home">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Select Page</label>
                                <select class="form-control menu-page-select" name="menu_items[{{ $index }}][page]">
                                    @foreach($pageOptions as $value => $label)
                                        <option value="{{ $value }}" {{ ($item['page'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-3 custom-url-group {{ ($item['page'] ?? '') === 'custom' ? '' : 'd-none' }}">
                                <label>Custom URL</label>
                                <input type="url" class="form-control" name="menu_items[{{ $index }}][custom_url]" value="{{ $item['custom_url'] ?? '' }}" placeholder="https://example.com/page">
                            </div>
                            <div class="form-group col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-danger btn-sm remove-menu-item">X</button>
                            </div>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>

            <button type="button" class="btn btn-secondary mb-4" id="add-menu-item">Add Menu Item</button>

            <hr>
            <h5>Header Button</h5>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="button_text">Button Text</label>
                    <input class="form-control" type="text" name="button_text" id="button_text" value="{{ old('button_text', $header['button_text']) }}" placeholder="Buy Tickets">
                </div>
                <div class="form-group col-md-4">
                    <label for="button_page">Button Link/Page</label>
                    <select class="form-control menu-page-select" name="button_page" id="button_page">
                        @foreach($pageOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('button_page', $buttonPage) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4 {{ old('button_page', $buttonPage) === 'custom' ? '' : 'd-none' }}" id="button-custom-url-group">
                    <label for="button_custom_url">Button Custom URL</label>
                    <input class="form-control" type="url" name="button_custom_url" id="button_custom_url" value="{{ old('button_custom_url', $buttonCustomUrl) }}" placeholder="https://example.com/tickets">
                </div>
            </div>

            <div class="form-group">
                <button class="btn btn-primary" type="submit">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
    (function () {
        const wrapper = document.getElementById('menu-items-wrapper');
        const addBtn = document.getElementById('add-menu-item');
        let menuIndex = {{ count(old('menu_items', $menuRows)) }};

        const pageOptions = `{!! collect($pageOptions)->map(function ($label, $value) {
            return '<option value="' . e($value) . '">' . e($label) . '</option>';
        })->implode('') !!}`;

        function bindRowEvents(row) {
            const removeBtn = row.querySelector('.remove-menu-item');
            const select = row.querySelector('.menu-page-select');
            const customGroup = row.querySelector('.custom-url-group');

            removeBtn.addEventListener('click', function () {
                row.remove();
            });

            select.addEventListener('change', function () {
                customGroup.classList.toggle('d-none', select.value !== 'custom');
            });
        }

        function createRow(index) {
            const row = document.createElement('div');
            row.className = 'menu-item-row border rounded p-3 mb-3';
            row.innerHTML = `
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Menu Name</label>
                        <input type="text" class="form-control" name="menu_items[${index}][name]" placeholder="Menu Name">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Select Page</label>
                        <select class="form-control menu-page-select" name="menu_items[${index}][page]">${pageOptions}</select>
                    </div>
                    <div class="form-group col-md-3 custom-url-group d-none">
                        <label>Custom URL</label>
                        <input type="url" class="form-control" name="menu_items[${index}][custom_url]" placeholder="https://example.com/page">
                    </div>
                    <div class="form-group col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-menu-item">X</button>
                    </div>
                </div>
            `;

            bindRowEvents(row);
            return row;
        }

        wrapper.querySelectorAll('.menu-item-row').forEach(bindRowEvents);

        addBtn.addEventListener('click', function () {
            wrapper.appendChild(createRow(menuIndex++));
        });

        const buttonPage = document.getElementById('button_page');
        const buttonCustomUrlGroup = document.getElementById('button-custom-url-group');

        buttonPage.addEventListener('change', function () {
            buttonCustomUrlGroup.classList.toggle('d-none', buttonPage.value !== 'custom');
        });
    })();
</script>
@endsection
