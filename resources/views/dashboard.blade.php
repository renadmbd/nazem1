<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>NAZEM | Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="{{ asset('static/css/style.css') }}" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>

<body>
<nav class="topnav">
    <div class="container">
        <a class="brand" href="{{ route('home') }}">NAZEM</a>

        <div class="nav-links">
            <a href="{{ route('home') }}">Home</a>
            <a class="active" href="{{ route('dashboard') }}">Dashboard</a>
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
    </div>
</nav>

<main class="page">
    <div class="container">
        <h2 class="page-title">Dashboard</h2>

        {{-- 1) Filters row (بدون Range) --}}
        <div class="card" style="margin-bottom:16px;">
            <form method="GET"
                  class="grid"
                  style="grid-template-columns: repeat(4,minmax(0,1fr)); gap:12px; align-items:end;">

                {{-- Category (ديناميكي من الداتا) --}}
                <div class="field">
                    <label>Category</label>
                    <select name="category" class="select">
                        <option value="">All</option>
                        @foreach($categoryOptions as $cat)
                            <option value="{{ $cat }}" {{ $selectedCategory == $cat ? 'selected' : '' }}>
                                {{ ucfirst($cat) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="field">
                    <label>Status</label>
                    <select name="status" class="select">
                        <option value="">All</option>
                        <option value="safe"   {{ $selectedStatus == 'safe'   ? 'selected' : '' }}>Safe</option>
                        <option value="low"    {{ $selectedStatus == 'low'    ? 'selected' : '' }}>Low</option>
                        <option value="out"    {{ $selectedStatus == 'out'    ? 'selected' : '' }}>Out of stock</option>
                        <option value="expiry" {{ $selectedStatus == 'expiry' ? 'selected' : '' }}>Expiry soon</option>
                    </select>
                </div>

                {{-- Search --}}
                <div class="field">
                    <label>Search by item name</label>
                    <input class="input"
                           type="text"
                           name="q"
                           value="{{ $searchQuery }}"
                           placeholder="e.g. Panadol, Gloves…" />
                </div>

                {{-- Apply + helper --}}
                <div class="field" style="text-align:right;">
                    <button class="btn btn--ghost" type="submit" style="margin-bottom:4px;">
                        Apply
                    </button>
                    <span class="helper">
                        Showing metrics for <strong>all recorded sales</strong>.
                    </span>
                </div>
            </form>
        </div>

       {{-- 2) خمسة كروت KPIs جنب بعض (مع Expiry) --}}
<div class="grid" style="grid-template-columns: repeat(5,minmax(0,1fr)); gap:16px; margin-bottom:16px;">
    <article class="card">
        <h3>Total Items</h3>
        <p style="font-size:28px;font-weight:800;margin:8px 0;">
            {{ $kpis['totalItems'] }}
        </p>
        <p class="muted">All SKUs tracked</p>
    </article>

    <article class="card">
        <h3>Safe Items</h3>
        <p style="font-size:28px;font-weight:800;margin:8px 0;">
            <span class="num-green">{{ $kpis['safeItems'] }}</span>
        </p>
        <p class="muted">Above minimum</p>
    </article>

    <article class="card">
        <h3>Low Stock</h3>
        <p style="font-size:28px;font-weight:800;margin:8px 0;">
            <span class="num-orange">{{ $kpis['lowStock'] }}</span>
        </p>
        <p class="muted">Below minimum</p>
    </article>

    <article class="card">
        <h3>Out of Stock</h3>
        <p style="font-size:28px;font-weight:800;margin:8px 0;">
            <span class="num-red">{{ $kpis['outOfStock'] }}</span>
        </p>
        <p class="muted">Needs reorder</p>
    </article>

    <article class="card">
        <h3>Expiry Soon</h3>
        <p style="font-size:28px;font-weight:800;margin:8px 0;">
            <span class="num-orange">{{ $kpis['expiryItems'] }}</span>
        </p>
        <p class="muted">Expiring in ≤ 14 days</p>
    </article>
</div>


        {{-- 3) صف: pie + bar --}}
        <div class="grid" style="grid-template-columns: repeat(2,minmax(0,1fr)); gap:16px; margin-bottom:16px;">
            <article class="card">
                <h3 class="card__title">Status Distribution</h3>
                <canvas id="pieStatus" height="200"></canvas>
            </article>

            <article class="card">
                <h3 class="card__title">Top by Quantity</h3>
                <canvas id="barTop" height="270"></canvas>
            </article>
        </div>

        {{-- 4) Sales timeline (كل البيانات) --}}
        <div class="card" style="margin-bottom:16px;">
            <h3 class="card__title">Sales (All time)</h3>
            <canvas id="lineSales" height="100"></canvas>
        </div>

        {{-- 5) Demand Forecast --}}
        <div class="card" style="margin-bottom:16px;">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:8px;">
                <h3 class="card__title" style="margin:0;">
                    Demand Forecast (Next {{ count($forecastLabels) }} days)
                    @if($selectedForecastItemName)
                        – {{ $selectedForecastItemName }}
                    @else
                        – All items
                    @endif
                </h3>

                <form method="GET" style="display:flex; gap:8px; align-items:center;">
                    {{-- نحافظ على الفلاتر الحالية --}}
                    <input type="hidden" name="category" value="{{ $selectedCategory }}">
                    <input type="hidden" name="status" value="{{ $selectedStatus }}">
                    <input type="hidden" name="q" value="{{ $searchQuery }}">

                    <label style="font-size:12px;color:#6b7280;">Product:</label>
                    <select name="forecast_product"
                            class="select"
                            onchange="this.form.submit()"
                            style="min-width:200px;">
                        <option value="">All items</option>
                        @foreach($forecastItems as $it)
                            <option value="{{ $it->id }}"
                                {{ $selectedForecastItemId == $it->id ? 'selected' : '' }}>
                                {{ $it->id }} – {{ $it->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <canvas id="lineForecast" height="90"></canvas>
        </div>

        {{-- 6) Quick Sell --}}
        <div class="card" style="margin-bottom:16px;">
            <h3 class="card__title">Quick Sell</h3>
            <form class="grid" method="POST" action="{{ route('data.quickSell') }}"
                  style="grid-template-columns: repeat(3,minmax(0,1fr)); gap:12px; align-items:end;">
                @csrf
                <div class="field">
                    <label>Item ID</label>
                    <input class="input" type="text" name="item_id" placeholder="e.g., 101" />
                </div>

                <div class="field">
                    <label>Quantity</label>
                    <input class="input" type="number" name="quantity" value="1" min="1" />
                </div>

                <div class="field">
                    <button class="btn btn--ghost" type="submit" style="width:100%;margin-top:4px;">
                        Sell
                    </button>
                    <p class="helper" style="margin-top:4px;">
                        {{ session('sell_msg') }}
                    </p>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    // ===== Pie chart (Status) =====
   // ===== Pie chart (Status) =====
const statusCounts = @json($statusCounts);
new Chart(document.getElementById('pieStatus'), {
    type: 'pie',
    data: {
        labels: ['Out', 'Low', 'Safe', 'Expiry'],
        datasets: [{
            data: [
                statusCounts.out,
                statusCounts.low,
                statusCounts.safe,
                statusCounts.expiry
            ],
            backgroundColor: [
                '#dc2626', // Out
                '#F59E0B', // Low
                '#16a34a', // Safe
                '#696c70ff'  // Expiry (indigo)
            ]
        }]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
});


    // ===== Bar chart (Top by Qty) =====
    const topLabels = @json($topLabels);
    const topValues = @json($topValues);
    new Chart(document.getElementById('barTop'), {
        type: 'bar',
        data: {
            labels: topLabels,
            datasets: [{
                label: 'Qty',
                data: topValues
            }]
        },
        options: {
            scales: { y: { beginAtZero: true } }
        }
    });

    // ===== Line chart (Sales history) =====
    const salesLabels = @json($salesLabels);
    const salesValues = @json($salesValues);
    new Chart(document.getElementById('lineSales'), {
        type: 'line',
        data: {
            labels: salesLabels,
            datasets: [{
                label: 'Sold qty',
                data: salesValues,
                tension: 0.3,
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, 0.15)',
                fill: true
            }]
        },
        options: {
            scales: { y: { beginAtZero: true } }
        }
    });

    // ===== Line chart (Demand Forecast) =====
    const forecastLabels = @json($forecastLabels);
    const forecastValues = @json($forecastValues);
    new Chart(document.getElementById('lineForecast'), {
        type: 'line',
        data: {
            labels: forecastLabels,
            datasets: [{
                label: 'Expected Demand',
                data: forecastValues,
                tension: 0.3,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.2)',
                fill: true
            }]
        },
        options: {
            scales: { y: { beginAtZero: true } }
        }
    });
</script>

</body>
</html>
