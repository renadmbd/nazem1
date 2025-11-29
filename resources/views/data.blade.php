<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NAZEM | Data</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('static/css/style.css') }}">
</head>
<body>

<nav class="topnav">
    <div class="container">
        {{-- اللوجو يودّي للصفحة الرئيسية --}}
        <a class="brand" href="{{ route('home') }}">NAZEM</a>

        <div class="nav-links">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <a class="active" href="{{ route('data') }}">Data</a>
            <a href="{{ route('alerts') }}">Alerts</a>
            <a href="{{ route('profile') }}">Profile</a>
        </div>

        <div class="nav-auth">
            <form id="logout-form" method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="chip" type="submit">Log out</button>
            </form>
        </div>
    </div>
</nav>

<main class="page">
    <div class="container">

        <h2 class="page-title">Data Management</h2>

        @if (session('success'))
            <p class="helper" style="color:green">{{ session('success') }}</p>
        @endif

        {{-- Upload CSV --}}
        <div class="card">
            <h3 class="card__title">Upload Excel/CSV</h3>

            <form class="grid" method="POST" action="{{ route('data.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="field">
                    <label>Choose file</label>
                    <input class="input" type="file" name="file" accept=".csv" required>
                </div>

                <button class="btn btn--primary" type="submit">Import</button>

                <p class="helper">
                    Columns: <b>id, category, name, price, qty, min_qty, expiry (YYYY-MM-DD)</b>
                </p>
            </form>
        </div>

        {{-- Preview Table --}}
        <div class="card" style="margin-top:16px;">

            {{-- العنوان + زر الريفرش --}}
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <h3 class="card__title" style="margin:0;">Preview</h3>

                <form method="GET" action="{{ route('data') }}">
                    <button class="chip" type="submit" style="padding:6px 16px; cursor:pointer;">
                        Refresh
                    </button>
                </form>
            </div>

            @if($items->isEmpty())
                <p class="helper">No data available. Please import a file.</p>
            @else
                <div style="overflow:auto; max-height: 450px;">
                    <table class="table" style="width:100%; border-collapse:collapse; font-size: 14px;">
                        <thead>
                            <tr style="background:#f5f7fb;">
                                <th style="padding:8px 10px;">ID</th>
                                <th style="padding:8px 10px;">Category</th>
                                <th style="padding:8px 10px;">Name</th>
                                <th style="padding:8px 10px; text-align:right;">Qty</th>
                                <th style="padding:8px 10px; text-align:right;">Min</th>
                                <th style="padding:8px 10px; text-align:center;">Expiry</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td style="padding:6px 10px; border-top:1px solid #eee;">
                                        {{ $item->id }}
                                    </td>
                                    <td style="padding:6px 10px; border-top:1px solid #eee;">
                                        {{ $item->category }}
                                    </td>
                                    <td style="padding:6px 10px; border-top:1px solid #eee;">
                                        {{ $item->name }}
                                    </td>
                                    <td style="padding:6px 10px; border-top:1px solid #eee; text-align:right;">
                                        {{ $item->stock_availability }}
                                    </td>
                                    <td style="padding:6px 10px; border-top:1px solid #eee; text-align:right;">
                                        {{ $item->min_qty }}
                                    </td>
                                    <td style="padding:6px 10px; border-top:1px solid #eee; text-align:center;">
                                        {{ $item->expiration_date }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Quick Sell --}}
        <div class="card" style="margin-top:16px;">
            <h3 class="card__title">Quick Sell</h3>
            <form class="grid" method="POST" action="{{ route('data.quickSell') }}">
                @csrf

                <div class="field">
                    <label>Item ID</label>
                    <input class="input" type="text" name="item_id" placeholder="e.g., 101">
                </div>

                <div class="field">
                    <label>Quantity</label>
                    <input class="input" type="number" name="quantity" min="1" value="1">
                </div>

                <button class="btn btn--ghost" type="submit">Sell</button>

                <p class="helper">{{ session('sell_msg') }}</p>
            </form>
        </div>

    </div>
</main>

</body>
</html>
