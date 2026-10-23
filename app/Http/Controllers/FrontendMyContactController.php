<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMyContactSubmissionRequest;
use App\Models\Country;
use App\Models\MyContact;
use App\Services\AdminNotificationMailer;
use App\Support\MyContactPageSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class FrontendMyContactController extends Controller
{
    public function index(Request $request)
    {
        $query = MyContact::query()
            ->where('is_published', true)
            ->whereIn('contact_status', [
                MyContact::STATUS_DEFAULT,
                MyContact::STATUS_KNOWN,
            ]);

        foreach (['country', 'category', 'specialization', 'experience'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->input('is_key_figure') === '1') {
            $query->where('is_whitelisted', true);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%")
                    ->orWhere('experience', 'like', "%{$search}%");
            });
        }

        $contacts = $query
            ->orderByDesc('is_whitelisted')
            ->orderByDesc('stars')
            ->orderBy('name')
            ->paginate(40)
            ->appends($request->query());

        $countries = MyContact::query()
            ->where('is_published', true)
            ->whereIn('contact_status', [MyContact::STATUS_DEFAULT, MyContact::STATUS_KNOWN])
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        $categories = MyContact::query()
            ->where('is_published', true)
            ->whereIn('contact_status', [MyContact::STATUS_DEFAULT, MyContact::STATUS_KNOWN])
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $specializations = MyContact::query()
            ->where('is_published', true)
            ->whereIn('contact_status', [MyContact::STATUS_DEFAULT, MyContact::STATUS_KNOWN])
            ->whereNotNull('specialization')
            ->where('specialization', '!=', '')
            ->distinct()
            ->orderBy('specialization')
            ->pluck('specialization');

        $experiences = MyContact::query()
            ->where('is_published', true)
            ->whereIn('contact_status', [MyContact::STATUS_DEFAULT, MyContact::STATUS_KNOWN])
            ->whereNotNull('experience')
            ->where('experience', '!=', '')
            ->distinct()
            ->orderBy('experience')
            ->pluck('experience');

        $countryCodeMap = Country::query()
            ->whereNotNull('short_code')
            ->where('short_code', '!=', '')
            ->pluck('short_code', 'name')
            ->mapWithKeys(function ($code, $name) {
                return [mb_strtolower(trim((string) $name)) => strtoupper(trim((string) $code))];
            });

        $pageSettings = MyContactPageSettings::get();

        return view('frontend.myContacts.index', compact(
            'contacts',
            'countries',
            'categories',
            'specializations',
            'experiences',
            'countryCodeMap',
            'pageSettings'
        ));
    }

    public function create()
    {
        $left = random_int(1, 9);
        $right = random_int(1, 9);
        $question = "{$left} + {$right} = ?";

        Session::put('my_contact_captcha_answer', (string) ($left + $right));
        Session::put('my_contact_captcha_question', $question);

        $countries = Country::query()
            ->select(['name'])
            ->orderBy('name')
            ->pluck('name');

        $categories = MyContact::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $specializations = MyContact::query()
            ->whereNotNull('specialization')
            ->where('specialization', '!=', '')
            ->distinct()
            ->orderBy('specialization')
            ->pluck('specialization');

        $pageSettings = MyContactPageSettings::get();

        return view('frontend.myContacts.create', compact(
            'pageSettings',
            'countries',
            'categories',
            'specializations',
            'question'
        ));
    }

    public function checkOfficialEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'official_email' => ['required', 'email', 'max:255'],
        ]);

        $email = mb_strtolower(trim((string) $validated['official_email']));
        $exists = MyContact::query()
            ->whereRaw('LOWER(TRIM(official_email)) = ?', [$email])
            ->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists
                ? 'This email already exists.'
                : 'This email is available.',
        ]);
    }

    public function store(StoreMyContactSubmissionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $pageSettings = MyContactPageSettings::get();
        $notificationEmail = trim((string) ($pageSettings['notification_email'] ?? ''));
        $photoUrl = '';

        if ($request->hasFile('photo_file')) {
            $image = $request->file('photo_file');
            $directory = public_path('uploads/my-contact-submissions');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($image->getClientOriginalExtension());
            $fileName = 'my-contact-submission-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $image->move($directory, $fileName);
            $photoUrl = 'uploads/my-contact-submissions/' . $fileName;
        }

        app(AdminNotificationMailer::class)->send(
            'my_contact_submission',
            [
                'full_name' => $validated['full_name'] ?? '',
                'designation' => $validated['designation'] ?? '',
                'nationality' => $validated['nationality'] ?? '',
                'category' => $validated['category'] ?? '',
                'gender' => $validated['gender'] ?? '',
                'photo_url' => $photoUrl,
                'company_name' => $validated['company_name'] ?? '',
                'area_of_expertise' => $validated['area_of_expertise'] ?? '',
                'official_email' => $validated['official_email'] ?? '',
                'alternate_email' => $validated['alternate_email'] ?? '',
                'mobile_whatsapp_number' => $validated['mobile_whatsapp_number'] ?? '',
                'linkedin_url' => $validated['linkedin_url'] ?? '',
                'website_url' => $validated['website_url'] ?? '',
                'publish_basic_details' => (bool) ($validated['publish_basic_details'] ?? false),
                'publish_contact_details' => (bool) ($validated['publish_contact_details'] ?? false),
                'confirm_accuracy' => true,
                'grant_permission' => true,
            ],
            [],
            $notificationEmail !== '' ? [$notificationEmail] : null
        );

        Session::forget('my_contact_captcha_answer');
        Session::forget('my_contact_captcha_question');

        return redirect()
            ->route('frontend.my-contacts.create')
            ->with('message', 'Thank you for submitting your details. Our team will review your profile and contact you if any further information is required.');
    }
}
