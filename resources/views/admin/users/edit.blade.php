@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            {{ trans('global.edit') }} {{ trans('cruds.user.title_singular') }}
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update', [$user->id]) }}" enctype="multipart/form-data">
                @method('PUT')
                @csrf

                <div class="form-group">
                    <label class="required" for="name">{{ trans('cruds.user.fields.name') }}</label>
                    <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name"
                        id="name" value="{{ old('name', $user->name) }}" required>
                    @if ($errors->has('name'))
                        <div class="invalid-feedback">
                            {{ $errors->first('name') }}
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    <label for="mobile">{{ trans('cruds.user.fields.mobile') }}</label>
                    <input class="form-control {{ $errors->has('mobile') ? 'is-invalid' : '' }}" type="text"
                        name="mobile" id="mobile" value="{{ old('mobile', $user->mobile) }}">
                    @if ($errors->has('mobile'))
                        <div class="invalid-feedback">
                            {{ $errors->first('mobile') }}
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    <label for="company_name">Company Name</label>
                    <input class="form-control {{ $errors->has('company_name') ? 'is-invalid' : '' }}" type="text"
                        name="company_name" id="company_name" value="{{ old('company_name', $user->company_name) }}">
                    @if ($errors->has('company_name'))
                        <div class="invalid-feedback">
                            {{ $errors->first('company_name') }}
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="required" for="email">{{ trans('cruds.user.fields.email') }}</label>
                    <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" type="email"
                        name="email" id="email" value="{{ old('email', $user->email) }}" required>
                    @if ($errors->has('email'))
                        <div class="invalid-feedback">
                            {{ $errors->first('email') }}
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    <label for="password">{{ trans('cruds.user.fields.password') }}</label>
                    <input class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" type="password"
                        name="password" id="password">
                    @if ($errors->has('password'))
                        <div class="invalid-feedback">
                            {{ $errors->first('password') }}
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    <label for="roles">{{ trans('cruds.user.fields.roles') }}</label>
                    <div style="padding-bottom: 4px">
                        <span class="btn btn-info btn-xs select-all"
                            style="border-radius: 0">{{ trans('global.select_all') }}</span>
                        <span class="btn btn-info btn-xs deselect-all"
                            style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                    </div>
                    <select class="form-control select2 {{ $errors->has('roles') ? 'is-invalid' : '' }}" name="roles[]"
                        id="roles" multiple>
                        @foreach ($roles as $id => $role)
                            <option value="{{ $id }}"
                                {{ in_array($id, old('roles', [])) || $user->roles->contains($id) ? 'selected' : '' }}>
                                {{ $role }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('roles'))
                        <div class="invalid-feedback">
                            {{ $errors->first('roles') }}
                        </div>
                    @endif
                </div>

                <hr>
                <h5>Train owner access</h5>
                <p class="text-muted">An administrator must assign the Train Owner role, approve access, and assign specific trains. Passenger and event approval are separate.</p>
                <div class="form-group">
                    <label for="approve_train_owner">Owner approval</label>
                    <select class="form-control" name="approve_train_owner" id="approve_train_owner">
                        <option value="0">Not approved</option>
                        <option value="1" @selected((string) old('approve_train_owner', $user->train_owner_approved_at ? '1' : '0') === '1')>Approved</option>
                    </select>
                    @if($errors->has('approve_train_owner'))<div class="invalid-feedback d-block">{{ $errors->first('approve_train_owner') }}</div>@endif
                </div>
                <div class="form-group">
                    <label for="train_ids">Trains this owner may manage</label>
                    <select class="form-control select2" name="train_ids[]" id="train_ids" multiple>
                        @foreach($trains as $train)
                            <option value="{{ $train->id }}" @selected(in_array($train->id, old('train_ids', $trains->where('owner_user_id', $user->id)->pluck('id')->all())))>
                                {{ $train->code }} — {{ $train->name }}@if($train->owner_user_id && $train->owner_user_id !== $user->id) (currently assigned to another owner)@endif
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Selecting a train transfers its management assignment to this account.</small>
                    @if($errors->has('train_ids'))<div class="invalid-feedback d-block">{{ $errors->first('train_ids') }}</div>@endif
                </div>

                <div class="form-group">
                    <button class="btn btn-danger" type="submit">
                        {{ trans('global.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
