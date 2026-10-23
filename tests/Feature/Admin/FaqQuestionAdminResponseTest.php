<?php

namespace Tests\Feature\Admin;

use App\Mail\HtmlBodyMail;
use App\Models\FaqQuestion;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FaqQuestionAdminResponseTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_respond_to_faq_question_and_user_receives_response_email(): void
    {
        Mail::fake();

        $admin = $this->createAdminWithFaqAccess();
        $question = FaqQuestion::factory()->create([
            'name' => 'Question User',
            'email' => 'question-user@example.com',
            'question' => 'Will recordings be shared?',
            'status' => FaqQuestion::STATUS_PENDING,
            'answer' => null,
            'answered_at' => null,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.faq-questions.update', $question), [
                'status' => FaqQuestion::STATUS_ANSWERED,
                'answer' => 'Yes, recordings will be shared with registered delegates.',
            ])
            ->assertRedirect(route('admin.faq-questions.index'));

        $this->assertDatabaseHas('faq_questions', [
            'id' => $question->id,
            'status' => FaqQuestion::STATUS_ANSWERED,
            'answer' => 'Yes, recordings will be shared with registered delegates.',
        ]);

        Mail::assertQueued(HtmlBodyMail::class, function (HtmlBodyMail $mail): bool {
            return $mail->hasTo('question-user@example.com');
        });
    }

    private function createAdminWithFaqAccess(): User
    {
        $role = Role::firstOrCreate(['title' => 'FAQ Admin']);
        $permission = Permission::firstOrCreate(['title' => 'faq_access']);
        $role->permissions()->syncWithoutDetaching([$permission->id]);

        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        return $admin;
    }
}

