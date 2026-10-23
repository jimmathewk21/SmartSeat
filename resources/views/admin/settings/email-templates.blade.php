@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">Admin Email Templates</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.email-templates.update') }}">
            @csrf
            @method('PUT')

            @foreach($definitions as $key => $definition)
                @php
                    $template = $settings['templates'][$key] ?? [];
                @endphp
                <div class="border rounded p-4 mb-4">
                    <div class="form-group form-check mb-3">
                        <input type="hidden" name="templates[{{ $key }}][enabled]" value="0">
                        <input type="checkbox" class="form-check-input" id="templates_{{ $key }}_enabled" name="templates[{{ $key }}][enabled]" value="1" @checked(old('templates.' . $key . '.enabled', $template['enabled'] ?? false))>
                        <label class="form-check-label font-weight-bold" for="templates_{{ $key }}_enabled">{{ $definition['label'] }}</label>
                    </div>

                    <div class="form-group">
                        <label for="templates_{{ $key }}_subject">Subject</label>
                        <input
                            type="text"
                            class="form-control"
                            id="templates_{{ $key }}_subject"
                            name="templates[{{ $key }}][subject]"
                            value="{{ old('templates.' . $key . '.subject', $template['subject'] ?? '') }}"
                        >
                    </div>

                    <div class="form-group">
                        <label for="templates_{{ $key }}_body">HTML Body</label>
                        <textarea
                            class="form-control ckeditor"
                            id="templates_{{ $key }}_body"
                            name="templates[{{ $key }}][body]"
                            rows="8"
                        >{{ old('templates.' . $key . '.body', $template['body'] ?? '') }}</textarea>
                        <small class="form-text text-muted">You can format this with the editor. Keep placeholders like <code>&#123;&#123;name&#125;&#125;</code> unchanged so they are replaced correctly.</small>
                    </div>

                    <div class="small text-muted">
                        Available placeholders:
                        @foreach($definition['placeholders'] as $placeholder)
                            <code>&#123;&#123;{{ $placeholder }}&#125;&#125;</code>@if(! $loop->last), @endif
                        @endforeach
                    </div>
                </div>
            @endforeach

            <button class="btn btn-primary" type="submit">Save</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
    document.querySelectorAll('.ckeditor').forEach(function (element) {
        if (element.dataset.ckeditorInitialized === '1') {
            return;
        }

        ClassicEditor
            .create(element)
            .then(function (editor) {
                element.dataset.ckeditorInitialized = '1';
                editor.model.document.on('change:data', function () {
                    element.value = editor.getData();
                });
            })
            .catch(function (error) {
                console.error(error);
            });
    });
</script>
@endsection
