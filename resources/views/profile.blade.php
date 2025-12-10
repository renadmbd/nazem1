<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NAZEM | Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('static/css/style.css') }}">
</head>
<body>

{{-- Navigation Bar --}}
<nav class="topnav">
    <div class="container">
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

{{-- Page Content --}}
<main class="page" style="display:flex; justify-content:center; align-items:center; min-height:70vh;">
    <div class="container" style="max-width:520px;">

        @php $user = auth()->user(); @endphp
        
        <article class="card" style="padding:32px;">

            {{-- Avatar + Header --}}
            <div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
                <div style="
                    width:64px;height:64px;border-radius:50%;
                    background:#e8efff;display:flex;align-items:center;justify-content:center;
                    font-weight:700;font-size:22px;color:#1f2937;
                ">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>

                <div>
                    <h2 style="margin:0;font-size:22px;font-weight:700;">Profile</h2>
                    <p style="margin:2px 0 0;font-size:13px;color:#6b7280;">
                         Your NAZEM account information.
                    </p>
                </div>
            </div>

            {{-- Account Info Section --}}
            <h4 style="font-size:13px;font-weight:600;color:#9ca3af;margin-bottom:6px;letter-spacing:.05em;">
                ACCOUNT INFO
            </h4>

            <div class="field">
                <label>Username</label>
                <input class="input" type="text" value="{{ $user->name }}" disabled>
            </div>

            <div class="field">
                <label>Email</label>
                <input class="input" type="email" value="{{ $user->email }}" disabled>
            </div>

            {{-- System Info --}}
            <h4 style="font-size:13px;font-weight:600;color:#9ca3af;margin:20px 0 6px;letter-spacing:.05em;">
                SYSTEM INFO
            </h4>

            <div class="field">
                <label>Member since</label>
                <input class="input" type="text" value="{{ $user->created_at?->format('Y-m-d') }}" disabled>
            </div>

            {{-- Footer Text --}}
            <div style="margin-top:18px;">
                <p class="helper" style="margin:0;">
                    To update your account info, please contact the admin at
                    <strong>info@nazem.sa</strong>
                </p>
            </div>

        </article>
    </div>
</main>

{{-- Footer --}}
<footer class="footer">
    <div class="container">
        © 2025 NAZEM — Graduation Project
    </div>
</footer>

</body>
</html>
