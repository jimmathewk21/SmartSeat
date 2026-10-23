<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Services\AdminNotificationMailer;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ParticipantsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('participant_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Participant::with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'approved') {
                $query->where('is_approved', true);
            } elseif ($request->input('status') === 'pending') {
                $query->where('is_approved', false);
            }
        }

        $allowedSorts = ['id', 'approved_at', 'created_at'];
        $sortBy = $request->input('sort_by', 'created_at');
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }
        $sortDir = $request->input('sort_dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $participants = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate(25)
            ->appends($request->query());

        return view('admin.participants.index', compact('participants'));
    }

    public function show(Participant $participant)
    {
        abort_if(Gate::denies('participant_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $participant->load('user');

        return view('admin.participants.show', compact('participant'));
    }

    public function update(Request $request, Participant $participant)
    {
        abort_if(Gate::denies('participant_approve'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'is_approved' => 'required|in:0,1',
        ]);

        $isApproved = $data['is_approved'] === '1';

        $participant->forceFill([
            'is_approved' => $isApproved,
            'approved_at' => $isApproved ? now() : null,
        ])->save();

        if ($participant->user) {
            $participant->user->forceFill([
                'verification_status' => $isApproved,
                'remember_token' => $isApproved ? $participant->user->remember_token : Str::random(60),
            ])->save();
        }

        if ($isApproved && $participant->user?->email) {
            $this->sendApprovalEmail($participant);
        }

        return redirect()->route('admin.participants.index');
    }

    private function sendApprovalEmail(Participant $participant): void
    {
        $user = $participant->user;
        if (! $user || ! $user->email) {
            return;
        }

        app(AdminNotificationMailer::class)->send('user_approval', [
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->mobile ?: '-',
            'phone' => $user->mobile ?: '-',
            'company_name' => $user->company_name ?: '-',
            'login_url' => route('login'),
            'support_email' => (string) config('mail.from.address', ''),
            'approved_at' => now()->format('d-M-Y h:i A'),
        ], [], [$user->email]);
    }
}
