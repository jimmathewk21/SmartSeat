<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Requests\Api\V1\Auth\UpdateProfileRequest;
use App\Models\LoginActivity;
use App\Models\Participant;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminNotificationMailer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    private const PENDING_APPROVAL_TITLE = 'Registration Submitted';

    private const PENDING_APPROVAL_MESSAGE = 'Thank you for sharing your details. We are reviewing the information you provided. If you need help, please contact SmartSeat support.';

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()
            ->with('participant')
            ->where('email', $request->input('email'))
            ->first();

        if (! $user || ! password_verify($request->input('password'), (string) $user->password)) {
            $this->logAttempt($request, 'failed', $user);

            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        if ($this->isBlockedParticipant($user)) {
            $this->logAttempt($request, 'pending', $user);

            throw ValidationException::withMessages([
                'email' => [self::PENDING_APPROVAL_MESSAGE],
            ]);
        }

        $this->touchParticipantLogin($request, $user);
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

        return response()->json([
            'token' => $user->createToken('mobile-app')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $user->fresh()->load('participant'),
        ]);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => trim($request->input('first_name') . ' ' . $request->input('last_name')),
                'company_name' => $request->input('company_name'),
                'mobile' => $request->input('mobile'),
                'email' => $request->input('email'),
                'password' => $request->input('password'),
            ]);

            Participant::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'is_approved' => false,
                'approved_at' => null,
            ]);

            $defaultRoleId = Role::query()
                ->where('title', 'User')
                ->value('id');

            if ($defaultRoleId) {
                $user->roles()->syncWithoutDetaching([$defaultRoleId]);
            }

            app(AdminNotificationMailer::class)->send('user_registration', [
                'name' => $user->name,
                'company_name' => $user->company_name ?: '-',
                'email' => $user->email,
                'mobile' => $user->mobile ?: '-',
            ]);

            $participant = $user->participant()->first();

            LoginActivity::create([
                'user_id' => $user->id,
                'participant_id' => $participant?->id,
                'email' => $user->email,
                'status' => 'registered',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'attempted_at' => now(),
            ]);

            event(new Registered($user));

            return $user->load('participant');
        });

        return response()->json([
            'message' => self::PENDING_APPROVAL_MESSAGE,
            'notice_title' => self::PENDING_APPROVAL_TITLE,
            'user' => $user,
        ], Response::HTTP_CREATED);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()?->load('participant'),
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        $validated = $request->validated();
        $originalCompanyName = (string) ($user->company_name ?? '');

        $user->forceFill([
            'name' => trim((string) $validated['name']),
            'mobile' => array_key_exists('mobile', $validated)
                ? $this->normalizeNullableString($validated['mobile'])
                : $user->mobile,
            'company_name' => array_key_exists('company_name', $validated)
                ? $this->normalizeNullableString($validated['company_name'])
                : $user->company_name,
            'profile_picture' => array_key_exists('profile_picture', $validated)
                ? $this->normalizeNullableString($validated['profile_picture'])
                : $user->profile_picture,
        ])->save();

        if ($user->participant) {
            $user->participant->forceFill([
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
            ])->save();
        }

        $changedFields = [];

        if ($originalCompanyName !== (string) ($user->company_name ?? '')) {
            $changedFields[] = 'Company Name';
        }

        if ($changedFields !== []) {
            app(AdminNotificationMailer::class)->send('user_profile_updated', [
                'name' => $user->name,
                'email' => $user->email,
                'company_name' => $user->company_name ?: '-',
                'changed_fields' => $changedFields,
                'updated_at' => now()->format('d-M-Y h:i A'),
            ]);
        }

        return response()->json([
            'message' => __('global.update_profile_success'),
            'user' => $user->fresh()->load('participant'),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::broker()->sendResetLink([
            'email' => $request->input('email'),
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }

    private function isBlockedParticipant(User $user): bool
    {
        return (bool) $user->participant && ! $user->participant->is_approved;
    }

    private function touchParticipantLogin(Request $request, User $user): void
    {
        if (! $user->participant) {
            return;
        }

        $user->participant->forceFill([
            'last_login_ip' => $request->ip(),
            'last_login_at' => now(),
        ])->save();
    }

    private function logAttempt(Request $request, string $status, ?User $user = null): void
    {
        $email = (string) $request->input('email');

        LoginActivity::create([
            'user_id' => $user?->id,
            'participant_id' => $user?->participant?->id,
            'email' => $email !== '' ? $email : null,
            'status' => $status,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'attempted_at' => now(),
        ]);
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
