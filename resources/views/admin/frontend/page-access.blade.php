@extends('layouts.admin')

@section('styles')
@parent
<style>
    .pa-table th, .pa-table td { vertical-align: middle; }
    .pa-role-head { text-align: center; white-space: nowrap; }
    .pa-role-head small { display: block; font-size: 10px; font-weight: 400; color: #888; }
    .pa-check-cell { text-align: center; }
    .pa-role-admin { background: #e8f4ff; font-weight: 600; font-size: 12px; color: #3758c8; text-align: center; }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        Page Access
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.frontend.page-access.update') }}">
            @csrf
            @method('PUT')

            <div class="alert alert-info mb-3">
                Control which pages each role can access on the frontend.
                <strong>Admin</strong> always has full access. <strong>Guests</strong> can access Login, Sign Up, and any pages enabled below.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-4 pa-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Page</th>
                            <th class="pa-role-head" style="width:110px;">
                                Admin
                                <small>All access</small>
                            </th>
                            <th class="pa-role-head" style="width:110px;">
                                Member
                                <small>All pages</small>
                            </th>
                            <th class="pa-role-head" style="width:110px;">
                                User
                                <small>Limited</small>
                            </th>
                            <th class="pa-role-head" style="width:110px;">
                                Guest
                                <small>Public access</small>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($definitions as $key => $definition)
                            @php
                                $pageSettings = $settings[$key] ?? ['user' => true, 'member' => true, 'guest' => false];
                            @endphp
                            <tr>
                                <td><strong>{{ $definition['label'] }}</strong></td>

                                {{-- Admin: always on --}}
                                <td class="pa-role-admin">
                                    <i class="fa fa-check-circle text-primary"></i> Always
                                </td>

                                {{-- Member --}}
                                <td class="pa-check-cell">
                                    <div class="form-check justify-content-center d-flex mb-0">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="pages[{{ $key }}][member]"
                                            id="member_{{ $key }}"
                                            value="1"
                                            {{ old("pages.{$key}.member", $pageSettings['member']) ? 'checked' : '' }}
                                        >
                                    </div>
                                </td>

                                {{-- User --}}
                                <td class="pa-check-cell">
                                    <div class="form-check justify-content-center d-flex mb-0">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="pages[{{ $key }}][user]"
                                            id="user_{{ $key }}"
                                            value="1"
                                            {{ old("pages.{$key}.user", $pageSettings['user']) ? 'checked' : '' }}
                                        >
                                    </div>
                                </td>

                                {{-- Guest --}}
                                <td class="pa-check-cell">
                                    <div class="form-check justify-content-center d-flex mb-0">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="pages[{{ $key }}][guest]"
                                            id="guest_{{ $key }}"
                                            value="1"
                                            {{ old("pages.{$key}.guest", $pageSettings['guest'] ?? false) ? 'checked' : '' }}
                                        >
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button class="btn btn-primary" type="submit">Save</button>
        </form>
    </div>
</div>
@endsection
