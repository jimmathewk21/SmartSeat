<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\AgendaPageSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AgendaPageSettingsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_upload_agenda_download_file_on_agenda_page_settings(): void
    {
        $admin = $this->createAgendaAdmin();

        $response = $this->actingAs($admin)->put(route('admin.agenda.page-settings.update'), [
            'event_id' => null,
            'subtitle' => 'Schedule Details',
            'title' => 'Information of Event Schedules',
            'small_description' => 'Agenda upload test.',
            'agenda_download_text' => 'Download Agenda',
            'agenda_download_url' => '',
            'agenda_download_file' => UploadedFile::fake()->create('agenda.pdf', 120),
        ]);

        $response->assertRedirect(route('admin.agenda.page-settings.edit'));

        $settings = AgendaPageSettings::get();

        $this->assertStringStartsWith('uploads/agenda/agenda-download-', $settings['agenda_download_url']);
        $this->assertStringEndsWith('.pdf', $settings['agenda_download_url']);
        $this->assertFileExists(public_path($settings['agenda_download_url']));

        @unlink(public_path($settings['agenda_download_url']));
    }

    public function test_admin_rejects_unsupported_agenda_download_file_types(): void
    {
        $admin = $this->createAgendaAdmin();

        $this->actingAs($admin)
            ->put(route('admin.agenda.page-settings.update'), [
                'event_id' => null,
                'subtitle' => 'Schedule Details',
                'title' => 'Information of Event Schedules',
                'small_description' => 'Agenda upload test.',
                'agenda_download_text' => 'Download Agenda',
                'agenda_download_url' => '',
                'agenda_download_file' => UploadedFile::fake()->create('agenda.exe', 12),
            ])
            ->assertSessionHasErrors(['agenda_download_file']);
    }

    private function createAgendaAdmin(): User
    {
        $role = Role::firstOrCreate(['title' => 'Agenda Page Admin']);
        Permission::insertOrIgnore([
            ['title' => 'event_access'],
        ]);

        $permissionIds = Permission::whereIn('title', ['event_access'])->pluck('id');
        $role->permissions()->sync($permissionIds);

        $user = User::factory()->create();
        $user->roles()->sync([$role->id]);

        return $user;
    }
}
