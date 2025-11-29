<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NAZEM — Verify Code</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('static/css/style.css') }}">
</head>
<body class="auth">

<nav class="topnav">
    <div class="container">
        <a class="brand" href="{{ route('home') }}">NAZEM</a>
        <div class="nav-auth">
            <a class="chip" href="{{ route('login') }}">Log in</a>
            <a class="chip chip--primary" href="{{ route('signup') }}">Sign up</a>
        </div>
    </div>
</nav>

<main class="auth-wrap">
    <div class="auth-card">

        <div class="wordmark">
            <div class="wordmark__brand">NAZEM</div>
            <div class="wordmark__sub">INVENTORY MANAGEMENT SYSTEM</div>
        </div>

        <h2 class="form-title">Verify Code</h2>

        @if (session('error'))
            <p class="errmsg">{{ session('error') }}</p>
        @endif

        <form method="POST" action="{{ route('password.verify') }}">
            @csrf

            <div class="field">
                <label>Email</label>
                <input class="input"
                       type="email"
                       name="email"
                       value="{{ old('email', $email ?? '') }}"
                       placeholder="name@gmail.com"
                       required>
            </div>

            <div class="field">
                <label>Verification Code</label>
                <input class="input"
                       type="text"
                       name="code"
                       placeholder="6-digit code"
                       required>
            </div>

            <div class="field">
                <label>New Password</label>
                <input class="input"
                       type="password"
                       name="password"
                       placeholder="New password"
                       required>
            </div>

            <div class="field">
                <label>Confirm Password</label>
                <input class="input"
                       type="password"
                       name="password_confirmation"
                       placeholder="Re-type password"
                       required>
            </div>

            <button class="btn btn--primary" type="submit" style="width:100%;">
                Reset Password
            </button>

        </form>

    </div>
</main>

</body>
</html>
