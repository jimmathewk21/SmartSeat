<?php

namespace Tests\Feature\Api;

use App\Models\DownloadMediaItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ContentApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_downloads_api_returns_grouped_sections(): void
    {
        DownloadMediaItem::create([
            'section_title' => 'Logo White Background',
            'item_title' => 'Summit Logo',
            'item_subtitle' => 'Primary logo pack',
            'image_url' => 'uploads/downloads/logo.png',
            'primary_button_text' => 'PDF',
            'primary_file_url' => 'uploads/downloads/logo.pdf',
            'secondary_button_text' => 'PNG',
            'secondary_file_url' => 'uploads/downloads/logo.png',
            'is_grouped_versions' => true,
            'version_1_label' => 'Version 1',
            'version_1_image_url' => 'uploads/downloads/logo-v1.png',
            'version_1_pdf_url' => 'uploads/downloads/logo-v1.pdf',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        DownloadMediaItem::create([
            'section_title' => 'Floor Plan',
            'item_title' => 'Venue Floor Plan',
            'item_subtitle' => 'Main hall layout',
            'image_url' => 'uploads/downloads/floor.png',
            'primary_button_text' => 'PDF',
            'primary_file_url' => 'uploads/downloads/floor.pdf',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $this->getJson(route('api.v1.downloads.index'))
            ->assertOk()
            ->assertJsonPath('data.sections.0.title', 'Logo White Background')
            ->assertJsonPath('data.sections.0.layout', 'logo')
            ->assertJsonPath('data.sections.0.items.0.primary_file_url', '/uploads/downloads/logo.pdf')
            ->assertJsonPath('data.sections.1.layout', 'wide');
    }

    public function test_notifications_api_compiles_feed_items_from_content(): void
    {
        DownloadMediaItem::create([
            'section_title' => 'Logo White Background',
            'item_title' => 'First Demo Logo',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        DownloadMediaItem::create([
            'section_title' => 'Railway Booking Guide',
            'item_title' => 'Train Booking Guide',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->getJson(route('api.v1.notifications.index'))
            ->assertOk()
            ->assertJsonPath('data.today_items.0.title', 'Download Available')
            ->assertJsonPath('data.today_items.0.description', 'Train Booking Guide is available in Railway Booking Guide.')
            ->assertJsonPath('data.earlier_items.0.title', 'Download Added')
            ->assertJsonPath('data.earlier_items.0.description', 'First Demo Logo is available in Logo White Background.')
            ->assertJsonPath('data.unread_count', 1);

        $response->assertJsonCount(1, 'data.today_items');
    }
}
