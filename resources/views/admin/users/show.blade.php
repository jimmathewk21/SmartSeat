@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            {{ trans('global.show') }} {{ trans('cruds.user.title') }}
        </div>

        <div class="card-body">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.users.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr>
                            <th>{{ trans('cruds.user.fields.id') }}</th>
                            <td>{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('cruds.user.fields.name') }}</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('cruds.user.fields.mobile') }}</th>
                            <td>{{ $user->mobile }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('cruds.user.fields.email') }}</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                    </tbody>
                </table>
                <a class="btn btn-default" href="{{ route('admin.users.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>


@endsection
