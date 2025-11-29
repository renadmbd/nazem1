<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NAZEM | Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('static/css/style.css') }}">
</head>

<body>

<nav class="topnav">
    <div class="container">
        <a class="brand" href="{{ route('home') }}">NAZEM</a>

        {{-- إذا المستخدم مسجّل دخول --}}
        @auth
            <div class="nav-links">
                <a class="active" href="{{ route('home') }}">Home</a>
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <a href="{{ route('data') }}">Data</a>
                <a href="{{ route('alerts') }}">Alerts</a>
                <a href="{{ route('profile') }}">Profile</a>
            </div>

            <div class="nav-auth">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="chip" type="submit">Log out</button>
                </form>
            </div>

        {{-- إذا المستخدم غير مسجّل دخول --}}
        @else
            <div class="nav-links">
                <a class="active" href="{{ route('home') }}">Home</a>
            </div>

            <div class="nav-auth">
                <a class="chip" href="{{ route('login') }}">Log in</a>
                <a class="chip chip--primary" href="{{ route('signup') }}">Sign up</a>
            </div>
        @endauth
    </div>
</nav>


<main class="page">

    {{-- HERO SECTION --}}
    <section class="hero container">
        <div class="hero__grid">
            <div>
                <h1>NAZEM Management System</h1>
                <p>
                    AI-powered warehouse management: real-time stock tracking,
                    expiry monitoring, demand forecasting, and automated alerts.
                </p>

                <div class="actions">
                    @auth
                        {{-- لو مسجّل دخول يختفي الزر --}}
                    @else
                        {{-- لو ضيف يروح لتسجيل الدخول --}}
                        <a class="btn btn--primary" href="{{ route('login') }}">
                            Get Started
                        </a>
                    @endauth
                </div>
            </div>

            <div>
                <img src="{{ asset('static/img/Warehouse.jpeg') }}"
                     alt="Warehouse Illustration"
                     style="max-width:100%;border-radius:14px">
            </div>
        </div>
    </section>

    {{-- FEATURES --}}
    <section class="container section">
        <h2>Main Features</h2>

        <div class="cards">
            <article class="card">
                <div class="card__icon">🔍</div>
                <h3>Smart Tracking</h3>
                <p>Track inventory movement and auto-update quantities with clear in/out logs.</p>
            </article>

            <article class="card">
                <div class="card__icon">📊</div>
                <h3>Real-time Analytics</h3>
                <p>Dashboards showing safe, warning, and critical items with live KPIs.</p>
            </article>

            <article class="card">
                <div class="card__icon">🔔</div>
                <h3>Automated Alerts</h3>
                <p>Low stock and expiry reminders delivered instantly to the right users.</p>
            </article>
        </div>
    </section>

</main>

<footer class="footer">
    <div class="container">© 2025 NAZEM — Graduation Project</div>
</footer>

</body>
</html>
