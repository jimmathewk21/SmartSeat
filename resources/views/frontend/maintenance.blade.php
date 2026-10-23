<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = 'Website Maintenance';
        $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);

        $maintenanceSettings = $maintenanceSettings ?? \App\Support\FrontendMaintenanceSettings::get();

        $resolveAssetHref = function (?string $path, string $fallback = ''): string {
            $path = trim((string) $path);
            if ($path === '') {
                $path = $fallback;
            }

            if ($path === '') {
                return '';
            }

            return str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//') || str_starts_with($path, '/')
                ? $path
                : asset(ltrim($path, '/'));
        };

        $backgroundHref = $resolveAssetHref($maintenanceSettings['background_image'] ?? '', 'images/background/8.webp');
        $badgeIconRaw = trim((string) ($maintenanceSettings['badge_icon'] ?? ''));
        if ($badgeIconRaw !== '' && !str_starts_with($badgeIconRaw, 'http') && !str_starts_with($badgeIconRaw, '//') && !file_exists(public_path(ltrim($badgeIconRaw, '/')))) {
            $badgeIconRaw = '';
        }
        $badgeIconHref = $resolveAssetHref($badgeIconRaw, $siteIcon) ?: asset('images/icon.webp');
        $title = trim((string) ($maintenanceSettings['title'] ?? '')) ?: 'Website Under Maintenance';
        $headline = trim((string) ($maintenanceSettings['headline'] ?? ''));
        $thanksText = trim((string) ($maintenanceSettings['thanks_text'] ?? 'Thank you for your patience.'));
        $inquiryPrefix = trim((string) ($maintenanceSettings['inquiry_prefix'] ?? 'For Inquiries, please email -'));
        $inquiryEmail = trim((string) ($maintenanceSettings['inquiry_email'] ?? ''));

    @endphp
    <title>{{ $frontendSeoTitle ?? $pageTitle }}</title>
    <link rel="icon" href="{{ $siteIconHref }}" type="image/gif" sizes="16x16">
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" id="bootstrap">
    <style>
        :root {
            --maint-shell-bg: #081033;
            --maint-panel-bg: #181d62;
            --maint-card-bg: rgba(255, 255, 255, 0.96);
            --maint-text: #111827;
            --maint-shadow: 0 24px 80px rgba(7, 18, 52, 0.28);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Poppins", "Segoe UI", sans-serif;
            color: var(--maint-text);
            background: var(--maint-shell-bg);
        }

        .maintenance-page {
            position: relative;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px 18px;
            overflow: hidden;
        }

        .maintenance-page::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(180deg, rgba(7, 12, 40, 0.12), rgba(7, 12, 40, 0.32)),
                url("{{ $backgroundHref }}") center center / cover no-repeat;
            transform: scale(1.02);
        }

        .maintenance-shell {
            position: relative;
            z-index: 1;
            width: min(760px, 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
        }

        .maintenance-card {
            width: 100%;
            background: var(--maint-card-bg);
            border-radius: 22px;
            box-shadow: var(--maint-shadow);
            padding: 20px 24px 18px;
            text-align: center;
        }

        .maintenance-logo-wrap {
            min-height: 54px;
            display: grid;
            place-items: center;
            margin-bottom: 10px;
        }

        .maintenance-brand {
            color: #0f5137;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .maintenance-message-panel {
            position: relative;
            margin: 0 auto;
            background: #181d62 !important;
            border-radius: 14px;
            padding: 28px 26px 52px;
            color: #fff;
        }

        .maintenance-title {
            margin: 0 0 14px;
            font-size: clamp(15px, 1.8vw, 20px);
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #ffffff !important;
        }

        .maintenance-message {
            margin: 0;
            font-size: clamp(26px, 1.5vw, 46px);
            font-weight: 500;
            line-height: 1.08;
            letter-spacing: -0.03em;
            white-space: pre-line;
            color: #ffffff !important;
        }

        .maintenance-message-panel *,
        .maintenance-message-panel h1,
        .maintenance-message-panel p {
            color: #ffffff !important;
        }

        .maintenance-badge {
            position: absolute;
            left: 50%;
            bottom: -30px;
            transform: translateX(-50%);
            width: 84px;
            height: 84px;
            border-radius: 18px;
            background: #fff;
            display: grid;
            place-items: center;
            overflow: hidden;
            box-shadow: 0 16px 34px rgba(13, 19, 70, 0.2);
        }

        .maintenance-badge img {
            width: 68%;
            height: 68%;
            object-fit: contain;
        }

        .maintenance-thanks {
            margin: 42px 0 2px;
            font-size: clamp(24px, 2.2vw, 36px);
            line-height: 1.15;
            font-weight: 500;
            color: #1b2338;
        }

        .maintenance-contact {
            width: min(610px, 100%);
            background: rgba(14, 23, 74, 0.94);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 16px 38px rgba(7, 14, 45, 0.36);
            border-radius: 14px;
            padding: 14px 22px;
            text-align: center;
            color: #fff;
            font-size: clamp(18px, 1.8vw, 24px);
            line-height: 1.2;
        }

        .maintenance-contact a {
            color: #fff;
            text-decoration: none;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.48);
        }

        .maintenance-contact a:hover {
            border-bottom-color: #fff;
        }

        @media (max-width: 767.98px) {
            .maintenance-page {
                padding: 20px 14px;
            }

            .maintenance-card {
                border-radius: 18px;
                padding: 14px 14px 16px;
            }

            .maintenance-logo-wrap {
                min-height: 40px;
                margin-bottom: 8px;
            }

            .maintenance-message-panel {
                border-radius: 12px;
                padding: 18px 16px 42px;
            }

            .maintenance-badge {
                width: 72px;
                height: 72px;
                border-radius: 16px;
                bottom: -26px;
            }

            .maintenance-thanks {
                margin-top: 34px;
            }

            .maintenance-contact {
                padding: 12px 14px;
                border-radius: 12px;
            }
        }
    </style>
</head>
<body>
    <main class="maintenance-page">
        <section class="maintenance-shell" role="status" aria-live="polite">
            <article class="maintenance-card">
                <div class="maintenance-logo-wrap">
                    <span class="maintenance-brand">SmartSeat</span>
                </div>

                <div class="maintenance-message-panel">
                    @if($title !== '')
                        <p class="maintenance-title" style="color: #ffffff !important;">{{ $title }}</p>
                    @endif
                    <h1 class="maintenance-message" style="color: #ffffff !important;margin-bottom: 18px;">{{ $headline }}</h1>
                    <div class="maintenance-badge" aria-hidden="true">
                        <img src="{{ $badgeIconHref }}" alt="" onerror="this.onerror=null;this.src='{{ asset('images/icon.webp') }}'">
                    </div>
                </div>

                <p class="maintenance-thanks">{{ $thanksText }}</p>
            </article>

            @if($inquiryEmail !== '')
                <div class="maintenance-contact">
                    {{ $inquiryPrefix }}
                    <a href="mailto:{{ $inquiryEmail }}">{{ $inquiryEmail }}</a>
                </div>
            @endif
        </section>
    </main>
</body>
</html>
