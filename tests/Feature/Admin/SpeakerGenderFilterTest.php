<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Speaker;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SpeakerGenderFilterTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_speaker_index_filters_by_gender(): void
    {
        $admin = $this->makeSpeakerAdmin();

        $maleSpeaker = Speaker::factory()->create([
            'name' => 'Male Speaker',
            'gender' => 'male',
            'is_active' => true,
        ]);

        $femaleSpeaker = Speaker::factory()->create([
            'name' => 'Female Speaker',
            'gender' => 'female',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.speakers.index', ['speaker_type' => 'male']))
            ->assertOk()
            ->assertSee('Gender', false)
            ->assertSee($maleSpeaker->name)
            ->assertDontSee($femaleSpeaker->name);

        $this->actingAs($admin)
            ->get(route('admin.speakers.index', ['speaker_type' => 'female']))
            ->assertOk()
            ->assertSee('Gender', false)
            ->assertSee($femaleSpeaker->name)
            ->assertDontSee($maleSpeaker->name);
    }

    private function makeSpeakerAdmin(): User
    {
        $role = Role::create(['title' => 'Speaker Admin']);
        Permission::insertOrIgnore([
            ['title' => 'speaker_access'],
        ]);

        $permissionIds = Permission::whereIn('title', [
            'speaker_access',
        ])->pluck('id');

        $role->permissions()->sync($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        return $admin;
    }
}
