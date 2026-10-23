<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TrainOwnerAuthController extends Controller
{
    public function showLogin()
    {
        return view('train-owner.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'The supplied credentials are invalid or not approved for train owner access.']);
        }

        $request->session()->regenerate();
        $user = $request->user();

        if (! $user->hasApprovedTrainOwnerAccess()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages(['email' => 'The supplied credentials are invalid or not approved for train owner access.']);
        }

        $request->session()->put('train_owner_session', true);

        return redirect()->route('train-owner.trains.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('train-owner.login');
    }
}
