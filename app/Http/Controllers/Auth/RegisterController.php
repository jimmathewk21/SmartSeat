<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginActivity;
use App\Models\Participant;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminNotificationMailer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    private const PENDING_APPROVAL_TITLE = 'Registration Submitted';
    private const PENDING_APPROVAL_MESSAGE = 'Thank you for sharing your details. We are reviewing the information you provided. If you need help, please contact SmartSeat support.';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        $this->clearTrainAuthFlow(Session::get('train_auth_return_to'));
        $left = random_int(1, 9);
        $right = random_int(1, 9);
        $question = "{$left} + {$right} = ?";

        Session::put('register_captcha_answer', (string) ($left + $right));
        Session::put('register_captcha_question', $question);

        return view('auth.register', [
            'captchaQuestion' => $question,
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'mobile' => ['required', 'string', 'max:40'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'captcha' => [
                'required',
                function ($attribute, $value, $fail): void {
                    $expected = (string) Session::get('register_captcha_answer', '');
                    if ($expected === '' || trim((string) $value) !== $expected) {
                        $fail('Invalid captcha answer.');
                    }
                },
            ],
        ]);
    }

         /**
          * Create a new user instance after a valid registration.
          *
          * @param  array  $data
          * @return \App\User
         */
         protected function create(array $data, bool $trainRegistration = false)
         {
             return DB::transaction(function () use ($data, $trainRegistration) {
                 $user = User::create([
                     'name'     => $data['name'],
                     'company_name' => $data['company_name'],
                     'mobile'   => $data['mobile'],
                     'email'    => $data['email'],
                     'password' => $data['password'],
                 ]);

                Participant::create([
                    'user_id' => $user->id,
                    'name'    => $data['name'],
                    'email'   => $data['email'],
                    'mobile'  => $data['mobile'] ?? null,
                    'is_approved' => false,
                    'approved_at' => null,
                    'train_access_enabled' => $trainRegistration,
                ]);

                $user->forceFill([
                    'verification_status' => false,
                ])->save();

                $defaultRoleId = Role::query()
                    ->where('title', 'User')
                    ->value('id');

                if ($defaultRoleId) {
                    $user->roles()->syncWithoutDetaching([$defaultRoleId]);
                }

                if (! $trainRegistration) {
                    $this->sendAdminApprovalRequestEmail($user);
                }

                 return $user;
             });
         }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $trainRegistration = $this->consumeTrainRegistrationOrigin($request);

        if (! $trainRegistration) {
            $this->clearTrainAuthFlow($request->session()->get('train_auth_return_to'));
        }

        event(new Registered($user = $this->create($request->all(), $trainRegistration)));

        $this->logRegistrationActivity($request, $user);

        $this->registered($request, $user);

        if ($request->wantsJson()) {
            return new JsonResponse([], 201);
        }

        if ($trainRegistration) {
            $searchParameters = $request->session()->get('train_auth_search_parameters', []);
            $continue = $request->session()->get('train_auth_continue', 'search');
            $routeParameters = $continue === 'bookings'
                ? ['continue' => 'bookings']
                : $searchParameters;

            return redirect()->route('frontend.trains.login', $routeParameters)
                ->with('email_prefill', $user->email)
                ->with('train_auth_notice', 'Your account is ready. Sign in to continue with your train booking.');
        }

        return redirect()->route('login')->with([
            'auth_notice_title' => self::PENDING_APPROVAL_TITLE,
            'pending_review_message' => self::PENDING_APPROVAL_MESSAGE,
        ]);
    }

    private function consumeTrainRegistrationOrigin(Request $request): bool
    {
        $expected = $request->session()->pull('train_registration_token');
        $provided = $request->input('train_registration_token');

        return is_string($expected)
            && $expected !== ''
            && is_string($provided)
            && hash_equals($expected, $provided);
    }

    private function clearTrainAuthFlow(mixed $trainReturnTo): void
    {
        if (is_string($trainReturnTo) && Session::get('url.intended') === $trainReturnTo) {
            Session::forget('url.intended');
        }

        Session::forget([
            'train_registration_token',
            'train_auth_flow',
            'train_auth_continue',
            'train_auth_search_parameters',
            'train_auth_return_to',
        ]);
    }

    protected function registered(Request $request, $user)
    {
        Session::forget('register_captcha_answer');
        Session::forget('register_captcha_question');
    }

    private function logRegistrationActivity(Request $request, User $user): void
    {
        LoginActivity::create([
            'user_id' => $user->id,
            'participant_id' => $user->participant?->id,
            'email' => $user->email,
            'status' => 'registered',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'attempted_at' => now(),
        ]);
    }

    private function sendAdminApprovalRequestEmail(User $user): void
    {
        app(AdminNotificationMailer::class)->send('user_registration', [
            'name' => $user->name,
            'company_name' => $user->company_name ?: '-',
            'email' => $user->email,
            'mobile' => $user->mobile ?: '-',
        ]);
    }
}
