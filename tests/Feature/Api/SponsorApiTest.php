<?php

namespace Tests\Feature\Api;

use App\Models\Speaker;
use App\Models\Sponsor;
use App\Models\SponsorCategory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SponsorApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_sponsor_list_groups_active_sponsors_by_category(): void
    {
        $platinum = SponsorCategory::create([
            'name' => 'Platinum Sponsors',
            'sort_order' => 1,
            'logo_size' => 'lg',
            'is_active' => true,
        ]);

        $gold = SponsorCategory::create([
            'name' => 'Gold Sponsors',
            'sort_order' => 2,
            'logo_size' => 'md',
            'is_active' => true,
        ]);

        $featured = Sponsor::create([
            'sponsor_category_id' => $platinum->id,
            'name' => 'Microsoft',
            'sub_category' => 'Title Sponsor',
            'website_url' => 'https://microsoft.com',
            'logo_url' => 'uploads/sponsors/microsoft.png',
            'logo_size' => 'lg',
            'is_active' => true,
            'sort_order' => 1,
            'show_on_homepage' => true,
            'show_on_scrollbar' => true,
        ]);
        $featured->sponsorCategories()->sync([$platinum->id]);

        $partner = Sponsor::create([
            'sponsor_category_id' => $gold->id,
            'name' => 'Contoso',
            'sub_category' => 'Gold Partner',
            'website_url' => 'https://contoso.example',
            'logo_url' => 'uploads/sponsors/contoso.png',
            'logo_size' => 'md',
            'is_active' => true,
            'sort_order' => 2,
            'show_on_homepage' => true,
            'show_on_scrollbar' => false,
        ]);
        $partner->sponsorCategories()->sync([$gold->id]);

        $this->getJson(route('api.v1.sponsors.index'))
            ->assertOk()
            ->assertJsonPath('data.title_sponsor.name', 'Microsoft')
            ->assertJsonPath('data.categories.0.name', 'Platinum Sponsors')
            ->assertJsonPath('data.categories.0.sponsors.0.name', 'Microsoft')
            ->assertJsonPath('data.categories.1.sponsors.0.name', 'Contoso');
    }

    public function test_sponsor_detail_returns_profile_payload(): void
    {
        $category = SponsorCategory::create([
            'name' => 'Platinum Sponsors',
            'sort_order' => 1,
            'logo_size' => 'lg',
            'is_active' => true,
        ]);

        $sponsor = Sponsor::create([
            'sponsor_category_id' => $category->id,
            'name' => 'Microsoft',
            'sub_category' => 'Title Sponsor',
            'use_sub_category_text' => false,
            'website_url' => 'https://microsoft.com',
            'linkedin_url' => 'https://linkedin.com/company/microsoft',
            'logo_url' => 'uploads/sponsors/microsoft.png',
            'logo_size' => 'lg',
            'is_active' => true,
            'sort_order' => 1,
            'detail' => '<p>Empowering every person.</p>',
            'asset_type' => 'file',
            'resource_url' => 'uploads/sponsors/bio.pdf',
            'show_on_homepage' => true,
            'show_on_scrollbar' => true,
            'show_contact_details' => true,
            'show_website_url' => true,
            'show_linkedin_url' => true,
            'show_bio' => true,
            'show_advert' => true,
            'contact_name' => 'Ada Lovelace',
            'contact_designation' => 'Chief AI Officer',
            'contact_mobile' => '9999999999',
            'contact_email' => 'ada@example.com',
            'advert_url' => 'uploads/sponsors/advert.pdf',
            'brochure_url' => 'uploads/sponsors/brochure.pdf',
            'sponsor_video_url' => 'https://youtube.com/watch?v=abc123',
        ]);
        $sponsor->sponsorCategories()->sync([$category->id]);

        $speaker = Speaker::factory()->create([
            'name' => 'Dr. Elena Vance',
            'designation' => 'Chief AI Officer',
            'company_name' => 'TechVision Inc.',
            'is_active' => true,
            'show_on_scrollbar' => true,
        ]);

        $this->getJson(route('api.v1.sponsors.show', $sponsor))
            ->assertOk()
            ->assertJsonPath('data.name', 'Microsoft')
            ->assertJsonPath('data.primary_category_name', 'Platinum Sponsors')
            ->assertJsonPath('data.contact.name', 'Ada Lovelace')
            ->assertJsonPath('data.resource_url', '/uploads/sponsors/bio.pdf')
            ->assertJsonPath('data.related_speakers.0.name', 'Dr. Elena Vance');
    }

    public function test_inactive_sponsor_detail_returns_404(): void
    {
        $category = SponsorCategory::create([
            'name' => 'Platinum Sponsors',
            'sort_order' => 1,
            'logo_size' => 'lg',
            'is_active' => true,
        ]);

        $sponsor = Sponsor::create([
            'sponsor_category_id' => $category->id,
            'name' => 'Inactive Sponsor',
            'is_active' => false,
            'sort_order' => 1,
        ]);
        $sponsor->sponsorCategories()->sync([$category->id]);

        $this->getJson(route('api.v1.sponsors.show', $sponsor))
            ->assertNotFound();
    }
}
