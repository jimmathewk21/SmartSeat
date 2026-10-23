<?php

namespace Tests\Feature\Frontend;

use App\Models\Sponsor;
use App\Models\SponsorCategory;
use App\Models\User;
use App\Support\FrontendPageAccessSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SponsorPageOrderTest extends TestCase
{
    use DatabaseTransactions;

    public function test_sponsor_page_shows_latest_categories_and_sponsors_first(): void
    {
        FrontendPageAccessSettings::set(FrontendPageAccessSettings::defaults());

        $user = User::factory()->create();

        $olderCategory = SponsorCategory::create([
            'name' => 'Older Category',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $newerCategory = SponsorCategory::create([
            'name' => 'Newer Category',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $olderSponsor = Sponsor::create([
            'name' => 'Older Sponsor',
            'website_url' => 'https://older.example.com',
            'logo_url' => '',
            'logo_size' => 'md',
            'is_active' => true,
            'sort_order' => 1,
            'sponsor_category_id' => $olderCategory->id,
        ]);
        $olderSponsor->sponsorCategories()->attach($olderCategory->id);

        $newerSponsor = Sponsor::create([
            'name' => 'Newer Sponsor',
            'website_url' => 'https://newer.example.com',
            'logo_url' => '',
            'logo_size' => 'md',
            'is_active' => true,
            'sort_order' => 2,
            'sponsor_category_id' => $olderCategory->id,
        ]);
        $newerSponsor->sponsorCategories()->attach($olderCategory->id);

        $newerCategorySponsor = Sponsor::create([
            'name' => 'Newer Category Sponsor',
            'website_url' => 'https://newer-category.example.com',
            'logo_url' => '',
            'logo_size' => 'md',
            'is_active' => true,
            'sort_order' => 1,
            'sponsor_category_id' => $newerCategory->id,
        ]);
        $newerCategorySponsor->sponsorCategories()->attach($newerCategory->id);

        $response = $this->actingAs($user)
            ->get(route('frontend.sponsors.index'))
            ->assertOk();

        $data = $response->viewData();

        $categories = $data['categories'];
        $newerCategoryIndex = $categories->search(fn ($category) => $category->id === $newerCategory->id);
        $olderCategoryIndex = $categories->search(fn ($category) => $category->id === $olderCategory->id);
        $olderCategorySponsors = $categories->firstWhere('id', $olderCategory->id)?->sponsors ?? collect();
        $newerSponsorIndex = $olderCategorySponsors->search(fn ($sponsor) => $sponsor->id === $newerSponsor->id);
        $olderSponsorIndex = $olderCategorySponsors->search(fn ($sponsor) => $sponsor->id === $olderSponsor->id);

        $this->assertNotFalse($newerCategoryIndex);
        $this->assertNotFalse($olderCategoryIndex);
        $this->assertLessThan($olderCategoryIndex, $newerCategoryIndex);

        $this->assertSame($newerCategorySponsor->id, $categories->firstWhere('id', $newerCategory->id)?->sponsors?->first()?->id);
        $this->assertNotFalse($newerSponsorIndex);
        $this->assertNotFalse($olderSponsorIndex);
        $this->assertLessThan($olderSponsorIndex, $newerSponsorIndex);
    }
}
