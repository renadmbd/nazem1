<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NAZEM — Log in</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('static/css/style.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('nazem-logo.png') }}">
</head>

<body class="auth">

<nav class="topnav">
    <div class="container">
        {{-- اللوجو يرجع للهوم --}}
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

        <h2 class="form-title">Log in</h2>

        {{-- رسالة نجاح بعد إنشاء حساب --}}
        @if (session('success'))
            <p class="helper" style="color:green; margin-bottom:8px;">
                {{ session('success') }}
            </p>
        @endif

        {{-- رسالة خطأ --}}
        @if (session('error'))
            <p class="errmsg">{{ session('error') }}</p>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field">
                <label>Email</label>
                <input class="input"
                       type="email"
                       name="email"
                       placeholder="Enter your email"
                       required>
            </div>

            <div class="field">
                <label>Password</label>
                <input class="input"
                       type="password"
                       name="password"
                       placeholder="Enter password"
                       required>
            </div>
            
            <p class="helper" style="margin-top:8px;">
               <a href="{{ route('password.request') }}">Forgot password?</a>
            </p>

            <button class="btn btn--primary" type="submit" style="width:100%;">LOG IN</button>

            <p class="helper">
                Don't have an account?
                <a href="{{ route('signup') }}">Sign up</a>
            </p>
        </form>

    </div>
</main>

</body>
</html>
