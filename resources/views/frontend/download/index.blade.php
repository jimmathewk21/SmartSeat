<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = $settings['hero_title'] ?? 'Download';
        $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);
        $resolveAsset = function (?string $path): string {
            $path = trim((string) $path);
            if ($path === '') {
                return '';
            }
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//') || str_starts_with($path, '/')) {
                return $path;
            }
            return asset(ltrim($path, '/'));
        };
    @endphp
    <title>{{ $frontendSeoTitle ?? $pageTitle }}</title>
    <link rel="icon" href="{{ $siteIconHref }}" type="image/gif" sizes="16x16">
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" id="bootstrap">
    <link href="{{ asset('css/vendors.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet" type="text/css">
    <link id="colors" href="{{ asset('css/colors/scheme-01.css') }}" rel="stylesheet" type="text/css">
    <style>
        html, body { margin: 0 !important; padding: 0 !important; }
        #wrapper { display: flow-root; margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.download-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.download-page > footer { margin-top: 0 !important; }
        body.download-page {
            background: #fff;
            color: #111;
        }
        body.download-page .inside-page-hero-headline {
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
            background: none !important;
            text-shadow: none !important;
            opacity: 1 !important;
            margin-bottom: 10px;
            line-height: 1.1;
        }
        body.download-page .inside-page-hero-main {
            color: #000;
            font-size: 42px;
            font-weight: 700;
            line-height: 1;
        }
        body.download-page .inside-page-hero-headline small {
            color: #000;
            font-size: 0.6em;
            font-weight: 400;
        }
        body.download-page .inside-page-hero-description {
            font-size: 18px;
            line-height: 1.8;
            color: #111;
            margin-bottom: 12px;
            max-width: 1100px;
        }
        .download-world-section {
            position: relative;
            overflow: hidden;
            padding: 104px 0 72px;
        }
        .download-world-map {
            position: absolute;
            top: 140px;
            left: 50%;
            width: min(1411px, 100vw);
            aspect-ratio: 1411 / 702;
            height: auto;
            transform: translateX(-50%);
            background: url('{{ asset('images/worldmap.png') }}') no-repeat center 28px/contain;
            opacity: 0.40;
            pointer-events: none;
            z-index: 0;
        }
        .download-world-section > .container {
            position: relative;
            z-index: 1;
            max-width: 900px;
        }
        .download-copy {
            margin-top: 14px;
        }
        .download-copy p {
            margin: 0 0 12px;
            font-size: 16px;
            line-height: 1.6;
            color: #181818;
        }
        .download-copy h3 {
            margin: 0 0 6px;
            font-size: 18px;
            line-height: 1.45;
            color: #111;
            font-weight: 800;
        }
        .download-columns {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 28px;
            margin: 14px 0 8px;
        }
        .download-columns-content {
            font-size: 16px;
            line-height: 1.6;
            color: #202020;
        }
        .download-columns-content p {
            margin: 0 0 8px;
        }
        .download-columns-content ul {
            margin: 0;
            padding-left: 1.1rem;
        }
        .download-media-section {
            margin-top: 28px;
        }
        .download-media-section + .download-media-section {
            margin-top: 34px;
        }
        .download-media-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }
        .download-media-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #171717;
            line-height: 1.3;
        }
        .download-media-line {
            height: 1px;
            flex: 1 1 auto;
            background: #d8dcea;
        }
        .download-media-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px 22px;
        }
        .download-media-grid.is-triple {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .download-media-grid.is-single {
            grid-template-columns: minmax(0, 1fr);
        }
        .download-media-card {
            background: transparent;
            border: 0;
            padding: 0;
        }
        .download-media-card.is-logo .download-media-image {
            min-height: 98px;
            padding: 8px 10px;
            background: #fff;
            border: 1px solid #d7dbe5;
        }
        .download-logo-strip {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 0;
            border: 1px solid #d7dbe5;
            border-radius: 2px;
            overflow: hidden;
            background: #fff;
        }
        .download-logo-strip.is-dark {
            background: #080808;
            border-color: #242424;
        }
        .download-logo-strip-main {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            align-items: stretch;
        }
        .download-logo-strip-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 92px;
            padding: 10px 8px 7px;
            text-align: center;
        }
        .download-logo-strip-item + .download-logo-strip-item {
            border-left: 1px solid rgba(216, 220, 234, 0.65);
        }
        .download-logo-strip.is-dark .download-logo-strip-item + .download-logo-strip-item {
            border-left-color: rgba(255, 255, 255, 0.08);
        }
        .download-logo-strip.is-dark .download-logo-strip-item {
            color: #fff;
        }
        .download-logo-strip-item img {
            max-width: 100%;
            max-height: 56px;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
        }
        .download-logo-strip-item:nth-child(3) img {
            max-height: 42px;
        }
        .download-logo-strip-item span {
            display: block;
            margin-top: 6px;
            font-size: 13px;
            line-height: 1.2;
            color: inherit;
            font-weight: 600;
        }
        .download-logo-strip-actions {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 6px;
            padding: 12px 8px;
            background: rgba(124, 77, 255, 0.04);
        }
        .download-logo-strip.is-dark .download-logo-strip-actions {
            background: #101010;
        }
        .download-logo-strip-label {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            color: #606880;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 2px;
        }
        .download-logo-strip.is-dark .download-logo-strip-label {
            color: #cfd3df;
        }
        .download-logo-version-actions {
            display: flex;
            gap: 4px;
            flex-wrap: nowrap;
            justify-content: center;
            align-items: center;
            margin-top: 8px;
            width: 100%;
        }
        .download-logo-version-actions .download-media-btn {
            flex: 0 0 auto;
            min-width: 0;
            padding: 7px 10px;
        }
        .download-media-card.is-poster .download-media-image {
            min-height: 146px;
        }
        .download-media-card.is-wide .download-media-image {
            min-height: 228px;
        }
        .download-media-image {
            background: #fff;
            border: 1px solid #d8dbea;
            border-radius: 2px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .download-media-image img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: contain;
            background: #fff;
        }
        .download-media-card.is-poster .download-media-image img,
        .download-media-card.is-wide .download-media-image img {
            object-fit: cover;
        }
        .download-media-item-title {
            margin: 6px 0 0;
            font-size: 14px;
            font-weight: 700;
            color: #121212;
            text-align: center;
        }
        .download-media-card.is-poster .download-media-item-title,
        .download-media-card.is-wide .download-media-item-title {
            display: none;
        }
        .download-media-item-subtitle {
            display: none;
        }
        .download-media-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            margin-top: 8px;
        }
        .download-media-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-width: 64px;
            padding: 7px 12px;
            border-radius: 6px;
            background: linear-gradient(90deg, #7c4dff 0%, #6a44f2 100%);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            line-height: 1;
            text-transform: uppercase;
            text-decoration: none;
        }
        .download-media-btn i {
            font-size: 12px;
            line-height: 1;
        }
        .download-media-btn:hover,
        .download-media-btn:focus {
            color: #fff;
            text-decoration: none;
            opacity: .94;
        }
        @media (max-width: 991.98px) {
            .download-world-section {
                padding: 90px 0 48px;
            }
            .download-columns,
            .download-media-grid,
            .download-media-grid.is-triple,
            .download-media-grid.is-single {
                grid-template-columns: 1fr;
            }
            .download-logo-strip {
                grid-template-columns: 1fr;
            }
            .download-logo-strip-main {
                grid-template-columns: 1fr;
            }
            .download-logo-strip-actions {
                flex-direction: row;
                align-items: center;
                justify-content: center;
                flex-wrap: wrap;
            }
            .download-media-card.is-logo .download-media-image,
            .download-media-card.is-poster .download-media-image {
                min-height: 180px;
            }
            .download-media-card.is-wide .download-media-image {
                min-height: 220px;
            }
        }
        @media (max-width: 767.98px) {
            body.download-page .inside-page-hero-main {
                font-size: 34px;
            }
            body.download-page .inside-page-hero-description,
            .download-copy p,
            .download-columns-content {
                font-size: 16px;
            }
            .download-world-map {
                top: 160px;
                width: 118vw;
                aspect-ratio: 1411 / 702;
                height: auto;
                opacity: .18;
            }
            .download-media-title {
                font-size: 16px;
            }
        }
    </style>
</head>
<body class="download-page">
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="download-world-section">
        <div class="download-world-map" aria-hidden="true"></div>
        <div class="container">
            @include('frontend.partials.inner-page-hero', [
                'heroLabel' => $settings['hero_label'] ?? '',
                'heroTitle' => $settings['hero_title'] ?? '',
                'heroSuffix' => $settings['hero_title_suffix'] ?? '',
                'heroDescription' => $settings['hero_description'] ?? '',
                'heroButtons' => [],
            ])

            <div class="download-copy">
                @if(!empty($settings['intro_paragraph_1']))<p>{{ $settings['intro_paragraph_1'] }}</p>@endif
                @if(!empty($settings['intro_heading']))<h3>{{ $settings['intro_heading'] }}</h3>@endif
                @if(!empty($settings['intro_paragraph_2']))<p>{{ $settings['intro_paragraph_2'] }}</p>@endif
                @if(!empty($settings['intro_paragraph_3']))<p>{{ $settings['intro_paragraph_3'] }}</p>@endif

                <div class="download-columns">
                    <div>
                        @if(!empty($settings['left_column_heading']))<h3>{{ $settings['left_column_heading'] }}</h3>@endif
                        <div class="download-columns-content">{!! nl2br(e($settings['left_column_content'] ?? '')) !!}</div>
                    </div>
                    <div>
                        @if(!empty($settings['right_column_heading']))<h3>{{ $settings['right_column_heading'] }}</h3>@endif
                        <div class="download-columns-content">{!! nl2br(e($settings['right_column_content'] ?? '')) !!}</div>
                    </div>
                </div>
            </div>

            @foreach($items as $sectionTitle => $sectionItems)
                @php
                    $firstItem = $sectionItems->first();
                    $isGroupedLogoSection = $firstItem && $firstItem->is_grouped_versions;
                    $isLogoSection = $isGroupedLogoSection
                        || str_contains($sectionTitle, 'White Background')
                        || str_contains($sectionTitle, 'Dark Background')
                        || str_contains($sectionTitle, 'Logo');
                    $isWideSection = str_contains($sectionTitle, 'In General for PPT') || str_contains($sectionTitle, 'Floor Plan');
                    $itemClass = 'download-media-card';
                    $gridClass = 'download-media-grid';
                    $firstLogo = $isLogoSection ? $firstItem : null;
                    $groupedLogoVersions = collect();

                    if ($isLogoSection && $firstLogo && $firstLogo->is_grouped_versions) {
                        $groupedLogoVersions = collect([1, 2, 3])->map(function ($version) use ($firstLogo, $resolveAsset) {
                            $image = $resolveAsset($firstLogo->{"version_{$version}_image_url"} ?? '');
                            $pdf = $resolveAsset($firstLogo->{"version_{$version}_pdf_url"} ?? '');
                            $png = $resolveAsset($firstLogo->{"version_{$version}_png_url"} ?? '');
                            $jpg = $resolveAsset($firstLogo->{"version_{$version}_jpg_url"} ?? '');

                            return [
                                'label' => trim((string) ($firstLogo->{"version_{$version}_label"} ?? 'Version ' . $version)),
                                'image' => $image,
                                'pdf' => $pdf,
                                'png' => $png,
                                'jpg' => $jpg,
                            ];
                        })->filter(fn ($version) => $version['image'] !== '' || $version['pdf'] !== '' || $version['png'] !== '' || $version['jpg'] !== '')->values();
                    }

                    if ($isLogoSection) {
                        $gridClass .= ' is-triple';
                        $itemClass .= ' is-logo';
                    } elseif ($isWideSection) {
                        $gridClass .= ' is-single';
                        $itemClass .= ' is-wide';
                    } else {
                        $itemClass .= ' is-poster';
                    }
                @endphp
                <div class="download-media-section">
                    <div class="download-media-header">
                        <h2 class="download-media-title">{{ $sectionTitle }}</h2>
                        <div class="download-media-line"></div>
                    </div>

                    @if($isLogoSection)
                        <div class="download-logo-strip{{ str_contains($sectionTitle, 'Dark Background') ? ' is-dark' : '' }}">
                            <div class="download-logo-strip-main">
                                @if($groupedLogoVersions->isNotEmpty())
                                    @foreach($groupedLogoVersions as $version)
                                        <div class="download-logo-strip-item">
                                            @if($version['image'] !== '')
                                                <img src="{{ $version['image'] }}" alt="{{ $version['label'] }}">
                                            @endif
                                            <span>{{ $version['label'] }}</span>
                                            <div class="download-logo-version-actions">
                                                @if($version['pdf'] !== '')
                                                    <a href="{{ $version['pdf'] }}" class="download-media-btn" target="_blank" rel="noopener" download><i class="fas fa-download" aria-hidden="true"></i><span>PDF</span></a>
                                                @endif
                                                @if($version['png'] !== '')
                                                    <a href="{{ $version['png'] }}" class="download-media-btn" target="_blank" rel="noopener" download><i class="fas fa-download" aria-hidden="true"></i><span>PNG</span></a>
                                                @endif
                                                @if($version['jpg'] !== '')
                                                    <a href="{{ $version['jpg'] }}" class="download-media-btn" target="_blank" rel="noopener" download><i class="fas fa-download" aria-hidden="true"></i><span>JPG</span></a>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    @foreach($sectionItems as $item)
                                        @php
                                            $imageSrc = $resolveAsset($item->image_url);
                                            $primaryHref = $resolveAsset($item->primary_file_url);
                                            $secondaryHref = $resolveAsset($item->secondary_file_url);
                                        @endphp
                                        <div class="download-logo-strip-item">
                                            @if($imageSrc !== '')
                                                <img src="{{ $imageSrc }}" alt="{{ $item->item_title ?: $sectionTitle }}">
                                            @endif
                                            @if($item->item_title !== '')
                                                <span>{{ $item->item_title }}</span>
                                            @endif
                                            <div class="download-logo-version-actions">
                                                @if($primaryHref !== '' && $item->primary_button_text)
                                                    <a href="{{ $primaryHref }}" class="download-media-btn" target="_blank" rel="noopener" download><i class="fas fa-download" aria-hidden="true"></i><span>{{ $item->primary_button_text }}</span></a>
                                                @endif
                                                @if($secondaryHref !== '' && $item->secondary_button_text)
                                                    <a href="{{ $secondaryHref }}" class="download-media-btn" target="_blank" rel="noopener" download><i class="fas fa-download" aria-hidden="true"></i><span>{{ $item->secondary_button_text }}</span></a>
                                                @endif
                                                @if($imageSrc !== '')
                                                    <a href="{{ $imageSrc }}" class="download-media-btn" target="_blank" rel="noopener" download><i class="fas fa-download" aria-hidden="true"></i><span>JPG</span></a>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="{{ $gridClass }}">
                            @foreach($sectionItems as $item)
                                @php
                                    $imageSrc = $resolveAsset($item->image_url);
                                    $primaryHref = $resolveAsset($item->primary_file_url);
                                    $secondaryHref = $resolveAsset($item->secondary_file_url);
                                @endphp
                                <div class="{{ $itemClass }}">
                                    <div class="download-media-image">
                                        @if($imageSrc !== '')
                                            <img src="{{ $imageSrc }}" alt="{{ $item->item_title ?: $sectionTitle }}">
                                        @endif
                                    </div>
                                    @if($item->item_title !== '')
                                        <h3 class="download-media-item-title">{{ $item->item_title }}</h3>
                                    @endif
                                    <div class="download-media-actions">
                                        @if($primaryHref !== '' && $item->primary_button_text)
                                            <a href="{{ $primaryHref }}" class="download-media-btn" target="_blank" rel="noopener" download><i class="fas fa-download" aria-hidden="true"></i><span>{{ $item->primary_button_text }}</span></a>
                                        @endif
                                        @if($secondaryHref !== '' && $item->secondary_button_text)
                                            <a href="{{ $secondaryHref }}" class="download-media-btn" target="_blank" rel="noopener" download><i class="fas fa-download" aria-hidden="true"></i><span>{{ $item->secondary_button_text }}</span></a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
</div>

@include('frontend.partials.footer')

<script src="{{ asset('js/vendors.js') }}"></script>
<script src="{{ asset('js/designesia.js') }}"></script>
</body>
</html>
