@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.sponsor.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.sponsors.update', [$sponsor->id]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.sponsors._form', ['sponsor' => $sponsor])

            <div class="form-group">
                <button class="btn btn-success" type="submit">{{ trans('global.update') }}</button>
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
