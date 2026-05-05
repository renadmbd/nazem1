<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Access Denied</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('static/css/style.css') }}">

    <style>
        body{
            margin:0;
            background:#f4f5fb;
        }

        .denied-wrap{
            min-height: calc(100vh - 90px);
            display:flex;
            align-items:center;
            justify-content:center;
            padding:40px 20px;
        }

        .denied-image{
            max-width: 720px;
            width: 100%;
            height: auto;
            display: block;
            object-fit: contain;
        }

        @media (max-width: 768px){
            .denied-wrap{
                padding:24px 16px;
            }

            .denied-image{
                max-width: 95%;
            }
        }
    </style>
</head>
<body>
    <nav class="topnav">
        <div class="container">
            <a class="brand" href="{{ route('home') }}">NAZEM</a>

            <div class="nav-links">
                @auth
                    <a href="{{ route('home') }}">Home</a>
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <a href="{{ route('data') }}">Data</a>
                    <a href="{{ route('alerts') }}">Alerts</a>
                    <a href="{{ route('profile') }}">Profile</a>
                @else
                    <a href="{{ route('home') }}">Home</a>
                @endauth
            </div>

            <div class="nav-auth">
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="chip" type="submit">Log out</button>
                    </form>
                @else
                    <a class="chip" href="{{ route('login') }}">Log in</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="denied-wrap">
        <img
            src="{{ asset('access denied.png') }}"
            alt="Access Denied"
            class="denied-image"
        >
    </main>
</body>
</html>