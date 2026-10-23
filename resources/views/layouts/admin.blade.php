<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ trans('panel.site_title') }}</title>
    @php
        $adminMeta = \App\Support\SiteMetaSettings::get();
        $adminIcon = !empty($adminMeta['admin_site_icon']) ? $adminMeta['admin_site_icon'] : ($adminMeta['site_icon'] ?? 'images/icon.webp');
    @endphp
    <link rel="icon" href="{{ asset($adminIcon) }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet" />
    <link href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" rel="stylesheet" />
    <link href="https://unpkg.com/@coreui/coreui@3.2/dist/css/coreui.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jquery.perfect-scrollbar/1.5.0/css/perfect-scrollbar.min.css" rel="stylesheet" />
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet" />
    @yield('styles')
</head>

<body class="c-app app-modern">
    @include('partials.menu')
    <div class="c-wrapper">
        <header class="c-header c-header-fixed px-3">
            <button class="c-header-toggler c-class-toggler d-lg-none mfe-auto" type="button" data-target="#sidebar" data-class="c-sidebar-show">
                <i class="fas fa-fw fa-bars"></i>
            </button>

            <a class="c-header-brand d-lg-none" href="{{ route('admin.home') }}">IGI SUMMIT</a>

            <button class="c-header-toggler mfs-3 d-md-down-none" type="button" responsive="true">
                <i class="fas fa-fw fa-bars"></i>
            </button>

            <ul class="c-header-nav ml-auto align-items-center">
                @if(count(config('panel.available_languages', [])) > 1)
                    <li class="c-header-nav-item dropdown d-md-down-none">
                        <a class="c-header-nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                            {{ strtoupper(app()->getLocale()) }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            @foreach(config('panel.available_languages') as $langLocale => $langName)
                                <a class="dropdown-item" href="{{ url()->current() }}?change_language={{ $langLocale }}">{{ strtoupper($langLocale) }} ({{ $langName }})</a>
                            @endforeach
                        </div>
                    </li>
                @endif
                @auth
                    <li class="c-header-nav-item dropdown">
                        <a class="c-header-nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                            <span class="user-chip">
                                <span class="user-chip__avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                                <span class="d-none d-md-inline">{{ auth()->user()->name ?? 'User' }}</span>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            @can('profile_password_edit')
                                <a class="dropdown-item" href="{{ route('profile.password.edit') }}">
                                    <i class="fas fa-key mr-2"></i>
                                    Change Password
                                </a>
                            @endcan
                            <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logoutform').submit();">
                                <i class="fas fa-sign-out-alt mr-2"></i>
                                {{ trans('global.logout') }}
                            </a>
                        </div>
                    </li>
                @endauth
            </ul>
        </header>

        <div class="c-body">
            <main class="c-main">


                <div class="container-fluid">
                    @if(session('message'))
                        <div class="row mb-2">
                            <div class="col-lg-12">
                                <div class="alert alert-success" role="alert">{{ session('message') }}</div>
                            </div>
                        </div>
                    @endif
                    @if($errors->count() > 0)
                        <div class="alert alert-danger">
                            <ul class="list-unstyled">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="content-stack">
                        @yield('content')
                    </div>

                </div>


            </main>
            <form id="logoutform" action="{{ route('logout') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.perfect-scrollbar/1.5.0/perfect-scrollbar.min.js"></script>
    <script src="https://unpkg.com/@coreui/coreui@3.2/dist/js/coreui.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/16.0.0/classic/ckeditor.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.js"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script>
        (function () {
            function getRowCheckboxes(form) {
                return Array.from(form.querySelectorAll('input[type="checkbox"][name="ids[]"]'));
            }

            function setCheckedState(form, checked) {
                getRowCheckboxes(form).forEach(function (box) {
                    box.checked = checked;
                });

                form.querySelectorAll('input[type="checkbox"][id^="select-all-"], #select-all-faqs').forEach(function (master) {
                    master.checked = checked;
                });
            }

            document.querySelectorAll('form').forEach(function (form) {
                var hasDeleteMethod = !!form.querySelector('input[name="_method"][value="DELETE"]');
                var hasBulkDeleteAction = typeof form.getAttribute('action') === 'string'
                    && form.getAttribute('action').indexOf('bulk-delete') !== -1;

                if (!hasDeleteMethod && !hasBulkDeleteAction) {
                    return;
                }

                var checkboxes = getRowCheckboxes(form);
                if (checkboxes.length === 0) {
                    return;
                }

                var actionsWrap = form.querySelector('.table-actions');
                if (!actionsWrap || actionsWrap.querySelector('[data-bulk-select-all]')) {
                    return;
                }

                var selectAllBtn = document.createElement('button');
                selectAllBtn.type = 'button';
                selectAllBtn.className = 'btn btn-outline-secondary ml-2';
                selectAllBtn.setAttribute('data-bulk-select-all', '1');
                selectAllBtn.textContent = 'Select All';

                var clearBtn = document.createElement('button');
                clearBtn.type = 'button';
                clearBtn.className = 'btn btn-outline-secondary ml-2';
                clearBtn.setAttribute('data-bulk-clear', '1');
                clearBtn.textContent = 'Clear';

                selectAllBtn.addEventListener('click', function () {
                    setCheckedState(form, true);
                });

                clearBtn.addEventListener('click', function () {
                    setCheckedState(form, false);
                });

                actionsWrap.appendChild(selectAllBtn);
                actionsWrap.appendChild(clearBtn);
            });
        })();
    </script>
    @yield('scripts')
</body>

</html>
