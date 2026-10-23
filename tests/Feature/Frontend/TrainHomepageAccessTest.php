<?php

namespace Tests\Feature\Frontend;

use App\Models\Participant;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\TrainSearchService;
use App\Support\FrontendPageAccessSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TrainHomepageAccessTest extends TestCase
{
    use DatabaseTransactions;

    private const HOME_ROUTES = [
        'frontend.trains.home',
        'frontend.trains.legacy-home',
        'frontend.trains.search',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        FrontendPageAccessSettings::set(FrontendPageAccessSettings::defaults());
        $this->mock(TrainSearchService::class, function ($mock): void {
            $mock->shouldReceive('stations')->andReturn(collect());
        });
    }

    public function test_train_passenger_keeps_their_session_on_every_homepage(): void
    {
        $passenger = $this->trainPassenger();
        $this->actingAs($passenger)->withSession(['homepage_session_marker' => 'preserve']);

        foreach (self::HOME_ROUTES as $route) {
            $sessionId = session()->getId();
            $this->withCookie(config('session.cookie'), $sessionId)
                ->get(route($route))->assertOk()->assertSessionHas('homepage_session_marker', 'preserve');
            $this->assertAuthenticatedAs($passenger);
            $this->assertSame($sessionId, session()->getId());
        }

        $this->assertDatabaseHas('participants', [
            'user_id' => $passenger->id,
            'is_approved' => false,
            'train_access_enabled' => true,
        ]);
    }

    public function test_train_passenger_can_open_profile_and_has_account_navigation(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_header_settings'],
            ['value' => json_encode([
                'logo_url' => 'uploads/event-logo.png',
                'menu_items' => [['name' => 'EVENT', 'link' => '/events']],
                'button_text' => 'Buy Tickets',
            ])]
        );
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_footer_settings'],
            ['value' => json_encode([
                'upcoming_events_title' => 'Upcoming Events',
                'upcoming_events' => [['name' => 'EventFlow launch', 'cta_text' => 'Get tickets']],
                'copyright_text' => 'IGI Summit',
            ])]
        );

        $passenger = $this->trainPassenger();
        $this->actingAs($passenger);

        $this->get(route('frontend.trains.home'))
            ->assertOk()
            ->assertSee('Find trains')
            ->assertSee('My Bookings')
            ->assertSee('Profile')
            ->assertSee('Sign Out')
            ->assertSee('action="' . route('logout') . '"', false)
            ->assertSee('name="_token"', false);

        $this->get(route('profile.password.edit'))
            ->assertOk()
            ->assertSee('SmartSeat')
            ->assertSee('Profile')
            ->assertSee('Sign Out')
            ->assertDontSee('EventFlow')
            ->assertDontSee('IGI Summit')
            ->assertDontSee('Upcoming Events')
            ->assertDontSee('Buy Tickets')
            ->assertDontSee('event-logo.png');

        $this->get(route('frontend.trains.bookings'))
            ->assertOk()
            ->assertSee('Find trains')
            ->assertSee('My Bookings')
            ->assertSee('Profile')
            ->assertSee('Sign Out');

        $this->post(route('logout'))->assertRedirect();
        $this->assertGuest();
    }

    public function test_guest_homepage_policy_is_consistent_across_all_entry_points(): void
    {
        $this->assertHomepagePolicy('guest');
    }

    public function test_guest_header_offers_train_sign_in_and_registration(): void
    {
        $this->get(route('frontend.trains.home'))
            ->assertOk()
            ->assertSee('Find trains')
            ->assertSee('Sign In')
            ->assertSee('Register')
            ->assertDontSee('Profile')
            ->assertDontSee('Sign Out')
            ->assertDontSee('EventFlow');
    }

    public function test_user_homepage_policy_denies_access_without_logging_out_the_passenger(): void
    {
        $this->assertHomepagePolicy('user', $this->trainPassenger());
    }

    public function test_member_homepage_policy_is_consistent_across_all_entry_points(): void
    {
        $member = $this->trainPassenger();
        $role = Role::create(['title' => 'Member']);
        $member->roles()->attach($role);

        $this->assertHomepagePolicy('member', $member);
    }

    public function test_train_access_does_not_bypass_legacy_approval_on_non_train_pages(): void
    {
        $this->actingAs($this->trainPassenger())
            ->get(route('frontend.faqs.index'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('auth_notice_title', 'Access Revoked');

        $this->assertGuest();
    }

    public function test_passenger_without_train_or_event_approval_is_still_logged_out(): void
    {
        $passenger = $this->trainPassenger(false);

        foreach (self::HOME_ROUTES as $route) {
            $this->actingAs($passenger)->get(route($route))->assertRedirect(route('login'));
            $this->assertGuest();
        }
    }

    private function trainPassenger(bool $trainAccess = true): User
    {
        $user = User::factory()->create();
        Participant::factory()->forUser($user)->create([
            'is_approved' => false,
            'train_access_enabled' => $trainAccess,
        ]);

        return $user;
    }

    private function assertHomepagePolicy(string $role, ?User $user = null): void
    {
        if ($user) {
            $this->actingAs($user);
        }
        $this->withSession(['homepage_session_marker' => 'preserve']);
        $settings = FrontendPageAccessSettings::defaults();
        $settings['home'][$role] = false;
        FrontendPageAccessSettings::set($settings);

        foreach (self::HOME_ROUTES as $route) {
            $sessionId = session()->getId();
            $this->withCookie(config('session.cookie'), $sessionId)
                ->get(route($route))->assertRedirect(route('login'))
                ->assertSessionHas('homepage_session_marker', 'preserve');
            $this->assertSame($sessionId, session()->getId());
            $user ? $this->assertAuthenticatedAs($user) : $this->assertGuest();
        }

        $settings['home'][$role] = true;
        FrontendPageAccessSettings::set($settings);

        foreach (self::HOME_ROUTES as $route) {
            $this->get(route($route))->assertOk();
            $user ? $this->assertAuthenticatedAs($user) : $this->assertGuest();
        }
    }
}
