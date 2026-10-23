<?php

namespace Tests\Feature\Frontend;

use App\Models\DownloadMediaItem;
use App\Models\Setting;
use App\Models\User;
use App\Support\FrontendPageAccessSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DownloadMediaItemOrderTest extends TestCase
{
    use DatabaseTransactions;

    public function test_download_page_uses_sort_order_for_frontend_sequence(): void
    {
        FrontendPageAccessSettings::set(FrontendPageAccessSettings::defaults());
        $user = User::factory()->create();

        DownloadMediaItem::query()->create([
            'section_title' => 'Section B',
            'item_title' => 'Second Item',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        DownloadMediaItem::query()->create([
            'section_title' => 'Section A',
            'item_title' => 'First Item',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('frontend.download.index'));

        $response->assertOk();
        $response->assertSeeInOrder([
            'Section A',
            'Section B',
        ], false);
    }
}
