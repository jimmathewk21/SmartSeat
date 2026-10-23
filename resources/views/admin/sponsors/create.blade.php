@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.sponsor.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.sponsors.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.sponsors._form')

            <div class="form-group">
                <button class="btn btn-success" type="submit">{{ trans('global.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
    @parent
    <script>
        (function () {
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
        })();
    </script>
@endsection
