<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\FrontendHomeSettings;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class FrontendHomeController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $banner = FrontendHomeSettings::get();
        $events = Event::query()
            ->select(['id', 'title'])
            ->where('is_active', true)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('admin.frontend.home', compact('banner', 'events'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('setting_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'video_src' => ['required', 'string', 'max:255'],
            'video_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/ogg', 'max:51200'],
            'subtitle' => ['required', 'string', 'max:255'],
            'title_line_1' => ['required', 'string', 'max:255'],
            'title_line_2' => ['nullable', 'string', 'max:255'],
            'title_line_3' => ['nullable', 'string', 'max:255'],
            'event_date' => ['required', 'string', 'max:255'],
            'event_location' => ['required', 'string', 'max:255'],
            'event_date_icon' => ['required', 'string', 'max:100'],
            'event_location_icon' => ['required', 'string', 'max:100'],
            'about_subtitle' => ['required', 'string', 'max:255'],
            'about_title' => ['required', 'string', 'max:255'],
            'about_description' => ['required', 'string'],
            'about_point_1' => ['required', 'string', 'max:255'],
            'about_point_2' => ['required', 'string', 'max:255'],
            'about_point_3' => ['required', 'string', 'max:255'],
            'about_image' => ['required', 'string', 'max:255'],
            'about_image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'why_us_section_name' => ['required', 'string', 'max:100'],
            'why_us_title' => ['required', 'string', 'max:255'],
            'why_us_description' => ['required', 'string'],
            'why_us_card_1_image' => ['required', 'string', 'max:255'],
            'why_us_card_1_title' => ['required', 'string', 'max:255'],
            'why_us_card_1_text' => ['required', 'string'],
            'why_us_card_1_image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'why_us_card_2_image' => ['required', 'string', 'max:255'],
            'why_us_card_2_title' => ['required', 'string', 'max:255'],
            'why_us_card_2_text' => ['required', 'string'],
            'why_us_card_2_image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'why_us_card_3_image' => ['required', 'string', 'max:255'],
            'why_us_card_3_title' => ['required', 'string', 'max:255'],
            'why_us_card_3_text' => ['required', 'string'],
            'why_us_card_3_image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'why_us_card_4_image' => ['required', 'string', 'max:255'],
            'why_us_card_4_title' => ['required', 'string', 'max:255'],
            'why_us_card_4_text' => ['required', 'string'],
            'why_us_card_4_image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'countdown_title' => ['required', 'string', 'max:255'],
            'countdown_subtitle' => ['required', 'string', 'max:255'],
            'countdown_until' => ['required', 'date_format:Y-m-d\TH:i'],
            'countdown_location_line_1' => ['required', 'string', 'max:255'],
            'countdown_location_line_2' => ['required', 'string', 'max:255'],
            'primary_button_text' => ['required', 'string', 'max:100'],
            'primary_button_link' => ['required', 'string', 'max:255'],
            'secondary_button_text' => ['required', 'string', 'max:100'],
            'secondary_button_link' => ['required', 'string', 'max:255'],
            'counter_1_icon' => ['required', 'string', 'max:100'],
            'counter_1_number' => ['required', 'string', 'max:20'],
            'counter_1_text' => ['required', 'string', 'max:100'],
            'counter_2_icon' => ['required', 'string', 'max:100'],
            'counter_2_number' => ['required', 'string', 'max:20'],
            'counter_2_text' => ['required', 'string', 'max:100'],
            'counter_3_icon' => ['required', 'string', 'max:100'],
            'counter_3_number' => ['required', 'string', 'max:20'],
            'counter_3_text' => ['required', 'string', 'max:100'],
            'counter_4_icon' => ['required', 'string', 'max:100'],
            'counter_4_number' => ['required', 'string', 'max:20'],
            'counter_4_text' => ['required', 'string', 'max:100'],
            'agenda_subtitle' => ['required', 'string', 'max:255'],
            'agenda_title' => ['required', 'string', 'max:255'],
            'agenda_meta' => ['required', 'string', 'max:255'],
            'agenda_description_1' => ['required', 'string'],
            'agenda_description_2' => ['required', 'string'],
            'agenda_button_text' => ['required', 'string', 'max:100'],
            'agenda_button_link' => ['required', 'string', 'max:255'],
            'agenda_background_image' => ['required', 'string', 'max:255'],
            'agenda_background_image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'agenda_video_url' => ['required', 'url', 'max:255'],
            'speakers_subtitle' => ['required', 'string', 'max:255'],
            'speakers_title' => ['required', 'string', 'max:255'],
            'speakers_description' => ['nullable', 'string'],
            'speakers_view_all_text' => ['nullable', 'string', 'max:255'],
            'speakers_view_all_link' => ['nullable', 'string', 'max:255'],
            'speakers_become_text' => ['nullable', 'string', 'max:255'],
            'speakers_become_link' => ['nullable', 'string', 'max:255'],
            'brands_subtitle' => ['required', 'string', 'max:100'],
            'brands_title' => ['required', 'string', 'max:255'],
            'brands_button_text' => ['required', 'string', 'max:100'],
            'brands_button_link' => ['required', 'string', 'max:255'],
            'brands_view_all_text' => ['nullable', 'string', 'max:255'],
            'brands_view_all_link' => ['nullable', 'string', 'max:255'],
            'brand_logo_1' => ['required', 'string', 'max:255'],
            'brand_logo_2' => ['required', 'string', 'max:255'],
            'brand_logo_3' => ['required', 'string', 'max:255'],
            'brand_logo_4' => ['required', 'string', 'max:255'],
            'brand_logo_5' => ['required', 'string', 'max:255'],
            'brand_logo_6' => ['required', 'string', 'max:255'],
            'brand_logo_7' => ['required', 'string', 'max:255'],
            'brand_logo_8' => ['required', 'string', 'max:255'],
            'brand_logo_1_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'brand_logo_2_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'brand_logo_3_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'brand_logo_4_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'brand_logo_5_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'brand_logo_6_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'brand_logo_7_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'brand_logo_8_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'gallery_section_name' => ['required', 'string', 'max:255'],
            'gallery_main_title' => ['required', 'string', 'max:255'],
            'gallery_description' => ['required', 'string'],
            'gallery_images_list' => ['nullable', 'string'],
            'gallery_images_files' => ['nullable', 'array'],
            'gallery_images_files.*' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'gallery_view_more_text' => ['nullable', 'string', 'max:100'],
            'gallery_view_more_link' => ['nullable', 'string', 'max:255'],
            'faq_subtitle' => ['required', 'string', 'max:255'],
            'faq_title' => ['required', 'string', 'max:255'],
            'faq_view_more_text' => ['nullable', 'string', 'max:100'],
            'faq_view_more_link' => ['nullable', 'string', 'max:255'],
            'ticket_subtitle' => ['required', 'string', 'max:255'],
            'ticket_title' => ['required', 'string', 'max:255'],
            'ticket_description' => ['required', 'string'],
            'venue_subtitle' => ['required', 'string', 'max:255'],
            'venue_title' => ['required', 'string', 'max:255'],
            'venue_description' => ['required', 'string'],
            'venue_images_list' => ['nullable', 'string'],
            'venue_images_files' => ['nullable', 'array'],
            'venue_images_files.*' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'venue_view_more_text' => ['nullable', 'string', 'max:100'],
            'venue_view_more_link' => ['nullable', 'string', 'max:255'],
            'venue_address_heading' => ['required', 'string', 'max:100'],
            'venue_address_text' => ['required', 'string', 'max:255'],
            'venue_phone_heading' => ['required', 'string', 'max:100'],
            'venue_phone_text' => ['required', 'string', 'max:255'],
            'venue_email_heading' => ['required', 'string', 'max:100'],
            'venue_email_text' => ['required', 'string', 'max:255'],
            'map_heading' => ['required', 'string', 'max:255'],
            'map_title' => ['required', 'string', 'max:255'],
            'map_content' => ['required', 'string'],
            'map_iframe' => ['required', 'string'],
            'map_address_title' => ['required', 'string', 'max:255'],
            'map_address_line_1' => ['required', 'string', 'max:255'],
            'map_address_line_2' => ['required', 'string', 'max:255'],
            'map_tickets_title' => ['required', 'string', 'max:255'],
            'map_tickets_name' => ['required', 'string', 'max:255'],
            'map_tickets_phone' => ['required', 'string', 'max:255'],
            'map_tickets_email' => ['required', 'string', 'max:255'],
            'map_programme_title' => ['required', 'string', 'max:255'],
            'map_programme_name' => ['required', 'string', 'max:255'],
            'map_programme_phone' => ['required', 'string', 'max:255'],
            'map_programme_email' => ['required', 'string', 'max:255'],
            'things_subtitle' => ['required', 'string', 'max:255'],
            'things_title' => ['required', 'string', 'max:255'],
            'things_description' => ['nullable', 'string'],
            'things_button_text' => ['required', 'string', 'max:255'],
            'things_button_link' => ['required', 'string', 'max:255'],
            'newsletter_title' => ['required', 'string', 'max:255'],
            'newsletter_placeholder' => ['required', 'string', 'max:255'],
            'newsletter_button_text' => ['required', 'string', 'max:255'],
            'hide_banner_section' => ['nullable', 'boolean'],
            'hide_countdown_section' => ['nullable', 'boolean'],
            'hide_about_section' => ['nullable', 'boolean'],
            'hide_why_us_section' => ['nullable', 'boolean'],
            'testimonials_subtitle' => ['required', 'string', 'max:255'],
            'testimonials_title' => ['required', 'string', 'max:255'],
            'testimonials_button_text' => ['nullable', 'string', 'max:100'],
            'testimonials_button_link' => ['nullable', 'string', 'max:255'],
            'hide_testimonials_section' => ['nullable', 'boolean'],
            'speakers_subtitle' => ['required', 'string', 'max:255'],
            'speakers_title' => ['required', 'string', 'max:255'],
            'hide_speakers_section' => ['nullable', 'boolean'],
            'hide_counter_section' => ['nullable', 'boolean'],
            'hide_agenda_section' => ['nullable', 'boolean'],
            'hide_brands_section' => ['nullable', 'boolean'],
            'hide_gallery_section' => ['nullable', 'boolean'],
            'hide_faq_section' => ['nullable', 'boolean'],
            'hide_ticket_section' => ['nullable', 'boolean'],
            'hide_venue_section' => ['nullable', 'boolean'],
            'hide_map_section' => ['nullable', 'boolean'],
            'hide_things_section' => ['nullable', 'boolean'],
            'hide_newsletter_section' => ['nullable', 'boolean'],
        ]);

        $videoSrc = (string) $validated['video_src'];
        if ($request->hasFile('video_file')) {
            $file = $request->file('video_file');
            $directory = public_path('uploads/frontend-home');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($file->getClientOriginalExtension());
            $fileName = 'hero-video-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $file->move($directory, $fileName);

            $prefix = match ($extension) {
                'webm' => 'webm',
                'ogg', 'ogv' => 'ogv',
                default => 'mp4',
            };

            $videoSrc = $prefix . ':uploads/frontend-home/' . $fileName;
        }

        $aboutImage = (string) $validated['about_image'];
        if ($request->hasFile('about_image_file')) {
            $image = $request->file('about_image_file');
            $directory = public_path('uploads/frontend-home');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($image->getClientOriginalExtension());
            $fileName = 'about-image-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $image->move($directory, $fileName);
            $aboutImage = 'uploads/frontend-home/' . $fileName;
        }

        $whyUsImageKeys = [
            'why_us_card_1_image',
            'why_us_card_2_image',
            'why_us_card_3_image',
            'why_us_card_4_image',
        ];

        $whyUsImages = [];
        foreach ($whyUsImageKeys as $imageKey) {
            $whyUsImages[$imageKey] = (string) $validated[$imageKey];
            $fileKey = $imageKey . '_file';

            if ($request->hasFile($fileKey)) {
                $image = $request->file($fileKey);
                $directory = public_path('uploads/frontend-home');
                if (! is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }

                $extension = Str::lower($image->getClientOriginalExtension());
                $fileName = $imageKey . '-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
                $image->move($directory, $fileName);
                $whyUsImages[$imageKey] = 'uploads/frontend-home/' . $fileName;
            }
        }

        $agendaBackgroundImage = (string) $validated['agenda_background_image'];
        if ($request->hasFile('agenda_background_image_file')) {
            $image = $request->file('agenda_background_image_file');
            $directory = public_path('uploads/frontend-home');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($image->getClientOriginalExtension());
            $fileName = 'agenda-bg-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $image->move($directory, $fileName);
            $agendaBackgroundImage = 'uploads/frontend-home/' . $fileName;
        }

        $brandLogoKeys = [
            'brand_logo_1',
            'brand_logo_2',
            'brand_logo_3',
            'brand_logo_4',
            'brand_logo_5',
            'brand_logo_6',
            'brand_logo_7',
            'brand_logo_8',
        ];

        $brandLogos = [];
        foreach ($brandLogoKeys as $logoKey) {
            $brandLogos[$logoKey] = (string) $validated[$logoKey];
            $fileKey = $logoKey . '_file';

            if ($request->hasFile($fileKey)) {
                $image = $request->file($fileKey);
                $directory = public_path('uploads/frontend-home');
                if (! is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }

                $extension = Str::lower($image->getClientOriginalExtension());
                $fileName = $logoKey . '-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
                $image->move($directory, $fileName);
                $brandLogos[$logoKey] = 'uploads/frontend-home/' . $fileName;
            }
        }

        $galleryImages = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) ($validated['gallery_images_list'] ?? '')) as $galleryImage) {
            $galleryImage = trim((string) $galleryImage);
            if ($galleryImage !== '') {
                $galleryImages[] = $galleryImage;
            }
        }

        if ($request->hasFile('gallery_images_files')) {
            $directory = public_path('uploads/frontend-home');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            foreach ((array) $request->file('gallery_images_files') as $image) {
                if (! $image) {
                    continue;
                }

                $extension = Str::lower($image->getClientOriginalExtension());
                $fileName = 'gallery-image-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
                $image->move($directory, $fileName);
                $galleryImages[] = 'uploads/frontend-home/' . $fileName;
            }
        }

        $galleryImages = array_values(array_unique($galleryImages));

        if (empty($galleryImages)) {
            $existingSettings = FrontendHomeSettings::get();
            if (! empty($existingSettings['gallery_images']) && is_array($existingSettings['gallery_images'])) {
                foreach ($existingSettings['gallery_images'] as $imagePath) {
                    $imagePath = trim((string) $imagePath);
                    if ($imagePath !== '') {
                        $galleryImages[] = $imagePath;
                    }
                }
            } else {
                for ($i = 1; $i <= 6; $i++) {
                    $imagePath = trim((string) ($existingSettings['gallery_image_' . $i] ?? ('images/gallery/' . $i . '.webp')));
                    if ($imagePath !== '') {
                        $galleryImages[] = $imagePath;
                    }
                }
            }
        }

        $defaults = FrontendHomeSettings::defaults();
        $legacyGallery = [];
        for ($i = 1; $i <= 6; $i++) {
            $legacyGallery[$i] = $galleryImages[$i - 1] ?? ($defaults['gallery_image_' . $i] ?? ('images/gallery/' . $i . '.webp'));
        }

        $venueImages = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) ($validated['venue_images_list'] ?? '')) as $venueImage) {
            $venueImage = trim((string) $venueImage);
            if ($venueImage !== '') {
                $venueImages[] = $venueImage;
            }
        }

        if ($request->hasFile('venue_images_files')) {
            $directory = public_path('uploads/frontend-home');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            foreach ((array) $request->file('venue_images_files') as $image) {
                if (! $image) {
                    continue;
                }

                $extension = Str::lower($image->getClientOriginalExtension());
                $fileName = 'venue-image-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
                $image->move($directory, $fileName);
                $venueImages[] = 'uploads/frontend-home/' . $fileName;
            }
        }

        $venueImages = array_values(array_unique($venueImages));
        if (empty($venueImages)) {
            $existingSettings = FrontendHomeSettings::get();
            if (! empty($existingSettings['venue_images']) && is_array($existingSettings['venue_images'])) {
                foreach ($existingSettings['venue_images'] as $imagePath) {
                    $imagePath = trim((string) $imagePath);
                    if ($imagePath !== '') {
                        $venueImages[] = $imagePath;
                    }
                }
            } else {
                $legacyOne = trim((string) ($existingSettings['venue_image_1'] ?? 'images/misc/l1.webp'));
                $legacyTwo = trim((string) ($existingSettings['venue_image_2'] ?? 'images/misc/l2.webp'));
                if ($legacyOne !== '') {
                    $venueImages[] = $legacyOne;
                }
                if ($legacyTwo !== '') {
                    $venueImages[] = $legacyTwo;
                }
            }
        }

        $venueImageOne = $venueImages[0] ?? 'images/misc/l1.webp';
        $venueImageTwo = $venueImages[1] ?? ($venueImages[0] ?? 'images/misc/l2.webp');

        unset(
            $validated['video_file'],
            $validated['about_image_file'],
            $validated['why_us_card_1_image_file'],
            $validated['why_us_card_2_image_file'],
            $validated['why_us_card_3_image_file'],
            $validated['why_us_card_4_image_file'],
            $validated['agenda_background_image_file'],
            $validated['brand_logo_1_file'],
            $validated['brand_logo_2_file'],
            $validated['brand_logo_3_file'],
            $validated['brand_logo_4_file'],
            $validated['brand_logo_5_file'],
            $validated['brand_logo_6_file'],
            $validated['brand_logo_7_file'],
            $validated['brand_logo_8_file'],
            $validated['gallery_images_files'],
            $validated['gallery_images_list'],
            $validated['venue_images_files'],
            $validated['venue_images_list'],
            $validated['things_card_1_image_file'],
            $validated['things_card_2_image_file'],
            $validated['things_card_3_image_file'],
            $validated['things_card_4_image_file']
        );

        $validated['title'] = implode("\n", array_values(array_filter([
            trim((string) ($validated['title_line_1'] ?? '')),
            trim((string) ($validated['title_line_2'] ?? '')),
            trim((string) ($validated['title_line_3'] ?? '')),
        ], static fn ($value) => $value !== '')));

        FrontendHomeSettings::set(array_merge($validated, [
            'video_src' => $videoSrc,
            'about_image' => $aboutImage,
            'why_us_card_1_image' => $whyUsImages['why_us_card_1_image'],
            'why_us_card_2_image' => $whyUsImages['why_us_card_2_image'],
            'why_us_card_3_image' => $whyUsImages['why_us_card_3_image'],
            'why_us_card_4_image' => $whyUsImages['why_us_card_4_image'],
            'agenda_background_image' => $agendaBackgroundImage,
            'brand_logo_1' => $brandLogos['brand_logo_1'],
            'brand_logo_2' => $brandLogos['brand_logo_2'],
            'brand_logo_3' => $brandLogos['brand_logo_3'],
            'brand_logo_4' => $brandLogos['brand_logo_4'],
            'brand_logo_5' => $brandLogos['brand_logo_5'],
            'brand_logo_6' => $brandLogos['brand_logo_6'],
            'brand_logo_7' => $brandLogos['brand_logo_7'],
            'brand_logo_8' => $brandLogos['brand_logo_8'],
            'gallery_images' => $galleryImages,
            'gallery_image_1' => $legacyGallery[1],
            'gallery_image_2' => $legacyGallery[2],
            'gallery_image_3' => $legacyGallery[3],
            'gallery_image_4' => $legacyGallery[4],
            'gallery_image_5' => $legacyGallery[5],
            'gallery_image_6' => $legacyGallery[6],
            'venue_images' => $venueImages,
            'venue_image_1' => $venueImageOne,
            'venue_image_2' => $venueImageTwo,
        ]));

        return redirect()
            ->route('admin.frontend.home.edit')
            ->with('message', 'Frontend home banner updated successfully.');
    }
}
