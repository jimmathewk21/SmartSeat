<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFaqQuestionRequest;
use App\Mail\HtmlBodyMail;
use App\Models\FaqQuestion;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class FaqQuestionsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('faq_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = FaqQuestion::query()->select(['id', 'name', 'email', 'question', 'answer', 'status', 'created_at', 'answered_at']);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        $status = (string) $request->input('status', 'pending');
        if (in_array($status, [FaqQuestion::STATUS_PENDING, FaqQuestion::STATUS_ANSWERED], true)) {
            $query->where('status', $status);
        }

        $questions = $query
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(20)
            ->appends($request->query());

        return view('admin.faqQuestions.index', compact('questions'));
    }

    public function edit(FaqQuestion $faqQuestion)
    {
        abort_if(Gate::denies('faq_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.faqQuestions.edit', compact('faqQuestion'));
    }

    public function update(UpdateFaqQuestionRequest $request, FaqQuestion $faqQuestion): RedirectResponse
    {
        abort_if(Gate::denies('faq_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validated();
        $status = (string) ($validated['status'] ?? FaqQuestion::STATUS_PENDING);
        $answer = trim((string) ($validated['answer'] ?? ''));

        if ($status === FaqQuestion::STATUS_ANSWERED && $answer === '') {
            return back()
                ->withErrors(['answer' => 'Answer is required when marking question as answered.'])
                ->withInput();
        }

        $shouldSendResponseMail = $status === FaqQuestion::STATUS_ANSWERED
            && $answer !== ''
            && ($faqQuestion->status !== FaqQuestion::STATUS_ANSWERED || trim((string) $faqQuestion->answer) !== $answer);

        $faqQuestion->status = $status;
        $faqQuestion->answer = $answer !== '' ? $answer : null;
        $faqQuestion->answered_at = $status === FaqQuestion::STATUS_ANSWERED ? now() : null;
        $faqQuestion->save();

        if ($shouldSendResponseMail) {
            $subject = 'Your FAQ question has been answered';
            $body = '<p>Hello ' . e($faqQuestion->name) . ',</p>'
                . '<p>Thank you for contacting us. Our team has responded to your question.</p>'
                . '<p><strong>Your Question:</strong><br>' . nl2br(e($faqQuestion->question)) . '</p>'
                . '<p><strong>Our Response:</strong><br>' . nl2br(e((string) $faqQuestion->answer)) . '</p>'
                . '<p>Regards,<br>IGI Summit Team</p>';

            Mail::to($faqQuestion->email)->queue(new HtmlBodyMail($subject, $body));
        }

        return redirect()
            ->route('admin.faq-questions.index')
            ->with('message', 'Question response updated successfully.');
    }
}
