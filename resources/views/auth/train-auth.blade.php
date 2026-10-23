<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ $mode === 'register' ? 'Create an account' : 'Sign in' }} | SmartSeat</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sign in or register for SmartSeat train bookings.">
    <link href="{{ asset('css/train-auth.css') }}" rel="stylesheet">
</head>
<body class="smartseat-auth-page">
    <main class="smartseat-auth-shell">
        <a class="smartseat-auth-brand" href="{{ route('frontend.trains.search') }}" aria-label="SmartSeat train search">
            <svg viewBox="0 0 32 32" aria-hidden="true" focusable="false"><path d="M9 4h14a4 4 0 0 1 4 4v12a4 4 0 0 1-4 4H9a4 4 0 0 1-4-4V8a4 4 0 0 1 4-4Zm-4 11h22M10 9h4m4 0h4M10 28l3-4m9 4-3-4"/><circle cx="11" cy="19" r="1.5"/><circle cx="21" cy="19" r="1.5"/></svg>
            <span>SmartSeat</span>
        </a>
        <p class="smartseat-auth-tagline">Smarter journeys. Simpler bookings.</p>

        <section class="smartseat-auth-card" aria-labelledby="auth-title">
            <p class="smartseat-auth-eyebrow">Train booking</p>
            <h1 id="auth-title">{{ $mode === 'register' ? 'Create your account' : 'Welcome back' }}</h1>
            <p class="smartseat-auth-intro">
                @if($mode === 'register')
                    Create an account to book train journeys. You can sign in and book as soon as registration is complete.
                @else
                    Sign in to continue to your train search and manage your SmartSeat bookings.
                @endif
            </p>

            @if(session('train_auth_notice'))
                <div class="smartseat-auth-notice" role="status">{{ session('train_auth_notice') }}</div>
            @endif

            @if($errors->any())
                <div class="smartseat-auth-errors" role="alert" aria-labelledby="auth-errors-title">
                    <h2 id="auth-errors-title">Please review these details</h2>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($mode === 'register')
                <form method="POST" action="{{ route('register') }}" class="smartseat-auth-form">
                    @csrf
                    <input type="hidden" name="train_registration_token" value="{{ $registrationToken }}">
                    <label for="name">Full name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus>

                    <label for="company_name">Company or organization</label>
                    <input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}" autocomplete="organization" required>

                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>

                    <label for="mobile">Mobile number</label>
                    <input id="mobile" name="mobile" type="text" value="{{ old('mobile') }}" autocomplete="tel" required>

                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required>

                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>

                    <label for="captcha">Security check: {{ $captchaQuestion }}</label>
                    <input id="captcha" name="captcha" type="text" inputmode="numeric" required>

                    <button class="smartseat-auth-submit" type="submit">Create account <span aria-hidden="true">→</span></button>
                </form>
                <p class="smartseat-auth-switch">Already registered? <a href="{{ route('frontend.trains.login', $authRouteParameters) }}">Sign in</a></p>
            @else
                <form method="POST" action="{{ route('login') }}" class="smartseat-auth-form">
                    @csrf
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', session('email_prefill')) }}" autocomplete="username" required autofocus>

                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>

                    <label class="smartseat-auth-remember" for="remember">
                        <input id="remember" name="remember" type="checkbox" value="1">
                        <span>Remember me</span>
                    </label>

                    <button class="smartseat-auth-submit" type="submit">Sign in to SmartSeat <span aria-hidden="true">→</span></button>
                </form>
                <p class="smartseat-auth-switch">New to SmartSeat? <a href="{{ route('frontend.trains.register', $authRouteParameters) }}">Create an account</a></p>
            @endif
        </section>

        <a class="smartseat-auth-back" href="{{ route('frontend.trains.search', $searchParameters) }}">← Back to Find Trains</a>
    </main>
</body>
</html>
