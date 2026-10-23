<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpeakerApplication;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SpeakerApplicationsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('speaker_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = SpeakerApplication::query()->with('reviewer:id,name');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search): void {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('official_email', 'like', "%{$search}%")
                    ->orWhere('organization', 'like', "%{$search}%")
                    ->orWhere('proposed_topic_title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $applications = $query
            ->orderByDesc('id')
            ->paginate(20)
            ->appends($request->query());

        return view('admin.speakerApplications.index', compact('applications'));
    }

    public function show(SpeakerApplication $speakerApplication)
    {
        abort_if(Gate::denies('speaker_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $speakerApplication->load('reviewer:id,name');

        return view('admin.speakerApplications.show', compact('speakerApplication'));
    }

    public function update(Request $request, SpeakerApplication $speakerApplication): RedirectResponse
    {
        abort_if(Gate::denies('speaker_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $speakerApplication->update([
            'status' => $validated['status'],
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        return redirect()->route('admin.speaker-applications.show', $speakerApplication)
            ->with('message', 'Application status updated.');
    }

    public function destroy(SpeakerApplication $speakerApplication): RedirectResponse
    {
        abort_if(Gate::denies('speaker_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $speakerApplication->delete();

        return redirect()->route('admin.speaker-applications.index')->with('message', 'Application deleted.');
    }
}

