<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NAZEM | Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('static/css/style.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('nazem-logo.png') }}">
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
{{-- Services Section --}}
<section class="home-section" style="padding:1px 0 0px;">
    <div class="container">

        <h2 style="font-size:28px;font-weight:700;margin-bottom:32px;">
            Services
        </h2>

        <div class="grid" style="grid-template-columns: repeat(3, minmax(0,1fr)); gap:24px;">

            {{-- Card 1 - Expiry Alerts --}}
            <article class="card" style="text-align:left; padding:28px 24px;">
                <div style="display:flex; justify-content:center; margin-bottom:20px;">
                    <img src="{{ asset('static/img/IMG_9744.png') }}"
                         alt="Expiry Alerts"
                         style="max-width:150px; height:auto;">
                </div>

                <h3 style="font-size:18px; font-weight:600; margin-bottom:8px;">Expiry Alerts</h3>
                <p class="muted" style="font-size:14px; line-height:1.6;">
                    Get instant notifications for products nearing expiry to reduce waste.
                </p>
            </article>

            {{-- Card 2 - Demand Forecasting --}}
            <article class="card" style="text-align:left; padding:28px 24px;">
                <div style="display:flex; justify-content:center; margin-bottom:20px;">
                    <img src="{{ asset('static/img/IMG_9745.png') }}"
                         alt="Demand Forecasting"
                         style="max-width:150px; height:auto;">
                </div>

                <h3 style="font-size:18px; font-weight:600; margin-bottom:8px;">Demand Forecasting</h3>
                <p class="muted" style="font-size:14px; line-height:1.6;">
                    AI predicts future product demand using past data helping plan stock levels efficiently.
                </p>
            </article>

            {{-- Card 3 - Stock Optimization --}}
            <article class="card" style="text-align:left; padding:28px 24px;">
                <div style="display:flex; justify-content:center; margin-bottom:20px;">
                    <img src="{{ asset('static/img/IMG_9746.png') }}"
                         alt="Stock Optimization"
                         style="max-width:150px; height:auto;">
                </div>

                <h3 style="font-size:18px; font-weight:600; margin-bottom:8px;">Stock Optimization</h3>
                <p class="muted" style="font-size:14px; line-height:1.6;">
                    Monitors stock using a 3-color system (Green, Yellow, Red) to prevent shortage or overstock.
                </p>
            </article>

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
