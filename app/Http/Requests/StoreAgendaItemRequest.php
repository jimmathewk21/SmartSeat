<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StoreAgendaItemRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('event_access');
    }

    public function rules()
    {
        return [
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'agenda_day_id' => ['required', 'integer', 'exists:agenda_days,id'],
            'agenda_room_id' => ['required', 'integer', 'exists:agenda_rooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'topic_description' => ['nullable', 'string'],
            'start_time' => ['nullable', 'string', 'max:50'],
            'end_time' => ['nullable', 'string', 'max:50'],
            'panel_theme' => ['nullable', 'string', 'max:255'],
            'moderator_name' => ['nullable', 'string', 'max:255'],
            'moderator_speaker_id' => ['nullable', 'integer', 'exists:speakers,id'],
            'moderator_ppt_url' => ['nullable', 'string', 'max:255'],
            'moderator_ppt_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,doc,docx,ppt,pptx', 'max:20480'],
            'agenda_file_url' => ['nullable', 'string', 'max:255'],
            'agenda_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,doc,docx,ppt,pptx', 'max:20480'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'speaker_ids' => ['nullable', 'array'],
            'speaker_ids.*' => ['integer', 'exists:speakers,id'],
        ];
    }
}
