<?php

namespace App\Providers;

use App\Actions\Fortify\ConfirmPassword;
use App\Actions\Fortify\CreateNewUser;
use App\Http\Requests\LoginRequest;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FortifyLoginRequest::class, LoginRequest::class);
        $this->app->bind(\Laravel\Fortify\Actions\ConfirmPassword::class, ConfirmPassword::class);
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));

        RateLimiter::for('login', function (Request $request) {
            $email = $request->input('email');
            $key = hash('sha256', Str::lower(is_string($email) ? $email : '').'|'.$request->ip());

            return [
                Limit::perMinute(5)->by('login:'.$key),
                Limit::perMinute(30)->by('login-ip:'.$request->ip()),
            ];
        });
    }
}
