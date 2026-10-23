<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\AgendaPageSettings;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AgendaPageSettingsController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $settings = AgendaPageSettings::get();
        $events = Event::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->pluck('title', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        return view('admin.agenda.page-settings', compact('settings', 'events'));
    }

    public function update(Request $request)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'subtitle' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'small_description' => ['nullable', 'string'],
            'agenda_download_text' => ['required', 'string', 'max:100'],
            'agenda_download_url' => ['nullable', 'string', 'max:255'],
            'agenda_download_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,doc,docx,ppt,pptx', 'max:20480'],
        ]);

        if ($request->hasFile('agenda_download_file')) {
            $validated['agenda_download_url'] = $this->storeAgendaDownloadFile($request->file('agenda_download_file'));
        }

        unset($validated['agenda_download_file']);

        AgendaPageSettings::set($validated);

        return redirect()
            ->route('admin.agenda.page-settings.edit')
            ->with('status', trans('global.saved'));
    }

    private function storeAgendaDownloadFile($file): string
    {
        $directory = public_path('uploads/agenda');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = Str::lower($file->getClientOriginalExtension());
        $fileName = 'agenda-download-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
        $file->move($directory, $fileName);

        return 'uploads/agenda/' . $fileName;
    }
}
