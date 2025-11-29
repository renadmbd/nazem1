<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NAZEM — Forgot Password</title>
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

        <h2 class="form-title">Forgot Password</h2>

        @if (session('error'))
            <p class="errmsg">{{ session('error') }}</p>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="field">
                <label>Email</label>
                <input class="input" type="email" name="email"
                       placeholder="name@gmail.com" required>
            </div>

            <button class="btn btn--primary" type="submit" style="width:100%;">
                Send Verification Code
            </button>

            <p class="helper" style="margin-top:8px;">
                Remember your password?
                <a href="{{ route('login') }}">Log in</a>
            </p>
        </form>

    </div>
</main>

</body>
</html>
