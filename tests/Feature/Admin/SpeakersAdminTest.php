<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Speaker;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SpeakersAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_create_speaker_with_topic_category(): void
    {
        $admin = $this->makeSpeakerAdmin();

        $payload = [
            'name' => 'Asha Mehta',
            'designation' => 'Chief Architect',
            'bio' => 'Speaker bio',
            'photo_url' => '',
            'gender' => 'female',
            'speaker_role' => 'speaker',
            'is_active' => '1',
            'sort_order' => 10,
            'company_name' => 'Nova Labs',
            'country' => 'India',
            'email' => 'asha@example.com',
            'phone' => '9999999999',
            'show_contact_details' => '0',
            'linkedin_url' => 'https://linkedin.com/in/asha-mehta',
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
        ];

        $this->actingAs($admin)
            ->post(route('admin.speakers.store'), $payload)
            ->assertRedirect(route('admin.speakers.index'));

        $this->assertDatabaseHas('speakers', [
            'name' => 'Asha Mehta',
            'topic_category' => 'Artificial Intelligence',
            'category' => 'Keynote',
            'speaker_role' => 'speaker',
        ]);
    }

    public function test_admin_can_update_speaker_topic_category(): void
    {
        $admin = $this->makeSpeakerAdmin();
        $speaker = Speaker::factory()->create([
            'name' => 'Update Candidate',
            'topic_category' => 'Old Topic Category',
        ]);

        $payload = [
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
            'email' => $speaker->email,
            'phone' => $speaker->phone,
            'show_contact_details' => $speaker->show_contact_details ? '1' : '0',
            'linkedin_url' => $speaker->linkedin_url,
            'topic' => $speaker->topic,
            'category' => $speaker->category,
            'topic_category' => 'New Topic Category',
            'biography_type' => $speaker->biography_type,
            'resource_url' => $speaker->resource_url,
            'advert_url' => $speaker->advert_url,
            'presentation_file_url' => $speaker->presentation_file_url,
            'show_on_homepage' => $speaker->show_on_homepage ? '1' : '0',
            'show_on_scrollbar' => $speaker->show_on_scrollbar ? '1' : '0',
            'is_key_figure' => $speaker->is_key_figure ? '1' : '0',
        ];

        $this->actingAs($admin)
            ->put(route('admin.speakers.update', $speaker), $payload)
            ->assertRedirect(route('admin.speakers.index'));

        $this->assertDatabaseHas('speakers', [
            'id' => $speaker->id,
            'topic_category' => 'New Topic Category',
        ]);
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
