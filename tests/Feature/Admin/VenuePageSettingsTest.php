<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\VenueHotel;
use App\Support\VenuePageSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VenuePageSettingsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_update_venue_page_settings(): void
    {
        $admin = $this->createVenueAdmin();

        $this->actingAs($admin)
            ->put(route('admin.venue.page-settings.update'), [
                'hero_label' => 'VENUE TEST LABEL',
                'hero_title' => 'Custom Venue Title',
                'hero_title_suffix' => 'custom suffix',
                'hero_description' => 'Custom venue description.',
                'empty_state_text' => 'No partner venues yet.',
            ])
            ->assertRedirect(route('admin.venue.page-settings.edit'));

        $settings = VenuePageSettings::get();

        $this->assertSame('VENUE TEST LABEL', $settings['hero_label']);
        $this->assertSame('Custom Venue Title', $settings['hero_title']);
        $this->assertSame('custom suffix', $settings['hero_title_suffix']);
        $this->assertSame('Custom venue description.', $settings['hero_description']);
        $this->assertSame('No partner venues yet.', $settings['empty_state_text']);
    }

    public function test_venue_page_renders_managed_top_content(): void
    {
        $user = $this->createVenueAdmin();

        VenuePageSettings::set([
            'hero_label' => 'VENUE HERO LABEL',
            'hero_title' => 'Choose Your Perfect Hotel',
            'hero_title_suffix' => 'reserve today',
            'hero_description' => 'Managed venue hero description.',
            'empty_state_text' => 'No venues right now.',
        ]);

        VenueHotel::factory()->create([
            'name' => 'Palm Business Bay Hotel',
        ]);

        $this->actingAs($user)
            ->get(route('frontend.venue.index'))
            ->assertOk()
            ->assertSee('VENUE HERO LABEL')
            ->assertSee('Choose Your Perfect Hotel')
            ->assertSee('reserve today')
            ->assertSee('Managed venue hero description.');
    }

    public function test_updating_venue_page_settings_preserves_reservation_settings(): void
    {
        $admin = $this->createVenueAdmin();

        VenuePageSettings::set([
            'reservation_eyebrow_text' => 'CUSTOM RESERVE',
            'reservation_heading' => 'Reserve A Room',
            'reservation_heading_suffix' => 'quickly',
            'reservation_description' => 'Reservation text from admin.',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.venue.page-settings.update'), [
                'hero_label' => 'VENUE LABEL',
                'hero_title' => 'Venue Title',
                'hero_title_suffix' => 'Suffix',
                'hero_description' => 'Venue description',
                'empty_state_text' => 'No venues',
            ])
            ->assertRedirect(route('admin.venue.page-settings.edit'));

        $settings = VenuePageSettings::get();

        $this->assertSame('CUSTOM RESERVE', $settings['reservation_eyebrow_text']);
        $this->assertSame('Reserve A Room', $settings['reservation_heading']);
        $this->assertSame('quickly', $settings['reservation_heading_suffix']);
        $this->assertSame('Reservation text from admin.', $settings['reservation_description']);
    }

    private function createVenueAdmin(): User
    {
        $role = Role::firstOrCreate(['title' => 'Venue Admin']);
        Permission::insertOrIgnore([
            ['title' => 'venue_access'],
        ]);

        $permissionIds = Permission::whereIn('title', ['venue_access'])->pluck('id');
        $role->permissions()->sync($permissionIds);

        $user = User::factory()->create();
        $user->roles()->sync([$role->id]);

        return $user;
    }
}
