@php
    $logo = $frontendHeader['logo_url'] ?? 'images/logo.webp';
    $isAbsoluteLogo = str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://') || str_starts_with($logo, '//') || str_starts_with($logo, '/');
    $logoSrc = $isAbsoluteLogo ? $logo : asset($logo);
    $menuItems = collect($frontendHeader['menu_items'] ?? []);
    $venuePath = '/venue';
    $hasVenueItem = $menuItems->contains(function ($item) use ($venuePath) {
        $link = trim((string) ($item['link'] ?? ''));

        return $link === $venuePath || $link === route('frontend.venue.index');
    });

    if (! $hasVenueItem) {
        $menuItems->push([
            'name' => 'Venue',
            'link' => $venuePath,
        ]);
    }

    $hasTrainItem = $menuItems->contains(function ($item) {
        $link = trim((string) ($item['link'] ?? ''));

        return $link === '/trains' || $link === route('frontend.trains.search');
    });

    if (! $hasTrainItem) {
        $menuItems->push([
            'name' => 'Train Booking',
            'link' => route('frontend.trains.search'),
        ]);
    }

    if (auth()->check() && ! $menuItems->contains(fn ($item) => trim((string) ($item['link'] ?? '')) === route('frontend.trains.bookings'))) {
        $menuItems->push([
            'name' => 'My Train Bookings',
            'link' => route('frontend.trains.bookings'),
        ]);
    }

    $menuItems = $menuItems->values()->all();
    $buttonText = $frontendHeader['button_text'] ?? 'Buy Tickets';
    $buttonLink = $frontendHeader['button_link'] ?? '#section-tickets';
    $currentPath = request()->path();
    $isHomeRoute = request()->routeIs('frontend.home') || $currentPath === '/' || $currentPath === 'index.html';
    $headerInlineStyle = $isHomeRoute ? '' : 'background:#000 !important;';
@endphp

<header class="transparent" style="{{ $headerInlineStyle }}">
    <style>
        .header-right-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            white-space: nowrap;
            justify-content: flex-end;
        }
        .header-auth-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .header-auth-form {
            margin: 0;
        }
        .header-auth-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 42px;
            color: #fff;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: .35px;
            text-transform: uppercase;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 8px;
            line-height: 1;
            white-space: nowrap;
            transition: all .2s ease;
        }
        .header-auth-btn-login {
            border: 1px solid rgba(255, 255, 255, 0.45);
            background: rgba(255, 255, 255, 0.02);
        }
        .header-auth-btn-login:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.7);
        }
        .header-auth-btn-signup {
            border: 1px solid rgba(111, 66, 245, 0.95);
            background: linear-gradient(90deg, #6f42f5 0%, #7c55f6 100%);
            box-shadow: 0 8px 20px rgba(111, 66, 245, 0.25);
        }
        .header-auth-btn-signup:hover {
            color: #fff;
            background: linear-gradient(90deg, #7b54f6 0%, #8a67f7 100%);
            border-color: rgba(138, 103, 247, 1);
        }
        .header-user-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #fff;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: .25px;
            text-transform: uppercase;
            text-decoration: none;
            max-width: 180px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 8px;
            height: 42px;
            padding: 10px 14px;
            background: rgba(255, 255, 255, 0.04);
            box-sizing: border-box;
        }
        .header-user-chip:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.12);
        }
        .header-user-chip i {
            font-size: 14px;
        }
        .header-translate-wrap {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 8px;
            background: rgba(255, 255, 255, 1);
            padding: 0;
            box-sizing: border-box;
            overflow: hidden;
        }
        .header-translate-wrap .goog-te-gadget {
            color: #fff !important;
            font-size: 0 !important;
            line-height: 1 !important;
            font-family: inherit !important;
            white-space: nowrap;
        }
        .header-translate-wrap .goog-te-gadget span,
        .header-translate-wrap .goog-te-gadget .goog-logo-link {
            display: none !important;
            color: #fff !important;
        }
        .header-translate-wrap .goog-te-gadget .goog-te-combo {
            -webkit-appearance: none;
            appearance: none;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: transparent;
            text-indent: -9999px;
            width: 34px;
            height: 34px;
            min-height: 34px;
            max-width: 34px;
            padding: 0;
            font-size: 0;
            font-weight: 700;
            outline: none;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='22' height='22' viewBox='0 0 24 24' fill='none'%3E%3Crect x='1.5' y='1.5' width='9' height='9' rx='1.8' fill='%237fb3ff' stroke='%230d3f9e' stroke-width='1.5'/%3E%3Crect x='13.5' y='13.5' width='9' height='9' rx='1.8' fill='%23ffffff' stroke='%230d3f9e' stroke-width='1.5'/%3E%3Cpath d='M4.2 5.2h4.2M6.3 4v5.2M4.2 5.2l4.2 4.2M8.4 5.2L4.2 9.4' stroke='%230d3f9e' stroke-width='1.05' stroke-linecap='round' stroke-linejoin='round'/%3E%3Cpath d='M17.2 19.5l1.3-3.6 1.3 3.6M17.7 18.1h1.6' stroke='%230d3f9e' stroke-width='1.15' stroke-linecap='round' stroke-linejoin='round'/%3E%3Cpath d='M12.8 6.3c2.4 0 3.8 1.2 4.5 3.1M17.3 9.4l-.9 1.2M17.3 9.4l1.4.1' stroke='%230d3f9e' stroke-width='1.15' stroke-linecap='round' stroke-linejoin='round'/%3E%3Cpath d='M11.2 17.7c-2.4 0-3.8-1.2-4.5-3.1M6.7 14.6l.9-1.2M6.7 14.6l-1.4-.1' stroke='%230d3f9e' stroke-width='1.15' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
            background-size: 24px 24px;
        }
        .header-translate-wrap .goog-te-gadget .goog-te-combo option {
            color: #111;
            text-indent: 0;
            font-size: 13px;
        }
        .header-translate-wrap .goog-te-gadget-simple {
            background: transparent !important;
            border: 0 !important;
            padding: 0 !important;
        }
        .header-translate-wrap .goog-te-gadget-icon {
            display: none !important;
        }
        .header-container-wide {
            width: 100%;
            max-width: 1680px;
        }
        @media (min-width: 992px) {
            .de-flex {
                align-items: center;
                gap: 12px;
                flex-wrap: nowrap;
            }
            .de-flex > .de-flex-col:nth-child(2) {
                flex: 1 1 auto;
                min-width: 0;
            }
            #mainmenu {
                display: flex;
                align-items: center;
                justify-content: center;
                flex-wrap: nowrap;
                white-space: nowrap;
            }
            #mainmenu > li {
                float: none;
                display: inline-flex;
                margin-right: 16px;
            }
            #mainmenu > li:last-child {
                margin-right: 0;
            }
            #mainmenu > li > a {
                padding-top: 18px;
                padding-bottom: 18px;
            }
        }
        @media (min-width: 768px) and (max-width: 1280px) {
            .de-flex {
                gap: 10px;
            }
            .de-flex > .de-flex-col:nth-child(2) {
                flex: 1 1 auto;
                min-width: 0;
            }
            #logo .logo-main,
            #logo .logo-scroll {
                max-height: 34px;
                width: auto;
            }
            #mainmenu {
                display: flex;
                align-items: center;
                flex-wrap: nowrap;
                white-space: nowrap;
            }
            #mainmenu > li {
                float: none;
                display: inline-flex;
                margin-right: 14px;
            }
            #mainmenu > li:last-child {
                margin-right: 0;
            }
            #mainmenu > li > a {
                font-size: 13px;
                padding-top: 18px;
                padding-bottom: 18px;
            }
            .header-right-actions {
                gap: 6px;
            }
            .header-auth-actions {
                gap: 6px;
            }
            .header-auth-btn,
            .header-user-chip {
                height: 38px;
                font-size: 10px;
                padding: 8px 10px;
            }
            .header-translate-wrap {
                width: 38px;
                height: 38px;
                padding: 0;
            }
            .header-translate-wrap .goog-te-gadget .goog-te-combo {
                width: 30px;
                height: 30px;
                min-height: 30px;
                max-width: 30px;
                background-size: 22px 22px;
            }
            .header-right-actions .btn-main {
                padding: 10px 14px;
                font-size: 10px;
            }
        }
        @media (max-width: 991px) {
            #wrapper > section:first-of-type {
                padding-top: 90px !important;
            }
            .header-right-actions {
                gap: 8px;
                max-width: none;
                flex-wrap: nowrap;
                justify-content: flex-end;
            }
            .header-auth-actions {
                gap: 6px;
                flex-wrap: nowrap;
                justify-content: flex-end;
            }
            .header-auth-btn,
            .header-user-chip {
                font-size: 11px;
                padding: 8px 10px;
            }
            .header-translate-wrap {
                width: 38px;
                height: 38px;
                padding: 0;
            }
            .header-translate-wrap .goog-te-gadget .goog-te-combo {
                width: 30px;
                height: 30px;
                min-height: 30px;
                max-width: 30px;
                background-size: 22px 22px;
            }
            .header-user-chip {
                max-width: none;
            }
            .header-user-chip span {
                display: none;
            }
            .header-right-actions .btn-main {
                display: none;
            }
        }
        @media (max-width: 767px) {
            .header-right-actions {
                gap: 6px;
            }
            .header-auth-actions {
                width: auto;
            }
            .header-auth-btn,
            .header-user-chip,
            .header-right-actions .btn-main {
                min-height: 38px;
                font-size: 10px;
                padding: 8px 10px;
            }
            .header-translate-wrap {
                width: 36px;
                height: 36px;
                padding: 0;
            }
            .header-translate-wrap .goog-te-gadget .goog-te-combo {
                width: 28px;
                height: 28px;
                min-height: 28px;
                max-width: 28px;
                background-size: 20px 20px;
            }
        }
    </style>
    <div class="container header-container-wide">
        <div class="row">
            <div class="col-md-12">
                <div class="de-flex">
                    <div class="de-flex-col">
                        <div id="logo">
                            <a href="{{ route('frontend.home') }}">
                                <img class="logo-main" src="{{ $logoSrc }}" alt="">
                                <img class="logo-scroll" src="{{ $logoSrc }}" alt="">
                                <img class="logo-mobile" src="{{ $logoSrc }}" alt="">
                            </a>
                        </div>
                    </div>

                    <div class="de-flex-col">
                        <div class="de-flex-col header-col-mid">
                            <ul id="mainmenu">
                                @foreach($menuItems as $item)
                                    <li>
                                        @php($menuLink = trim((string) ($item['link'] ?? '')) === '/' ? route('frontend.home') : $item['link'])
                                        <a class="menu-item" href="{{ $menuLink }}">{{ $item['name'] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="de-flex-col">
                        <div class="header-right-actions">
                            <div class="header-translate-wrap" aria-label="Website language switcher">
                                <div id="google_translate_element"></div>
                            </div>
                            @guest
                                <div class="header-auth-actions">
                                    @if (Route::has('register'))
                                        <a class="header-auth-btn header-auth-btn-signup" href="{{ route('register') }}">Sign Up</a>
                                    @endif
                                </div>
                            @else
                                <div class="header-auth-actions">
                                    <a class="header-user-chip" href="{{ route('profile.password.edit') }}" title="{{ auth()->user()->name }}">
                                        <i class="fas fa-user-circle"></i>
                                        <span>{{ auth()->user()->name }}</span>
                                    </a>
                                    <form class="header-auth-form" method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="header-auth-btn header-auth-btn-login" type="submit">Sign Out</button>
                                    </form>
                                </div>
                            @endguest

                            <a class="btn-main fx-slide" href="{{ $buttonLink }}"><span>{{ $buttonText }}</span></a>
                        </div>

                        <div class="menu_side_area">
                            <span id="menu-btn"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
