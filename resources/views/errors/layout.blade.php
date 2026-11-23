<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') — Funny Couples</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0}
        body{min-height:100vh;display:grid;place-items:center;padding:24px 16px;background:radial-gradient(ellipse at 80% 0%,#522b5640,transparent 45%),#100d18;color:#f8f1ee;font-family:Tajawal,system-ui,sans-serif;line-height:1.8}
        main{width:100%;max-width:30rem;text-align:center}
        .code{display:inline-block;padding:4px 14px;border:1px solid #efc5aa55;border-radius:999px;color:#efc5aa;font-weight:700;font-size:14px;letter-spacing:2px}
        .card{width:96px;height:128px;margin:28px auto 22px;border-radius:16px;border:2px solid #efc5aa66;background:linear-gradient(150deg,#2b1733,#171324);display:grid;place-items:center;transform:rotate(-6deg)}
        h1{font-size:28px;font-weight:900;margin-bottom:8px}
        p{color:#bcafc6;font-size:15px}
        .actions{display:grid;gap:10px;margin-top:28px}
        .primary,.secondary{display:block;padding:14px 18px;border-radius:12px;font-weight:700;text-decoration:none;font-size:15px}
        .primary{background:#efc5aa;color:#251826}
        .secondary{border:1px solid #ffffff26;color:#f3e8f0}
        button.secondary{width:100%;background:none;font:inherit;cursor:pointer}
    </style>
</head>
<body>
<main>
    <span class="code">@yield('code')</span>
    <div class="card" aria-hidden="true">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#efc5aa" stroke-width="1.6"><path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/></svg>
    </div>
    <h1>@yield('heading')</h1>
    <p>@yield('message')</p>
    <div class="actions">
        @yield('actions')
        <a class="primary" href="{{ url('/') }}">رجوع للرئيسية</a>
        <a class="secondary" href="{{ url('/') }}#games">كل الألعاب</a>
    </div>
</main>
</body>
</html>
