<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginActivity;
use App\Models\User;
use App\Services\AdminNotificationMailer;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    private const PENDING_APPROVAL_TITLE = 'Registration Submitted';
    private const PENDING_APPROVAL_MESSAGE = 'Thank you for sharing your details. We are reviewing the information you provided. If you need help, please contact SmartSeat support.';

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        $trainAccessLogin = $user->participant
            && ! $user->participant->is_approved
            && $user->participant->train_access_enabled
            && $request->session()->get('train_auth_flow') === true;

        if ($user->participant && ! $user->participant->is_approved && ! $trainAccessLogin) {
            $this->logAttempt($request, 'pending', $user);
            $trainAuthFlow = $request->session()->get('train_auth_flow') === true;
            $trainReturnTo = $request->session()->get('train_auth_return_to', route('frontend.trains.search', [], false));
            $trainSearchParameters = $request->session()->get('train_auth_search_parameters', []);
            $trainContinue = $request->session()->get('train_auth_continue', 'search');

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($trainAuthFlow) {
                $request->session()->put([
                    'train_auth_flow' => true,
                    'train_auth_return_to' => $trainReturnTo,
                    'train_auth_search_parameters' => $trainSearchParameters,
                    'train_auth_continue' => $trainContinue,
                    'url.intended' => $trainReturnTo,
                ]);
                $request->session()->flash('email_prefill', $user->email);

                return redirect()->route('frontend.trains.login', $trainContinue === 'bookings'
                    ? ['continue' => 'bookings']
                    : $trainSearchParameters
                )->with('train_auth_notice', 'This account is awaiting administrator approval. Booking access will be available after approval.');
            }

            $request->session()->flash('auth_notice_title', self::PENDING_APPROVAL_TITLE);
            $request->session()->flash('pending_review_message', self::PENDING_APPROVAL_MESSAGE);
            $request->session()->flash('email_prefill', $user->email);

            return redirect()->route('login');
        }

        if ($user->participant) {
            $user->participant->forceFill([
                'last_login_ip' => $request->ip(),
                'last_login_at' => now(),
            ])->save();
        }

        if ($request->session()->get('train_auth_flow') === true) {
            $request->session()->forget([
                'train_auth_flow',
                'train_auth_return_to',
                'train_auth_search_parameters',
                'train_auth_continue',
            ]);
        }

        $this->logAttempt($request, 'success', $user);

        if ($user->is_admin) {
            app(AdminNotificationMailer::class)->send('admin_login', [
                'admin_name' => $user->name,
                'admin_email' => $user->email,
                'login_ip' => $request->ip() ?: '-',
                'login_at' => now()->format('d-M-Y h:i A'),
                'user_agent' => $request->userAgent() ?: '-',
            ]);
        }
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $this->logAttempt($request, 'failed');

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    protected function logAttempt(Request $request, string $status, ?User $user = null): void
    {
        $email = (string) $request->input($this->username());

        if (! $user && $email !== '') {
            $user = User::where('email', $email)->with('participant')->first();
        }

        LoginActivity::create([
            'user_id'        => $user?->id,
            'participant_id' => null,
            'email'          => $email !== '' ? $email : null,
            'status'         => $status,
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
            'attempted_at'   => now(),
        ]);
    }

    public function redirectTo()
    {
        $user = auth()->user();

        if ($user && $user->is_admin) {
            return route('admin.home');
        }

        return route('frontend.trains.home');
    }
}
