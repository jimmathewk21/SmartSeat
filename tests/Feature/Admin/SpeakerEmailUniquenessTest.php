<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Speaker;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SpeakerEmailUniquenessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_check_speaker_email_availability(): void
    {
        $admin = $this->makeSpeakerAdmin();

        Speaker::factory()->create([
            'email' => 'speaker@example.com',
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.speakers.check-email', ['email' => 'speaker@example.com']))
            ->assertOk()
            ->assertJson([
                'exists' => true,
            ]);

        $this->actingAs($admin)
            ->getJson(route('admin.speakers.check-email', [
                'email' => 'speaker@example.com',
                'speaker_id' => Speaker::query()->where('email', 'speaker@example.com')->value('id'),
            ]))
            ->assertOk()
            ->assertJson([
                'exists' => false,
            ]);

        $this->actingAs($admin)
            ->getJson(route('admin.speakers.check-email', ['email' => 'new@example.com']))
            ->assertOk()
            ->assertJson([
                'exists' => false,
            ]);
    }

    public function test_admin_cannot_create_duplicate_speaker_email(): void
    {
        $admin = $this->makeSpeakerAdmin();

        Speaker::factory()->create([
            'email' => 'speaker@example.com',
        ]);

        $payload = $this->basePayload([
            'email' => 'speaker@example.com',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.speakers.create'))
            ->post(route('admin.speakers.store'), $payload)
            ->assertRedirect(route('admin.speakers.create'))
            ->assertSessionHasErrors('email');
    }

    public function test_admin_cannot_update_speaker_email_to_existing_email(): void
    {
        $admin = $this->makeSpeakerAdmin();

        $existingSpeaker = Speaker::factory()->create([
            'email' => 'speaker@example.com',
        ]);

        $speaker = Speaker::factory()->create([
            'email' => 'other@example.com',
        ]);

        $payload = $this->basePayload([
            'email' => 'speaker@example.com',
            'name' => $speaker->name,
            'designation' => $speaker->designation,
            'bio' => $speaker->bio,
            'photo_url' => $speaker->photo_url,
            'gender' => $speaker->gender,
            'speaker_role' => $speaker->speaker_role ?? 'speaker',
            'is_active' => $speaker->is_active ? '1' : '0',
            'sort_order' => $speaker->sort_order,
            'company_name' => $speaker->company_name,
            'country' => $speaker->country,
            'phone' => $speaker->phone,
            'show_contact_details' => $speaker->show_contact_details ? '1' : '0',
            'linkedin_url' => $speaker->linkedin_url,
            'topic' => $speaker->topic,
            'category' => $speaker->category,
            'topic_category' => $speaker->topic_category,
            'biography_type' => $speaker->biography_type,
            'resource_url' => $speaker->resource_url,
            'advert_url' => $speaker->advert_url,
            'presentation_file_url' => $speaker->presentation_file_url,
            'show_on_homepage' => $speaker->show_on_homepage ? '1' : '0',
            'show_on_scrollbar' => $speaker->show_on_scrollbar ? '1' : '0',
            'is_key_figure' => $speaker->is_key_figure ? '1' : '0',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.speakers.edit', $speaker))
            ->put(route('admin.speakers.update', $speaker), $payload)
            ->assertRedirect(route('admin.speakers.edit', $speaker))
            ->assertSessionHasErrors('email');
        $this->assertDatabaseHas('speakers', [
            'id' => $speaker->id,
            'email' => 'other@example.com',
        ]);

        $this->assertDatabaseHas('speakers', [
            'id' => $existingSpeaker->id,
            'email' => 'speaker@example.com',
        ]);
    }

    public function test_admin_can_keep_the_same_email_when_updating_a_speaker(): void
    {
        $admin = $this->makeSpeakerAdmin();

        $speaker = Speaker::factory()->create([
            'email' => 'keep@example.com',
        ]);

        $payload = $this->basePayload([
            'email' => 'keep@example.com',
            'name' => $speaker->name,
            'designation' => $speaker->designation,
            'bio' => $speaker->bio,
            'photo_url' => $speaker->photo_url,
            'gender' => $speaker->gender,
            'speaker_role' => $speaker->speaker_role ?? 'speaker',
            'is_active' => $speaker->is_active ? '1' : '0',
            'sort_order' => $speaker->sort_order,
            'company_name' => $speaker->company_name,
            'country' => $speaker->country,
            'phone' => $speaker->phone,
            'show_contact_details' => $speaker->show_contact_details ? '1' : '0',
            'linkedin_url' => $speaker->linkedin_url,
            'topic' => $speaker->topic,
            'category' => $speaker->category,
            'topic_category' => $speaker->topic_category,
            'biography_type' => $speaker->biography_type,
            'resource_url' => $speaker->resource_url,
            'advert_url' => $speaker->advert_url,
            'presentation_file_url' => $speaker->presentation_file_url,
            'show_on_homepage' => $speaker->show_on_homepage ? '1' : '0',
            'show_on_scrollbar' => $speaker->show_on_scrollbar ? '1' : '0',
            'is_key_figure' => $speaker->is_key_figure ? '1' : '0',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.speakers.update', $speaker), $payload)
            ->assertRedirect(route('admin.speakers.index'));

        $this->assertDatabaseHas('speakers', [
            'id' => $speaker->id,
            'email' => 'keep@example.com',
        ]);
    }

    public function test_speaker_email_is_normalized_before_storage(): void
    {
        $admin = $this->makeSpeakerAdmin();

        $payload = $this->basePayload([
            'email' => '  MixedCase@Example.com  ',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.speakers.store'), $payload)
            ->assertRedirect(route('admin.speakers.index'));

        $this->assertDatabaseHas('speakers', [
            'email' => 'mixedcase@example.com',
        ]);
    }

    public function test_database_unique_index_blocks_duplicate_email_inserts(): void
    {
        Speaker::factory()->create([
            'email' => 'duplicate@example.com',
        ]);

        $this->expectException(QueryException::class);

        Speaker::query()->create([
            'name' => 'Duplicate Speaker',
            'speaker_role' => 'speaker',
            'is_active' => true,
            'sort_order' => 1,
            'gender' => 'male',
            'biography_type' => 'text',
            'show_contact_details' => false,
            'show_on_homepage' => false,
            'show_on_scrollbar' => false,
            'is_key_figure' => false,
            'show_on_current_conference' => false,
            'show_on_future_conference' => false,
            'email' => 'duplicate@example.com',
        ]);
    }

    private function basePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Speaker One',
            'designation' => 'Chief Architect',
            'bio' => 'Speaker bio',
            'photo_url' => '',
            'gender' => 'female',
            'speaker_role' => 'speaker',
            'is_active' => '1',
            'sort_order' => 10,
            'company_name' => 'Nova Labs',
            'country' => 'India',
            'email' => 'unique@example.com',
            'phone' => '9999999999',
            'show_contact_details' => '0',
            'linkedin_url' => 'https://linkedin.com/in/speaker-one',
            'topic' => 'AI governance',
            'category' => 'Keynote',
            'topic_category' => 'Artificial Intelligence',
            'biography_type' => 'text',
            'resource_url' => '',
            'advert_url' => '',
            'presentation_file_url' => '',
            'show_on_homepage' => '0',
            'show_on_scrollbar' => '0',
            'is_key_figure' => '0',
        ], $overrides);
    }

    private function makeSpeakerAdmin(): User
    {
        $role = Role::create(['title' => 'Speaker Admin']);
        Permission::insertOrIgnore([
            ['title' => 'speaker_access'],
            ['title' => 'speaker_create'],
            ['title' => 'speaker_edit'],
        ]);

        $permissionIds = Permission::whereIn('title', [
            'speaker_access',
            'speaker_create',
            'speaker_edit',
        ])->pluck('id');

        $role->permissions()->sync($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        return $admin;
    }
}
