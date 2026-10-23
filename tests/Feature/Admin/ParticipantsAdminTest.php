<?php

namespace Tests\Feature\Admin;

use App\Mail\HtmlBodyMail;
use App\Mail\PlainTextMail;
use App\Models\Participant;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ParticipantsAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_approve_a_participant(): void
    {
        $role = Role::create(['title' => 'Participant Admin']);
        Permission::insertOrIgnore([
            ['title' => 'participant_access'],
            ['title' => 'participant_show'],
            ['title' => 'participant_approve'],
        ]);

        $permissionIds = Permission::whereIn('title', [
            'participant_access',
            'participant_show',
            'participant_approve',
        ])->pluck('id');

        $role->permissions()->sync($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        $user = User::factory()->create();
        $participant = Participant::factory()->forUser($user)->create([
            'is_approved' => false,
        ]);
        Mail::fake();

        $this->actingAs($admin)
            ->get(route('admin.participants.index'))
            ->assertOk()
            ->assertSee('Participants');

        $this->actingAs($admin)
            ->patch(route('admin.participants.update', $participant), [
                'is_approved' => '1',
            ])
            ->assertRedirect(route('admin.participants.index'));

        $this->assertDatabaseHas('participants', [
            'id' => $participant->id,
            'is_approved' => true,
        ]);

        Mail::assertQueued(HtmlBodyMail::class, function (HtmlBodyMail $mail) use ($user): bool {
            return $mail->hasTo($user->email);
        });
        Mail::assertNotQueued(PlainTextMail::class);
    }

    public function test_participants_index_renders_existing_participant_rows_without_missing_edit_route(): void
    {
        $role = Role::create(['title' => 'Participant Admin']);
        Permission::insertOrIgnore([
            ['title' => 'participant_access'],
            ['title' => 'participant_show'],
            ['title' => 'participant_approve'],
        ]);

        $permissionIds = Permission::whereIn('title', [
            'participant_access',
            'participant_show',
            'participant_approve',
        ])->pluck('id');

        $role->permissions()->sync($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        $user = User::factory()->create();
        $participant = Participant::factory()->forUser($user)->create([
            'name' => 'Visible Participant',
            'email' => 'visible@example.com',
            'is_approved' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.participants.index'))
            ->assertOk()
            ->assertSee($participant->name)
            ->assertSee(route('admin.participants.show', $participant), false);
    }

    public function test_user_without_permission_cannot_access_participant_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.participants.index'))
            ->assertForbidden();
    }
}
