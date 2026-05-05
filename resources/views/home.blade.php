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

    {{-- HERO --}}
    <section class="hero container hero-animate">
        <div class="hero__grid">
            <div>
                <h1 class="hero-title">NAZEM Management System</h1>

                <p class="hero-desc">
                    AI-powered warehouse management with real-time tracking,
                    smart alerts, and demand forecasting.
                </p>

                <div class="actions">
                    @auth
                        <a class="btn btn--primary" href="{{ route('dashboard') }}">
                            Go to Dashboard
                        </a>

                        <a class="btn btn--ghost" href="{{ route('alerts') }}">
                            View Alerts
                        </a>
                    @else
                        <a class="btn btn--primary" href="{{ route('login') }}">
                            Get Started
                        </a>

                        <a class="btn btn--ghost" href="{{ route('signup') }}">
                            Create Account
                        </a>
                    @endauth
                </div>
            </div>

            <div class="hero-img-wrap">
                <img src="{{ asset('static/img/Warehouse.jpeg') }}"
                     alt="Warehouse"
                     class="hero-img">
            </div>
        </div>
    </section>

    {{-- SERVICES --}}
    <section class="home-section">
        <div class="container">

            <h2 class="section-title">Services</h2>

            <div class="grid grid-3">

                <article class="card hover-card">
                    <div class="card-img">
                        <img src="{{ asset('static/img/IMG_9744.png') }}">
                    </div>

                    <h3>Expiry Alerts</h3>
                    <p class="muted">
                        Get instant notifications for products nearing expiry to reduce waste.
                    </p>
                </article>

                <article class="card hover-card">
                    <div class="card-img">
                        <img src="{{ asset('static/img/IMG_9745.png') }}">
                    </div>

                    <h3>Demand Forecasting</h3>
                    <p class="muted">
                        AI predicts future demand using historical sales data.
                    </p>
                </article>

                <article class="card hover-card">
                    <div class="card-img">
                        <img src="{{ asset('static/img/IMG_9746.png') }}">
                    </div>

                    <h3>Stock Optimization</h3>
                    <p class="muted">
                        Smart stock monitoring to prevent shortage or overstock.
                    </p>
                </article>

            </div>

        </div>
    </section>

    {{-- FEATURES --}}
    <section class="container section">
        <h2 class="section-title">Main Features</h2>

        <div class="cards">

            <article class="card hover-card">
                <div class="card__icon">🔍</div>
                <h3>Smart Tracking</h3>
                <p>Track inventory movement with automatic updates.</p>
            </article>

            <article class="card hover-card">
                <div class="card__icon">📊</div>
                <h3>Real-time Analytics</h3>
                <p>Live dashboards with clear KPIs and insights.</p>
            </article>

            <article class="card hover-card">
                <div class="card__icon">🔔</div>
                <h3>Automated Alerts</h3>
                <p>Receive alerts instantly for stock and expiry.</p>
            </article>

        </div>
    </section>

</main>

<footer class="footer">
    <div class="container">© 2025 NAZEM — Graduation Project</div>
</footer>

</body>
</html>