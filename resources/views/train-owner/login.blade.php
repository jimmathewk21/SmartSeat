<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Train owner sign in | SmartSeat</title>
    <style>
        body{font:16px system-ui,sans-serif;background:#f2f5f7;color:#18232d;margin:0;min-height:100vh;display:grid;place-items:center;padding:1rem}
        main{background:white;width:min(100%,28rem);padding:2rem;border-radius:1rem;box-shadow:0 1rem 3rem #17324d18}
        label{display:block;margin:.9rem 0 .3rem;font-weight:600}input{box-sizing:border-box;width:100%;padding:.8rem;border:1px solid #aab8c4;border-radius:.4rem}
        button{margin-top:1.2rem;padding:.8rem 1rem;background:#126b62;color:white;border:0;border-radius:.4rem;font-weight:700;cursor:pointer}
        .error{color:#9c2525}.hint{color:#52616d;font-size:.92rem}
    </style>
</head>
<body>
<main>
    <h1>Train owner sign in</h1>
    <p class="hint">Access is available to owners approved by a SmartSeat administrator.</p>
    @if($errors->any())<div class="error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('train-owner.login.submit') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <label><input type="checkbox" name="remember" value="1" style="width:auto"> Remember me</label>
        <button type="submit">Sign in</button>
    </form>
</main>
</body>
</html>
