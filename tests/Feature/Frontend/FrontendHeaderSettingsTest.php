<?php

namespace Tests\Feature\Frontend;

use App\Models\Setting;
use App\Support\FrontendHeaderSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FrontendHeaderSettingsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_removed_event_links_are_filtered_from_saved_header_settings(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => FrontendHeaderSettings::SETTING_KEY],
            ['value' => json_encode([
                'menu_items' => [
                    ['name' => 'Speakers', 'link' => '/speakers'],
                    ['name' => 'FAQs', 'link' => '/faqs'],
                    ['name' => 'External Speaker Page', 'link' => 'https://example.org/speakers'],
                ],
                'button_text' => 'Buy Tickets',
                'button_link' => '#section-tickets',
            ], JSON_UNESCAPED_SLASHES)]
        );

        $settings = FrontendHeaderSettings::get();

        $this->assertSame([
            ['name' => 'FAQs', 'link' => '/faqs'],
            ['name' => 'External Speaker Page', 'link' => 'https://example.org/speakers'],
        ], $settings['menu_items']);
        $this->assertSame('Search Trains', $settings['button_text']);
        $this->assertSame('/trains', $settings['button_link']);
        $this->assertArrayNotHasKey('/speakers', FrontendHeaderSettings::pageOptions());
        $this->assertArrayHasKey('/trains', FrontendHeaderSettings::pageOptions());
    }
}
