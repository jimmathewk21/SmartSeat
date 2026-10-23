<?php

namespace App\Http\Controllers;

use App\Mail\HtmlBodyMail;
use App\Models\FaqCategory;
use App\Models\FaqQuestion;
use App\Services\AdminNotificationMailer;
use App\Support\FaqPageSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class FrontendFaqController extends Controller
{
    public function index()
    {
        $pageSettings = FaqPageSettings::get();

        $faqCategories = FaqCategory::query()
            ->select(['id', 'name', 'sort_order'])
            ->where('is_active', true)
            ->with(['faqs' => function ($query) {
                $query->select(['id', 'faq_category_id', 'question', 'answer', 'sort_order'])
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id');
            }])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(fn (FaqCategory $category) => $category->faqs->isNotEmpty())
            ->values();

        return view('frontend.faqs.index', compact('faqCategories', 'pageSettings'));
    }

    public function storeQuestion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'question' => ['required', 'string', 'max:2000'],
        ]);

        FaqQuestion::create([
            'name' => (string) $validated['name'],
            'email' => (string) $validated['email'],
            'question' => (string) $validated['question'],
            'status' => FaqQuestion::STATUS_PENDING,
        ]);

        app(AdminNotificationMailer::class)->send('contact_submission', [
            'name' => (string) $validated['name'],
            'email' => (string) $validated['email'],
            'subject' => 'FAQ Question',
            'message' => (string) $validated['question'],
        ]);

        $subject = 'We have received your question';
        $body = '<p>Hello ' . e((string) $validated['name']) . ',</p>'
            . '<p>Thank you for submitting your question on the FAQ page.</p>'
            . '<p>Our team has received it and will respond shortly.</p>'
            . '<p><strong>Your Question:</strong><br>' . nl2br(e((string) $validated['question'])) . '</p>'
            . '<p>Regards,<br>SmartSeat Support</p>';
        try {
            Mail::to((string) $validated['email'])->queue(new HtmlBodyMail($subject, $body));
        } catch (Throwable $exception) {
            // Keep FAQ submission successful even if SMTP is temporarily unavailable.
        }

        return redirect()
            ->route('frontend.faqs.index')
            ->with('faq_question_submitted', true)
            ->with('faq_question_message', 'Your question has been submitted successfully. We have sent a confirmation email.');
    }
}
