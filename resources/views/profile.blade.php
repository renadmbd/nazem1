<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NAZEM | Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('static/css/style.css') }}">
</head>
<body>

<nav class="topnav">
    <div class="container">
        {{-- اللوجو يودّي للهوم --}}
        <a class="brand" href="{{ route('home') }}">NAZEM</a>

        <div class="nav-links">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <a href="{{ route('data') }}">Data</a>
            <a href="{{ route('alerts') }}">Alerts</a>
            <a class="active" href="{{ route('profile') }}">Profile</a>
        </div>

        <div class="nav-auth">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="chip" type="submit">Log out</button>
            </form>
        </div>
    </div>
</nav>

<main class="page">
    <div class="container narrow">
        <div class="auth-card">
            <h2 class="form-title">Profile</h2>

            @php
                $user = auth()->user();
            @endphp

            <div class="field">
                <label>Username</label>
                <input
                    class="input"
                    type="text"
                    value="{{ $user->name }}"
                    disabled
                >
            </div>

            <div class="field">
                <label>Email</label>
                <input
                    class="input"
                    type="email"
                    value="{{ $user->email }}"
                    disabled
                >
            </div>

            <div class="field">
                <label>Member since</label>
                <input
                    class="input"
                    type="text"
                    value="{{ $user->created_at?->format('Y-m-d') }}"
                    disabled
                >
            </div>

            <p class="helper" style="margin-top:8px;">
                To update your account info, please contact the admin.
            </p>
        </div>
    </div>
</main>

<footer class="footer">
    <div class="container">
        © 2025 NAZEM — Graduation Project
    </div>
</footer>

</body>
</html>
