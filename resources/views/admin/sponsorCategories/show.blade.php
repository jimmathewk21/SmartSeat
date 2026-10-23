@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.sponsorCategory.title_singular') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.sponsor-categories.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>{{ trans('cruds.sponsorCategory.fields.id') }}</th>
                        <td>{{ $sponsor_category->id }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('cruds.sponsorCategory.fields.name') }}</th>
                        <td>{{ $sponsor_category->name }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('cruds.sponsorCategory.fields.sort_order') }}</th>
                        <td>{{ $sponsor_category->sort_order }}</td>
                    </tr>
                    <tr>
                        <th>Logo Size</th>
                        <td>{{ ['sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large'][strtolower($sponsor_category->logo_size ?? 'md')] ?? 'Medium' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('cruds.sponsorCategory.fields.is_active') }}</th>
                        <td>{{ $sponsor_category->is_active ? trans('global.yes') : trans('global.no') }}</td>
                    </tr>
                </tbody>
            </table>

            <h5>{{ trans('global.relatedData') }}</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{{ trans('cruds.sponsor.fields.id') }}</th>
                            <th>{{ trans('cruds.sponsor.fields.name') }}</th>
                            <th>{{ trans('cruds.sponsor.fields.sort_order') }}</th>
                            <th>{{ trans('cruds.sponsor.fields.is_active') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sponsor_category->sponsors as $sponsor)
                            <tr>
                                <td>{{ $sponsor->id }}</td>
                                <td>{{ $sponsor->name }}</td>
                                <td>{{ $sponsor->sort_order }}</td>
                                <td>{{ $sponsor->is_active ? trans('global.yes') : trans('global.no') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">{{ trans('global.no_entries_in_table') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.sponsor-categories.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
