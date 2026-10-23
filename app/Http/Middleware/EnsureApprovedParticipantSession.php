<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureApprovedParticipantSession
{
    private const REVOKED_ACCESS_MESSAGE = 'Your access is no longer active. Please contact support for assistance.';

    private const TRAIN_ROUTES = [
        'frontend.trains.home',
        'frontend.trains.legacy-home',
        'frontend.trains.search',
        'frontend.trains.login',
        'frontend.trains.register',
        'frontend.trains.bookings',
        'frontend.trains.book',
        'frontend.trains.waitlist',
        'frontend.trains.cancel',
        'frontend.trains.waitlist.cancel',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (
            $user
    && $request->session()->get('train_owner_session') === true
            && (! $request->routeIs('train-owner.*') || ! $user->hasApprovedTrainOwnerAccess())
        ) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('train-owner.login');
        }

        if (
            $user
            && ! $user->is_admin
            && $user->participant
            && ! $user->participant->is_approved
            && ! ($user->participant->train_access_enabled && $request->routeIs(...self::TRAIN_ROUTES))
            && ! ($request->routeIs('train-owner.*') && $user->hasApprovedTrainOwnerAccess())
        ) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->flash('pending_review_message', self::REVOKED_ACCESS_MESSAGE);
            $request->session()->flash('auth_notice_title', 'Access Revoked');
            $request->session()->flash('email_prefill', $user->email);

            return redirect()->route('login');
        }

        return $next($request);
    }
}
