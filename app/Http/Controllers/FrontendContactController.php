<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Services\AdminNotificationMailer;
use App\Support\ContactPageSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class FrontendContactController extends Controller
{
    public function index()
    {
        $left = random_int(1, 9);
        $right = random_int(1, 9);
        $question = "{$left} + {$right} = ?";

        Session::put('contact_captcha_answer', (string) ($left + $right));
        Session::put('contact_captcha_question', $question);

        $settings = ContactPageSettings::get();
        $testimonials = Testimonial::query()
            ->where('is_active', true)
            ->where('show_on_scrollbar', true)
            ->orderBy('sort_order')
            ->orderByDesc('testimonial_date')
            ->orderBy('id')
            ->limit(10)
            ->get();

        return view('frontend.contact.index', compact('settings', 'testimonials', 'question'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:2000'],
            'captcha' => [
                'required',
                function ($attribute, $value, $fail): void {
                    $expected = (string) session('contact_captcha_answer', '');
                    if ($expected === '' || trim((string) $value) !== $expected) {
                        $fail('Invalid captcha answer.');
                    }
                },
            ],
        ]);

        app(AdminNotificationMailer::class)->send('contact_submission', [
            'name' => (string) $request->input('name'),
            'email' => (string) $request->input('email'),
            'subject' => (string) $request->input('subject'),
            'message' => (string) $request->input('message'),
        ]);

        Session::forget('contact_captcha_answer');
        Session::forget('contact_captcha_question');

        $successMessage = ContactPageSettings::get()['form_success_message']
            ?? 'Thank you for reaching out. Our team will contact you shortly.';

        return redirect()
            ->route('frontend.contact.index')
            ->with('message', $successMessage);
    }
}
