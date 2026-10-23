<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\AdminNotificationMailer;

class ChangePasswordController extends Controller
{
    public function edit()
    {
        abort_if(! auth()->check(), 403, '403 Forbidden');

        return view('auth.passwords.edit');
    }

    public function update(UpdatePasswordRequest $request)
    {
        auth()->user()->update($request->validated());

        return redirect()->route('profile.password.edit')->with('message', __('global.change_password_success'));
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = auth()->user();
        $validated = $request->validated();
        $originalCompanyName = (string) ($user->company_name ?? '');

        $user->update([
            'company_name' => $validated['company_name'] ?? null,
        ]);

        if ($user->participant) {
            $user->participant->forceFill([
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
            ])->save();
        }

        $changedFields = $originalCompanyName !== (string) ($user->company_name ?? '')
            ? ['Company Name']
            : [];

        if ($changedFields !== []) {
            app(AdminNotificationMailer::class)->send('user_profile_updated', [
                'name' => $user->name,
                'email' => $user->email,
                'company_name' => $user->company_name ?: '-',
                'changed_fields' => $changedFields,
                'updated_at' => now()->format('d-M-Y h:i A'),
            ]);
        }

        return redirect()->route('profile.password.edit')->with('message', __('global.update_profile_success'));
    }

    public function destroy()
    {
        $user = auth()->user();

        $user->update([
            'email' => time() . '_' . $user->email,
        ]);

        $user->delete();

        return redirect()->route('login')->with('message', __('global.delete_account_success'));
    }
}
