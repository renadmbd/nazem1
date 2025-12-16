<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NAZEM — Sign Up</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('static/css/style.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('nazem-logo.png') }}">
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

        <h2 class="form-title">Sign up</h2>

        {{-- رسائل الأخطاء --}}
        @if ($errors->any())
            <p class="errmsg">{{ $errors->first() }}</p>
        @endif

        {{-- رسالة نجاح --}}
        @if (session('success'))
            <p class="helper" style="color: green">{{ session('success') }}</p>
        @endif

        <form method="POST" action="{{ route('signup') }}">
            @csrf

            <div class="field">
                <label>Username</label>
                <input class="input" type="text" name="name" placeholder="Enter your username" required>
            </div>

            <div class="field">
                <label>Email</label>
                <input class="input" type="email" name="email" placeholder="name@example.com" required>
            </div>

            <div class="field">
                <label>Password</label>
                <input class="input" type="password" name="password" placeholder="Enter password" required>
            </div>

            <div class="field">
                <label>Confirm Password</label>
                <input class="input" type="password" name="password_confirmation" placeholder="Re-type password" required>
            </div>

            <button class="btn btn--primary" type="submit" style="width:100%;">SIGN UP</button>

            <p class="helper">
                Already have an account?
                <a href="{{ route('login') }}">Log in</a>
            </p>
        </form>

    </div>
</main>

</body>
</html>
