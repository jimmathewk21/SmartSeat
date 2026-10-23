<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\MyContact;
use App\Support\MyContactPageSettings;
use Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MyContactsController extends Controller
{
    public function checkEmail(Request $request): JsonResponse
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'my_contact_id' => ['nullable', 'integer', 'exists:my_contacts,id'],
        ]);

        $normalizedEmail = mb_strtolower(trim((string) $validated['email']));
        $myContactId = (int) ($validated['my_contact_id'] ?? 0);

        $exists = MyContact::query()
            ->whereNotNull('official_email')
            ->whereRaw('LOWER(TRIM(official_email)) = ?', [$normalizedEmail])
            ->when($myContactId > 0, function ($query) use ($myContactId) {
                $query->where('id', '!=', $myContactId);
            })
            ->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists
                ? 'This email is already used by another contact.'
                : 'Email is available.',
        ]);
    }

    public function index(Request $request)
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $myContacts = $this->buildFilteredQuery($request)
            ->paginate(20)
            ->appends($request->query());

        return view('admin.myContacts.index', array_merge(
            $this->filterOptions(),
            ['myContacts' => $myContacts]
        ));
    }

    public function create()
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.myContacts.create', $this->filterOptions());
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $this->validateRequest($request);
        $validated['picture_url'] = $this->storePicture($request, null);

        MyContact::create($validated);

        return redirect()->route('admin.my-contacts.index')->with('message', 'My Contact created successfully.');
    }

    public function show(MyContact $myContact)
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.myContacts.show', compact('myContact'));
    }

    public function edit(MyContact $myContact)
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.myContacts.edit', array_merge(
            $this->filterOptions(),
            ['myContact' => $myContact]
        ));
    }

    public function update(Request $request, MyContact $myContact)
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $this->validateRequest($request, $myContact->id);
        $validated['picture_url'] = $this->storePicture($request, $myContact->picture_url);

        $myContact->update($validated);

        return redirect()->route('admin.my-contacts.index')->with('message', 'My Contact updated successfully.');
    }

    public function destroy(MyContact $myContact)
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $myContact->delete();

        return back()->with('message', 'My Contact deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'contact_ids' => ['required', 'array', 'min:1'],
            'contact_ids.*' => ['integer', 'exists:my_contacts,id'],
            'action' => ['required', Rule::in(['delete', 'unsubscribe', 'subscribe', 'mark_known', 'mark_default', 'blacklist', 'publish', 'unpublish'])],
        ]);

        $contacts = MyContact::query()->whereIn('id', $validated['contact_ids']);
        $count = $contacts->count();

        if ($validated['action'] === 'delete') {
            $contacts->delete();
            return back()->with('message', $count . ' contact(s) deleted successfully.');
        }

        $update = match ($validated['action']) {
            'publish' => [
                'is_published' => true,
            ],
            'unpublish' => [
                'is_published' => false,
            ],
            'unsubscribe' => [
                'is_subscribed' => false,
                'is_blacklisted' => false,
                'contact_status' => MyContact::STATUS_UNSUBSCRIBED,
            ],
            'subscribe' => [
                'is_subscribed' => true,
                'is_blacklisted' => false,
                'contact_status' => MyContact::STATUS_DEFAULT,
            ],
            'mark_known' => [
                'is_subscribed' => true,
                'is_blacklisted' => false,
                'contact_status' => MyContact::STATUS_KNOWN,
            ],
            'blacklist' => [
                'is_subscribed' => false,
                'is_blacklisted' => true,
                'contact_status' => MyContact::STATUS_BLACKLISTED,
            ],
            default => [
                'is_subscribed' => true,
                'is_blacklisted' => false,
                'contact_status' => MyContact::STATUS_DEFAULT,
            ],
        };

        $contacts->update($update);

        return back()->with('message', $count . ' contact(s) updated successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $fileName = 'my-contacts-' . now()->format('Ymd-His') . '.csv';
        $rows = $this->buildFilteredQuery($request)->get();

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'name',
                'designation',
                'company_name',
                'gender',
                'official_email',
                'personal_email',
                'alternate_email',
                'mobile_number',
                'whatsapp_number',
                'alternate_mobile_number',
                'country',
                'category',
                'specialization',
                'experience',
                'speaker_tag',
                'role',
                'event_name',
                'event_names',
                'stars',
                'contact_status',
                'is_published',
                'is_subscribed',
                'is_blacklisted',
                'linkedin_profile',
                'web_url',
                'show_email',
                'show_whatsapp',
                'show_linkedin',
                'show_website',
                'show_category',
                'show_expertise',
            ]);

            foreach ($rows as $contact) {
                fputcsv($handle, [
                    $contact->name,
                    $contact->designation,
                    $contact->company_name,
                    $contact->gender,
                    $contact->official_email,
                    $contact->personal_email,
                    $contact->alternate_email,
                    $contact->mobile_number,
                    $contact->whatsapp_number,
                    $contact->alternate_mobile_number,
                    $contact->country,
                    $contact->category,
                    $contact->specialization,
                    $contact->experience,
                    $contact->speaker_tag,
                    $contact->role,
                    $contact->event_name,
                    implode(', ', $contact->event_list),
                    $contact->stars,
                    $contact->contact_status,
                    $contact->is_published ? '1' : '0',
                    $contact->is_subscribed ? '1' : '0',
                    $contact->is_blacklisted ? '1' : '0',
                    $contact->linkedin_profile,
                    $contact->web_url,
                    $contact->show_email ? '1' : '0',
                    $contact->show_whatsapp ? '1' : '0',
                    $contact->show_linkedin ? '1' : '0',
                    $contact->show_website ? '1' : '0',
                    $contact->show_category ? '1' : '0',
                    $contact->show_expertise ? '1' : '0',
                ]);
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request)
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'import_file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $handle = fopen($request->file('import_file')->getRealPath(), 'r');
        if (! $handle) {
            return back()->withErrors(['import_file' => 'Unable to read the import file.']);
        }

        $headers = fgetcsv($handle);
        if (! $headers) {
            fclose($handle);
            return back()->withErrors(['import_file' => 'The import file is empty.']);
        }

        $headers = collect($headers)->map(fn ($header) => Str::snake(trim((string) $header)))->all();
        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($handle, $headers, &$created, &$updated): void {
            while (($row = fgetcsv($handle)) !== false) {
                $rowData = array_combine($headers, array_pad($row, count($headers), null));
                if (! is_array($rowData)) {
                    continue;
                }

                $payload = $this->normalizeImportRow($rowData);
                if (! $payload['official_email'] || ! $payload['name']) {
                    continue;
                }

                $contact = MyContact::query()->where('official_email', $payload['official_email'])->first();
                if ($contact) {
                    $contact->update($payload);
                    $updated++;
                } else {
                    MyContact::query()->create($payload);
                    $created++;
                }
            }
        });

        fclose($handle);

        return back()->with('message', "Import complete. Created: {$created}, Updated: {$updated}.");
    }

    private function buildFilteredQuery(Request $request)
    {
        $query = MyContact::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('official_email', 'like', "%{$search}%")
                    ->orWhere('personal_email', 'like', "%{$search}%")
                    ->orWhere('alternate_email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%")
                    ->orWhere('whatsapp_number', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%")
                    ->orWhere('speaker_tag', 'like', "%{$search}%")
                    ->orWhere('experience', 'like', "%{$search}%")
                    ->orWhere('event_name', 'like', "%{$search}%")
                    ->orWhere('event_names', 'like', "%{$search}%");
            });
        }

        foreach (['category', 'country', 'specialization', 'speaker_tag', 'contact_status', 'experience'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->filled('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        if ($request->filled('event_name')) {
            $eventName = trim((string) $request->input('event_name'));
            $query->where(function ($q) use ($eventName): void {
                $q->where('event_name', $eventName)->orWhere('event_names', 'like', '%' . $eventName . '%');
            });
        }

        if ($request->filled('stars')) {
            $query->where('stars', (int) $request->input('stars'));
        }

        $allowedSorts = ['name', 'company_name', 'country', 'speaker_tag', 'experience', 'event_name', 'stars', 'contact_status', 'is_published', 'created_at'];
        $sortBy = $request->input('sort_by', 'created_at');
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }

        $sortDir = $request->input('sort_dir', 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortDir)->orderBy('name');
    }

    private function filterOptions(): array
    {
        $settings = MyContactPageSettings::get();

        return [
            'categories' => $this->distinctValues('category'),
            'countries' => $this->mergeOptionValues(
                Country::query()
                    ->whereNotNull('name')
                    ->where('name', '!=', '')
                    ->orderBy('name')
                    ->pluck('name'),
                $this->distinctValues('country'),
                $this->settingOptions($settings, 'country_options')
            ),
            'specializations' => $this->mergeOptionValues(
                $this->distinctValues('specialization'),
                $this->settingOptions($settings, 'specialization_options')
            ),
            'experiences' => $this->mergeOptionValues(
                $this->distinctValues('experience'),
                $this->settingOptions($settings, 'experience_options')
            ),
            'speakerTags' => $this->mergeOptionValues(
                $this->distinctValues('speaker_tag'),
                $this->settingOptions($settings, 'speaker_tag_options')
            ),
            'events' => $this->distinctEventValues(),
            'statusOptions' => [
                MyContact::STATUS_DEFAULT => 'Default / Subscribed',
                MyContact::STATUS_UNSUBSCRIBED => 'Unsubscribed',
                MyContact::STATUS_KNOWN => 'Known Contact',
                MyContact::STATUS_BLACKLISTED => 'Blacklisted',
            ],
        ];
    }

    private function distinctValues(string $column): Collection
    {
        return MyContact::query()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column);
    }

    private function distinctEventValues(): Collection
    {
        $eventsFromPrimary = $this->distinctValues('event_name');
        $eventsFromList = MyContact::query()
            ->whereNotNull('event_names')
            ->where('event_names', '!=', '')
            ->pluck('event_names')
            ->flatMap(fn ($eventNames) => $this->extractEventItems((string) $eventNames));

        return $this->mergeOptionValues($eventsFromPrimary, $eventsFromList);
    }

    private function settingOptions(array $settings, string $key): Collection
    {
        return collect(MyContactPageSettings::parseOptionLines($settings[$key] ?? ''));
    }

    private function mergeOptionValues(Collection ...$collections): Collection
    {
        $merged = collect();

        foreach ($collections as $collection) {
            $merged = $merged->merge($collection);
        }

        return $merged
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique(fn (string $value) => mb_strtolower($value))
            ->sortBy(fn (string $value) => mb_strtolower($value), SORT_NATURAL)
            ->values();
    }

    private function validateRequest(Request $request, ?int $ignoreId = null): array
    {
        $input = $request->all();
        $input['linkedin_profile'] = $this->normalizeUrlInput($input['linkedin_profile'] ?? null);
        $input['web_url'] = $this->normalizeUrlInput($input['web_url'] ?? null);
        $eventItems = $this->extractEventItems(trim((string) ($input['event_names'] ?? '')));

        $validator = Validator::make($input, [
            'picture_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'gender' => ['nullable', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'official_email' => ['required', 'email', 'max:255', Rule::unique('my_contacts', 'official_email')->ignore($ignoreId)],
            'personal_email' => ['nullable', 'email', 'max:255'],
            'alternate_email' => ['nullable', 'email', 'max:255'],
            'mobile_number' => ['nullable', 'string', 'max:40'],
            'whatsapp_number' => ['nullable', 'string', 'max:40'],
            'alternate_mobile_number' => ['nullable', 'string', 'max:40'],
            'country' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120'],
            'specialization' => ['nullable', 'string', 'max:120'],
            'experience' => ['nullable', 'string', 'max:120'],
            'role' => ['nullable', 'string', 'max:120'],
            'speaker_tag' => ['nullable', 'string', 'max:120'],
            'event_name' => ['nullable', 'string', 'max:255'],
            'event_names' => ['nullable', 'string'],
            'linkedin_profile' => ['nullable', 'url', 'max:255'],
            'web_url' => ['nullable', 'url', 'max:255'],
            'is_published' => ['nullable', 'boolean'],
            'show_email' => ['nullable', 'boolean'],
            'show_whatsapp' => ['nullable', 'boolean'],
            'show_linkedin' => ['nullable', 'boolean'],
            'show_website' => ['nullable', 'boolean'],
            'show_category' => ['nullable', 'boolean'],
            'show_expertise' => ['nullable', 'boolean'],
            'is_whitelisted' => ['nullable', 'boolean'],
            'contact_status' => ['required', Rule::in([
                MyContact::STATUS_DEFAULT,
                MyContact::STATUS_KNOWN,
                MyContact::STATUS_UNSUBSCRIBED,
                MyContact::STATUS_BLACKLISTED,
            ])],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();
        $normalizedEventItems = $eventItems;

        $validated['is_published'] = $request->boolean('is_published', true);
        $validated['show_email'] = $request->boolean('show_email');
        $validated['show_whatsapp'] = $request->boolean('show_whatsapp');
        $validated['show_linkedin'] = $request->boolean('show_linkedin');
        $validated['show_website'] = $request->boolean('show_website');
        $validated['show_category'] = $request->boolean('show_category');
        $validated['show_expertise'] = $request->boolean('show_expertise');
        $validated['is_whitelisted'] = $request->boolean('is_whitelisted');
        $validated['event_names'] = $normalizedEventItems === [] ? null : implode(PHP_EOL, $normalizedEventItems);
        $validated['event_name'] = trim((string) ($input['event_name'] ?? '')) ?: null;

        if ($validated['contact_status'] === MyContact::STATUS_UNSUBSCRIBED) {
            $validated['is_subscribed'] = false;
            $validated['is_blacklisted'] = false;
        } elseif ($validated['contact_status'] === MyContact::STATUS_KNOWN) {
            $validated['is_subscribed'] = true;
            $validated['is_blacklisted'] = false;
        } elseif ($validated['contact_status'] === MyContact::STATUS_BLACKLISTED) {
            $validated['is_subscribed'] = false;
            $validated['is_blacklisted'] = true;
        } else {
            $validated['is_subscribed'] = true;
            $validated['is_blacklisted'] = false;
        }

        return $validated;
    }

    private function normalizeImportRow(array $row): array
    {
        $status = (string) ($row['contact_status'] ?? '');
        if (! in_array($status, [MyContact::STATUS_DEFAULT, MyContact::STATUS_KNOWN, MyContact::STATUS_UNSUBSCRIBED, MyContact::STATUS_BLACKLISTED], true)) {
            $status = $this->parseBoolean($row['is_subscribed'] ?? null) ? MyContact::STATUS_DEFAULT : MyContact::STATUS_UNSUBSCRIBED;
        }

        $payload = [
            'picture_url' => $row['picture_url'] ?? null,
            'gender' => $row['gender'] ?? null,
            'name' => trim((string) ($row['name'] ?? '')),
            'designation' => $row['designation'] ?? null,
            'company_name' => $row['company_name'] ?? null,
            'official_email' => trim((string) ($row['official_email'] ?? '')),
            'personal_email' => $row['personal_email'] ?? null,
            'alternate_email' => $row['alternate_email'] ?? null,
            'mobile_number' => $row['mobile_number'] ?? null,
            'whatsapp_number' => $row['whatsapp_number'] ?? null,
            'alternate_mobile_number' => $row['alternate_mobile_number'] ?? null,
            'country' => $row['country'] ?? null,
            'category' => $row['category'] ?? null,
            'specialization' => $row['specialization'] ?? null,
            'experience' => $row['experience'] ?? null,
            'role' => $row['role'] ?? null,
            'speaker_tag' => $row['speaker_tag'] ?? null,
            'event_names' => $this->normalizeEventNames(
                trim((string) ($row['event_names'] ?? '') . PHP_EOL . (string) ($row['event_name'] ?? ''))
            ),
            'contact_status' => $status,
            'is_subscribed' => $this->parseBoolean($row['is_subscribed'] ?? null),
            'is_published' => $this->parseBoolean($row['is_published'] ?? '1'),
            'linkedin_profile' => $this->nullableUrl($row['linkedin_profile'] ?? null),
            'web_url' => $this->nullableUrl($row['web_url'] ?? null),
            'is_whitelisted' => $status === MyContact::STATUS_KNOWN,
            'is_blacklisted' => $status === MyContact::STATUS_BLACKLISTED || $this->parseBoolean($row['is_blacklisted'] ?? null),
            'show_email' => $this->parseBoolean($row['show_email'] ?? null),
            'show_whatsapp' => $this->parseBoolean($row['show_whatsapp'] ?? null),
            'show_linkedin' => $this->parseBoolean($row['show_linkedin'] ?? null),
            'show_website' => $this->parseBoolean($row['show_website'] ?? null),
            'show_category' => $this->parseBoolean($row['show_category'] ?? null),
            'show_expertise' => $this->parseBoolean($row['show_expertise'] ?? null),
        ];

        $payload['event_name'] = $this->extractEventItems((string) ($payload['event_names'] ?? ''))[0] ?? null;

        if ($status === MyContact::STATUS_UNSUBSCRIBED) {
            $payload['is_subscribed'] = false;
            $payload['is_blacklisted'] = false;
        } elseif ($status === MyContact::STATUS_KNOWN) {
            $payload['is_subscribed'] = true;
            $payload['is_blacklisted'] = false;
        } elseif ($status === MyContact::STATUS_BLACKLISTED) {
            $payload['is_subscribed'] = false;
            $payload['is_whitelisted'] = false;
            $payload['is_blacklisted'] = true;
        } else {
            $payload['is_subscribed'] = true;
            $payload['is_whitelisted'] = false;
            $payload['is_blacklisted'] = false;
        }

        return $payload;
    }

    private function normalizeUrlInput(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, '//')) {
            return 'https:' . $value;
        }

        if (! preg_match('/^[a-z][a-z0-9+\-.]*:\/\//i', $value)) {
            $candidate = ltrim($value, '/');
            if ($this->looksLikeDomain($candidate)) {
                return 'https://' . $candidate;
            }
        }

        return $value;
    }

    private function nullableUrl(mixed $value): ?string
    {
        $value = $this->normalizeUrlInput($value) ?? '';
        return filter_var($value, FILTER_VALIDATE_URL) ? $value : null;
    }

    private function parseBoolean(mixed $value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'y'], true);
    }

    private function normalizeEventNames(string $value): ?string
    {
        $items = collect($this->extractEventItems($value))
            ->values();

        return $items->isEmpty() ? null : $items->implode(PHP_EOL);
    }

    private function extractEventItems(string $value): array
    {
        return collect(preg_split('/[\r\n,]+/', $value))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique(fn (string $item) => mb_strtolower($item))
            ->values()
            ->all();
    }

    private function looksLikeDomain(string $value): bool
    {
        if ($value === '' || str_contains($value, ' ')) {
            return false;
        }

        $host = parse_url('https://' . $value, PHP_URL_HOST);

        return is_string($host) && str_contains($host, '.');
    }

    private function storePicture(Request $request, ?string $existing): ?string
    {
        if (! $request->hasFile('picture_file')) {
            return $existing;
        }

        $file = $request->file('picture_file');
        $directory = public_path('uploads/my-contacts');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $fileName = 'my-contact-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $fileName);

        return 'uploads/my-contacts/' . $fileName;
    }
}
