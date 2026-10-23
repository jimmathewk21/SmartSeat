<?php

namespace Tests\Feature\Admin;

use App\Models\MyContact;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\FrontendPageAccessSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MyContactsAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_create_contact_with_extended_fields(): void
    {
        $admin = $this->createAdminWithSettingsAccess();

        $this->actingAs($admin)
            ->post(route('admin.my-contacts.store'), [
                'name' => 'Pravin Anand',
                'gender' => 'Male',
                'designation' => 'Managing Partner',
                'company_name' => 'Anand and Anand',
                'official_email' => 'pravin@example.com',
                'personal_email' => 'personal@example.com',
                'alternate_email' => 'alternate@example.com',
                'mobile_number' => '+911234567891',
                'whatsapp_number' => '+911234567890',
                'alternate_mobile_number' => '+911234567892',
                'country' => 'India',
                'category' => 'Corporate',
                'specialization' => 'Patent',
                'experience' => '20+ Years',
                'role' => 'Speaker',
                'speaker_tag' => 'VIP',
                'event_name' => 'Event Name',
                'event_names' => "Event Name\nAnother Event",
                'contact_status' => 'known',
                'is_whitelisted' => '1',
                'is_published' => '1',
                'show_email' => '1',
                'linkedin_profile' => 'www.linkedin.com/in/pravin',
                'web_url' => 'www.example.com',
            ])
            ->assertRedirect(route('admin.my-contacts.index'));

        $this->assertDatabaseHas('my_contacts', [
            'official_email' => 'pravin@example.com',
            'specialization' => 'Patent',
            'experience' => '20+ Years',
            'speaker_tag' => 'VIP',
            'contact_status' => 'known',
            'is_whitelisted' => 1,
            'is_published' => 1,
            'show_email' => 1,
            'linkedin_profile' => 'https://www.linkedin.com/in/pravin',
            'web_url' => 'https://www.example.com',
            'stars' => 5,
        ]);
    }

    public function test_admin_cannot_create_duplicate_official_email(): void
    {
        $admin = $this->createAdminWithSettingsAccess();

        MyContact::factory()->create([
            'name' => 'Existing Contact',
            'official_email' => 'duplicate@example.com',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.my-contacts.create'))
            ->post(route('admin.my-contacts.store'), [
                'name' => 'Duplicate Attempt',
                'gender' => 'Male',
                'designation' => 'Manager',
                'company_name' => 'Example Co',
                'official_email' => 'duplicate@example.com',
                'contact_status' => MyContact::STATUS_DEFAULT,
            ])
            ->assertRedirect(route('admin.my-contacts.create'))
            ->assertSessionHasErrors(['official_email']);
    }

    public function test_admin_my_contacts_email_check_reports_duplicates_and_available_addresses(): void
    {
        $admin = $this->createAdminWithSettingsAccess();

        MyContact::factory()->create([
            'name' => 'Existing Contact',
            'official_email' => 'existing-contact@example.com',
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.my-contacts.check-email', [
                'email' => 'existing-contact@example.com',
            ]))
            ->assertOk()
            ->assertJson([
                'exists' => true,
                'message' => 'This email is already used by another contact.',
            ]);

        $this->actingAs($admin)
            ->getJson(route('admin.my-contacts.check-email', [
                'email' => 'available-contact@example.com',
            ]))
            ->assertOk()
            ->assertJson([
                'exists' => false,
                'message' => 'Email is available.',
            ]);
    }

    public function test_admin_my_contacts_email_check_ignores_current_record_on_edit(): void
    {
        $admin = $this->createAdminWithSettingsAccess();

        $contact = MyContact::factory()->create([
            'name' => 'Editable Contact',
            'official_email' => 'editable-contact@example.com',
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.my-contacts.check-email', [
                'email' => 'editable-contact@example.com',
                'my_contact_id' => $contact->id,
            ]))
            ->assertOk()
            ->assertJson([
                'exists' => false,
                'message' => 'Email is available.',
            ]);
    }

    public function test_admin_create_form_shows_stars_as_read_only(): void
    {
        $admin = $this->createAdminWithSettingsAccess();

        $this->actingAs($admin)
            ->get(route('admin.my-contacts.create'))
            ->assertOk()
            ->assertSee('name="stars"', false)
            ->assertSee('readonly', false)
            ->assertSee('Auto-calculated from category, experience, key person, key role, and VIP speaker tag.', false)
            ->assertSee('Key Person', false);
    }

    public function test_admin_can_update_contact_and_recalculate_star_score(): void
    {
        $admin = $this->createAdminWithSettingsAccess();
        $contact = MyContact::factory()->create([
            'category' => 'Law Firm',
            'experience' => '4-7 Years',
            'role' => null,
            'speaker_tag' => 'Delegate',
            'is_whitelisted' => false,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.my-contacts.update', $contact), [
                'name' => 'Updated Contact',
                'official_email' => $contact->official_email,
                'contact_status' => MyContact::STATUS_DEFAULT,
                'category' => 'Judge',
                'experience' => '4-7 Years',
                'speaker_tag' => 'VIP',
                'role' => 'Chief Guest',
                'is_whitelisted' => '1',
            ])
            ->assertRedirect(route('admin.my-contacts.index'));

        $this->assertDatabaseHas('my_contacts', [
            'id' => $contact->id,
            'category' => 'Judge',
            'experience' => '4-7 Years',
            'speaker_tag' => 'VIP',
            'role' => 'Chief Guest',
            'is_whitelisted' => 1,
            'stars' => 4,
        ]);
    }

    public function test_admin_can_import_contacts_and_recalculate_star_score(): void
    {
        $admin = $this->createAdminWithSettingsAccess();

        $csv = <<<'CSV'
name,official_email,contact_status,category,experience,role,speaker_tag,stars
Imported VIP,vip-import@example.com,default,Corporate,13-20 Years,Director,VIP,1
CSV;

        $this->actingAs($admin)
            ->post(route('admin.my-contacts.import'), [
                'import_file' => UploadedFile::fake()->createWithContent('my-contacts.csv', $csv),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('my_contacts', [
            'official_email' => 'vip-import@example.com',
            'category' => 'Corporate',
            'experience' => '13-20 Years',
            'role' => 'Director',
            'speaker_tag' => 'VIP',
            'stars' => 4,
        ]);
    }

    public function test_admin_my_contacts_list_shows_only_earned_stars(): void
    {
        $admin = $this->createAdminWithSettingsAccess();

        MyContact::factory()->create([
            'name' => 'Three Star Contact',
            'official_email' => 'three-star@example.com',
            'category' => 'Corporate',
            'experience' => '13-20 Years',
            'role' => null,
            'speaker_tag' => 'Delegate',
            'is_whitelisted' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.my-contacts.index'))
            ->assertOk()
            ->assertSee('Stars: &#9733;&#9733;&#9733;', false)
            ->assertDontSee('&#9734;', false);
    }

    public function test_admin_can_bulk_unsubscribe_contacts(): void
    {
        $admin = $this->createAdminWithSettingsAccess();
        $contacts = MyContact::factory()->count(2)->create([
            'is_subscribed' => true,
            'is_whitelisted' => true,
            'contact_status' => MyContact::STATUS_DEFAULT,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.my-contacts.bulkAction'), [
                'action' => 'unsubscribe',
                'contact_ids' => $contacts->pluck('id')->all(),
            ])
            ->assertRedirect();

        foreach ($contacts as $contact) {
            $this->assertDatabaseHas('my_contacts', [
                'id' => $contact->id,
                'is_subscribed' => 0,
                'is_whitelisted' => 1,
                'contact_status' => MyContact::STATUS_UNSUBSCRIBED,
            ]);
        }
    }

    public function test_admin_can_bulk_publish_and_unpublish_contacts(): void
    {
        $admin = $this->createAdminWithSettingsAccess();
        $contacts = MyContact::factory()->count(2)->create([
            'is_published' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.my-contacts.bulkAction'), [
                'action' => 'publish',
                'contact_ids' => $contacts->pluck('id')->all(),
            ])
            ->assertRedirect();

        foreach ($contacts as $contact) {
            $this->assertDatabaseHas('my_contacts', [
                'id' => $contact->id,
                'is_published' => 1,
            ]);
        }

        $this->actingAs($admin)
            ->post(route('admin.my-contacts.bulkAction'), [
                'action' => 'unpublish',
                'contact_ids' => $contacts->pluck('id')->all(),
            ])
            ->assertRedirect();

        foreach ($contacts as $contact) {
            $this->assertDatabaseHas('my_contacts', [
                'id' => $contact->id,
                'is_published' => 0,
            ]);
        }
    }

    public function test_admin_can_bulk_mark_contacts_known(): void
    {
        $admin = $this->createAdminWithSettingsAccess();
        $contacts = MyContact::factory()->count(2)->create([
            'is_subscribed' => true,
            'is_whitelisted' => false,
            'is_blacklisted' => false,
            'contact_status' => MyContact::STATUS_DEFAULT,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.my-contacts.bulkAction'), [
                'action' => 'mark_known',
                'contact_ids' => $contacts->pluck('id')->all(),
            ])
            ->assertRedirect();

        foreach ($contacts as $contact) {
            $this->assertDatabaseHas('my_contacts', [
                'id' => $contact->id,
                'is_subscribed' => 1,
                'is_whitelisted' => 0,
                'is_blacklisted' => 0,
                'contact_status' => MyContact::STATUS_KNOWN,
            ]);
        }
    }

    public function test_admin_can_bulk_mark_contacts_default_subscribed_and_clear_known_flags(): void
    {
        $admin = $this->createAdminWithSettingsAccess();
        $contacts = MyContact::factory()->count(2)->create([
            'is_subscribed' => true,
            'is_whitelisted' => true,
            'is_blacklisted' => false,
            'contact_status' => MyContact::STATUS_KNOWN,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.my-contacts.bulkAction'), [
                'action' => 'subscribe',
                'contact_ids' => $contacts->pluck('id')->all(),
            ])
            ->assertRedirect();

        foreach ($contacts as $contact) {
            $this->assertDatabaseHas('my_contacts', [
                'id' => $contact->id,
                'is_subscribed' => 1,
                'is_whitelisted' => 1,
                'is_blacklisted' => 0,
                'contact_status' => MyContact::STATUS_DEFAULT,
            ]);
        }
    }

    public function test_admin_can_bulk_blacklist_contacts(): void
    {
        $admin = $this->createAdminWithSettingsAccess();
        $contacts = MyContact::factory()->count(2)->create([
            'is_subscribed' => true,
            'is_whitelisted' => true,
            'is_blacklisted' => false,
            'contact_status' => MyContact::STATUS_KNOWN,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.my-contacts.bulkAction'), [
                'action' => 'blacklist',
                'contact_ids' => $contacts->pluck('id')->all(),
            ])
            ->assertRedirect();

        foreach ($contacts as $contact) {
            $this->assertDatabaseHas('my_contacts', [
                'id' => $contact->id,
                'is_subscribed' => 0,
                'is_whitelisted' => 1,
                'is_blacklisted' => 1,
                'contact_status' => MyContact::STATUS_BLACKLISTED,
            ]);
        }
    }

    public function test_admin_can_bulk_reset_contacts_to_default(): void
    {
        $admin = $this->createAdminWithSettingsAccess();
        $contacts = MyContact::factory()->count(2)->create([
            'is_subscribed' => false,
            'is_whitelisted' => true,
            'is_blacklisted' => true,
            'contact_status' => MyContact::STATUS_BLACKLISTED,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.my-contacts.bulkAction'), [
                'action' => 'mark_default',
                'contact_ids' => $contacts->pluck('id')->all(),
            ])
            ->assertRedirect();

        foreach ($contacts as $contact) {
            $this->assertDatabaseHas('my_contacts', [
                'id' => $contact->id,
                'is_subscribed' => 1,
                'is_whitelisted' => 1,
                'is_blacklisted' => 0,
                'contact_status' => MyContact::STATUS_DEFAULT,
            ]);
        }
    }

    public function test_admin_can_bulk_delete_contacts(): void
    {
        $admin = $this->createAdminWithSettingsAccess();
        $contacts = MyContact::factory()->count(2)->create();

        $this->actingAs($admin)
            ->post(route('admin.my-contacts.bulkAction'), [
                'action' => 'delete',
                'contact_ids' => $contacts->pluck('id')->all(),
            ])
            ->assertRedirect();

        foreach ($contacts as $contact) {
            $this->assertSoftDeleted('my_contacts', [
                'id' => $contact->id,
            ]);
        }
    }

    public function test_admin_can_save_more_than_three_events(): void
    {
        $admin = $this->createAdminWithSettingsAccess();

        $this->actingAs($admin)
            ->post(route('admin.my-contacts.store'), [
                'name' => 'Too Many Events',
                'official_email' => 'events@example.com',
                'contact_status' => MyContact::STATUS_DEFAULT,
                'event_names' => "Event 1\nEvent 2\nEvent 3\nEvent 4",
            ])
            ->assertRedirect(route('admin.my-contacts.index'));

        $contact = MyContact::query()
            ->where('official_email', 'events@example.com')
            ->firstOrFail();

        $this->assertSame("Event 1\nEvent 2\nEvent 3\nEvent 4", str_replace("\r\n", "\n", (string) $contact->event_names));
    }

    public function test_admin_event_list_is_deduplicated_case_insensitive_without_limit(): void
    {
        $admin = $this->createAdminWithSettingsAccess();

        $this->actingAs($admin)
            ->post(route('admin.my-contacts.store'), [
                'name' => 'Event Normalization',
                'official_email' => 'event-normalization@example.com',
                'contact_status' => MyContact::STATUS_DEFAULT,
                'event_name' => 'Event A',
                'event_names' => "Event A\nEvent B\nevent b\nEvent C\nEvent D",
            ])
            ->assertRedirect(route('admin.my-contacts.index'));

        $contact = MyContact::query()
            ->where('official_email', 'event-normalization@example.com')
            ->firstOrFail();

        $this->assertSame("Event A\nEvent B\nEvent C\nEvent D", str_replace("\r\n", "\n", (string) $contact->event_names));
        $this->assertSame(['Event A', 'Event B', 'Event C', 'Event D'], $contact->event_list);
    }

    public function test_frontend_my_contacts_page_only_shows_published_allowed_statuses(): void
    {
        FrontendPageAccessSettings::set([
            'my_contacts' => [
                'guest' => true,
            ],
        ]);

        MyContact::factory()->create([
            'name' => 'Visible Contact',
            'official_email' => 'visible@example.com',
            'contact_status' => MyContact::STATUS_DEFAULT,
            'is_published' => true,
        ]);

        MyContact::factory()->create([
            'name' => 'Hidden Unpublished',
            'official_email' => 'hidden-unpublished@example.com',
            'contact_status' => MyContact::STATUS_DEFAULT,
            'is_published' => false,
        ]);

        MyContact::factory()->create([
            'name' => 'Hidden Blacklisted',
            'official_email' => 'hidden-blacklisted@example.com',
            'contact_status' => MyContact::STATUS_BLACKLISTED,
            'is_published' => true,
            'is_blacklisted' => true,
        ]);

        $this->get(route('frontend.my-contacts.index'))
            ->assertOk()
            ->assertSee('Visible Contact')
            ->assertDontSee('Hidden Unpublished')
            ->assertDontSee('Hidden Blacklisted');
    }

    public function test_frontend_my_contacts_page_shows_ten_rows_per_page(): void
    {
        FrontendPageAccessSettings::set([
            'my_contacts' => [
                'guest' => true,
            ],
        ]);

        for ($i = 1; $i <= 41; $i++) {
            MyContact::factory()->create([
                'name' => sprintf('Contact %02d', $i),
                'official_email' => sprintf('contact-%02d@example.com', $i),
                'contact_status' => MyContact::STATUS_DEFAULT,
                'is_published' => true,
            ]);
        }

        $this->get(route('frontend.my-contacts.index'))
            ->assertOk()
            ->assertSee('Contact 01')
            ->assertSee('Contact 40')
            ->assertDontSee('Contact 41');

        $this->get(route('frontend.my-contacts.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Contact 41');
    }

    private function createAdminWithSettingsAccess(): User
    {
        $role = Role::firstOrCreate(['title' => 'Settings Admin']);
        $permission = Permission::firstOrCreate(['title' => 'setting_access']);
        $role->permissions()->syncWithoutDetaching([$permission->id]);

        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        return $admin;
    }
}
