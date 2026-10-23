<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Train owner panel') | SmartSeat</title>
    <style>
        body{font:16px system-ui,sans-serif;background:#f2f5f7;color:#18232d;margin:0}
        header{background:#123a46;color:white;padding:1rem max(1rem,calc((100vw - 70rem)/2));display:flex;justify-content:space-between;align-items:center;gap:1rem}
        main{max-width:70rem;margin:2rem auto;padding:0 1rem}a{color:#075f78}table{border-collapse:collapse;width:100%;background:white}th,td{text-align:left;padding:.8rem;border-bottom:1px solid #dce3e8}
        input,select{box-sizing:border-box;padding:.7rem;border:1px solid #aab8c4;border-radius:.35rem;max-width:34rem;width:100%}
        label{display:block;font-weight:600;margin:.8rem 0 .3rem}button,.button{display:inline-block;padding:.65rem .9rem;border:0;border-radius:.35rem;background:#126b62;color:white;text-decoration:none;font-weight:700;cursor:pointer}
        .message{padding:.8rem;background:#e4f3ec;border-radius:.4rem}.error{color:#9c2525}.muted{color:#52616d}
        @media(max-width:600px){header{align-items:flex-start;flex-direction:column}table{font-size:.9rem}th,td{padding:.55rem}}
    </style>
</head>
<body>
<header><strong>SmartSeat · Train owner panel</strong><form method="POST" action="{{ route('train-owner.logout') }}">@csrf<button type="submit">Sign out</button></form></header>
<main>
    @if(session('status'))<p class="message" role="status">{{ session('status') }}</p>@endif
    @if($errors->any())<div class="error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
</main>
</body>
</html>
