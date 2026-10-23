@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        Site Settings
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.site-meta.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="default_title">Default Page Title</label>
                <input class="form-control" type="text" name="default_title" id="default_title" value="{{ old('default_title', $settings['default_title']) }}" required>
            </div>

            <div class="form-group">
                <label for="site_icon">Frontend Site Icon URL/Path</label>
                <input class="form-control" type="text" name="site_icon" id="site_icon" value="{{ old('site_icon', $settings['site_icon']) }}" placeholder="images/icon.webp">
            </div>

            <div class="form-group">
                <label for="site_icon_file">Choose Frontend Site Icon</label>
                <input class="form-control-file" type="file" name="site_icon_file" id="site_icon_file" accept="image/*,.ico">
                <small class="form-text text-muted">If selected, uploaded icon will be used for the frontend.</small>
                <small class="form-text text-muted">Recommended image size: 512x512px.</small>
            </div>

            <hr>

            <div class="form-group">
                <label for="admin_site_icon">Admin Panel Icon URL/Path</label>
                <input class="form-control" type="text" name="admin_site_icon" id="admin_site_icon" value="{{ old('admin_site_icon', $settings['admin_site_icon'] ?? '') }}" placeholder="images/admin-icon.webp">
            </div>

            <div class="form-group">
                <label for="admin_site_icon_file">Choose Admin Panel Icon</label>
                <input class="form-control-file" type="file" name="admin_site_icon_file" id="admin_site_icon_file" accept="image/*,.ico">
                <small class="form-text text-muted">If selected, uploaded icon will be used for the admin panel.</small>
                <small class="form-text text-muted">Recommended image size: 512x512px.</small>
            </div>

            {{--
            <hr>
            <h5>Page-wise Titles</h5>
            <p class="text-muted">Add path and title for each page. Example paths: <code>/</code>, <code>/about</code>, <code>/contact</code></p>

            <div id="page-titles-wrapper">
                @foreach(old('pages', $settings['pages']) as $index => $page)
                    <div class="page-title-row border rounded p-3 mb-3">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Path</label>
                                <input type="text" class="form-control" name="pages[{{ $index }}][path]" value="{{ $page['path'] ?? '' }}" placeholder="/about">
                            </div>
                            <div class="form-group col-md-7">
                                <label>Title</label>
                                <input type="text" class="form-control" name="pages[{{ $index }}][title]" value="{{ $page['title'] ?? '' }}" placeholder="About Us - Your Site">
                            </div>
                            <div class="form-group col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-danger btn-sm remove-page-title">X</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" class="btn btn-secondary mb-4" id="add-page-title">Add Page Title</button>
            --}}

            {{--
            <hr>
            <h5>Restricted Pages (Login Required)</h5>
            <p class="text-muted">Users must sign in before opening these paths.</p>

            <div id="restricted-pages-wrapper">
                @foreach(old('restricted_paths', $settings['restricted_paths'] ?? []) as $index => $path)
                    <div class="restricted-path-row border rounded p-3 mb-3">
                        <div class="form-row">
                            <div class="form-group col-md-11">
                                <label>Path</label>
                                <input type="text" class="form-control" name="restricted_paths[{{ $index }}]" value="{{ $path }}" placeholder="/speakers">
                            </div>
                            <div class="form-group col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-danger btn-sm remove-restricted-path">X</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" class="btn btn-secondary mb-4" id="add-restricted-path">Add Restricted Path</button>
            --}}

            <div class="form-group">
                <button class="btn btn-primary" type="submit">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
