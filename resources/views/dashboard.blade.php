<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NAZEM | Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('static/css/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <style>
        .dashboard-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .dashboard-head-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-info {
            border: 1px solid var(--border);
            background: #fff;
            color: var(--brand);
            font-weight: 700;
            border-radius: 12px;
            padding: 10px 14px;
            cursor: pointer;
            transition: .18s ease;
        }

        .btn-info:hover {
            background: #f8fafc;
            transform: translateY(-1px);
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            justify-content: center;
            align-items: center;
            z-index: 9999;
            padding: 20px;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal-content {
            background: #fff;
            width: 100%;
            max-width: 640px;
            border-radius: 20px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.18);
            padding: 26px 24px 22px;
            position: relative;
            animation: modalFade .22s ease;
        }

        @keyframes modalFade {
            from {
                opacity: 0;
                transform: translateY(10px) scale(.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-close {
            position: absolute;
            top: 12px;
            right: 14px;
            border: none;
            background: transparent;
            font-size: 26px;
            line-height: 1;
            cursor: pointer;
            color: #64748b;
        }

        .modal-close:hover {
            color: #0f172a;
        }

        .modal-title {
            margin: 0 0 10px;
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
        }

        .modal-text {
            color: #475569;
            font-size: 15px;
            line-height: 1.7;
            margin-bottom: 14px;
        }

        .modal-list {
            margin: 0 0 14px;
            padding-left: 18px;
            color: #334155;
        }

        .modal-list li {
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: 18px;
        }

        .muted {
            color: var(--muted);
        }

        @media (max-width: 900px) {
            .dashboard-head {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
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

        <div class="dashboard-head">
            <h2 class="page-title">Dashboard</h2>

            <div class="dashboard-head-actions">
                <button type="button" class="btn-info" onclick="openInfoModal()">
                    <span class="summary-icon">✦</span>
                    <span>Generate Summary</span>
                </button>
            </div>
        </div>

        {{-- 1) Filters row --}}
        <div class="card" style="margin-bottom:16px;">
            <form method="GET"
                  action="{{ route('dashboard') }}"
                  class="grid"
                  style="grid-template-columns: repeat(4,minmax(0,1fr)); gap:12px; align-items:end;">

                {{-- Category --}}
                <div class="field">
                    <label>Category</label>
                    <select class="input" name="category">
                        <option value="">All</option>
                        @foreach ($categoryOptions as $category)
                            <option value="{{ $category }}" {{ $selectedCategory == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="field">
                    <label>Status</label>
                    <select class="input" name="status">
                        <option value="">All</option>
                        <option value="safe" {{ $selectedStatus == 'safe' ? 'selected' : '' }}>Safe</option>
                        <option value="low" {{ $selectedStatus == 'low' ? 'selected' : '' }}>Low stock</option>
                        <option value="out" {{ $selectedStatus == 'out' ? 'selected' : '' }}>Out of stock</option>
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
                    <button class="btn btn--ghost" type="submit" style="margin-bottom:4px; width:100%;">
                        Apply
                    </button>
                    <span class="helper">
                        Showing metrics for <strong>all recorded sales</strong>.
                    </span>
                </div>
            </form>
        </div>

        {{-- 2) KPI cards --}}
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
                    <span style="color:#696c70ff;">{{ $kpis['expiryItems'] }}</span>
                </p>
                <p class="muted">Expiring in ≤ 14 days</p>
            </article>
        </div>

        {{-- 3) pie + bar --}}
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

        {{-- 4) Sales timeline --}}
        <div class="card" style="margin-bottom:16px;">
            <h3 class="card__title">Sales (All time)</h3>
            <canvas id="lineSales" height="90"></canvas>
        </div>

        {{-- 5) Forecast --}}
        <div class="card" style="margin-bottom:16px;">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; margin-bottom:10px;">
                <h3 class="card__title" style="margin:0;">
                    Demand Forecast (Next 30 days)
                    @if(!empty($selectedForecastItemName))
                        – {{ $selectedForecastItemName }}
                    @else
                        – All items
                    @endif
                </h3>

                <form method="GET" action="{{ route('dashboard') }}" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <input type="hidden" name="category" value="{{ $selectedCategory }}">
                    <input type="hidden" name="status" value="{{ $selectedStatus }}">
                    <input type="hidden" name="q" value="{{ $searchQuery }}">

                    <label for="forecast_product" class="muted" style="font-size:13px;">Product:</label>
                    <select class="select" id="forecast_product" name="forecast_product" onchange="this.form.submit()">
                        <option value="">All items</option>
                        @foreach($forecastItems as $item)
                            <option value="{{ $item->id }}" {{ (string)$selectedForecastItemId === (string)$item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <canvas id="lineForecast" height="100"></canvas>
        </div>

        {{-- 6) Quick sell --}}
        <div class="card" style="margin-bottom:16px;">
            <h3 class="card__title">Quick Sell</h3>

            <form method="POST"
                  action="{{ route('data.quickSell') }}"
                  class="grid"
                  style="grid-template-columns: 1.1fr 1fr 0.8fr; gap:12px; align-items:end;">
                @csrf

                <div class="field">
                    <label>Item ID</label>
                    <input class="input" type="number" name="item_id" placeholder="e.g., 101" required>
                </div>

                <div class="field">
                    <label>Quantity</label>
                    <input class="input" type="number" name="quantity" value="1" min="1" required>
                </div>

                <div class="field">
                    <button class="btn btn--ghost" type="submit" style="height:58px;">Sell</button>
                </div>
            </form>
        </div>

    </div>
</main>

{{-- Dashboard Info Modal --}}
<div id="infoModal" class="modal-overlay" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="infoModalTitle">
        <button class="modal-close" type="button" onclick="closeInfoModal()" aria-label="Close">×</button>

        @php
            $totalItems = $kpis['totalItems'] ?? 0;
            $safeItems = $kpis['safeItems'] ?? 0;
            $lowItems = $kpis['lowStock'] ?? 0;
            $outItems = $kpis['outOfStock'] ?? 0;
            $expiryItems = $kpis['expiryItems'] ?? 0;

            $topItemName = null;
            $topItemQty = null;

            if (!empty($topLabels) && count($topLabels) > 0) {
                $topItemName = $topLabels[0];
            }

            if (!empty($topValues) && count($topValues) > 0) {
                $topItemQty = $topValues[0];
            }

            $forecastAvg = 0;
            if (!empty($forecastValues) && count($forecastValues) > 0) {
                $forecastAvg = round(array_sum($forecastValues) / count($forecastValues));
            }

            // System Status Logic
            $status = 'Stable';
            $statusText = 'Inventory levels are healthy overall.';

            if ($outItems > 0) {
                $status = 'Critical';
                $statusText = 'Some items are out of stock and require immediate action.';
            } elseif ($lowItems > 0) {
                $status = 'Warning';
                $statusText = 'Some items are running low and need restocking soon.';
            } elseif ($expiryItems > 0) {
                $status = 'Attention';
                $statusText = 'Some items are nearing expiration and should be reviewed.';
            }

            $healthPercent = $totalItems > 0 ? round(($safeItems / $totalItems) * 100) : 0;
        @endphp

        <h3 class="modal-title" id="infoModalTitle">📊 Dashboard Summary Report</h3>

        <p class="modal-text">
            This report provides a quick overview of the current inventory status and system insights based on real-time data.
        </p>

        <ul class="modal-list">
            <li>
                There are currently <strong>{{ $totalItems }}</strong> items tracked in the system.
            </li>

            <li>
                <strong>{{ $safeItems }}</strong> items are in a safe stock level,
                representing approximately <strong>{{ $healthPercent }}%</strong> of total inventory.
            </li>

            <li>
                <strong>{{ $lowItems }}</strong> items are low in stock,
                <strong>{{ $outItems }}</strong> are out of stock,
                and <strong>{{ $expiryItems }}</strong> are nearing expiration.
            </li>

            <li>
                <strong>System Status:</strong> {{ $status }} — {{ $statusText }}
            </li>

            @if($topItemName && $topItemQty !== null)
                <li>
                    The top item by quantity is <strong>{{ $topItemName }}</strong>
                    with a total of <strong>{{ $topItemQty }}</strong>.
                </li>
            @endif

            @if(!empty($salesValues) && count($salesValues) > 1)
                <li>
                    Sales activity shows a consistent trend that can be used for performance insights.
                </li>
            @endif

            @if(!empty($forecastValues) && count($forecastValues) > 0)
                <li>
                    The average expected demand for the upcoming period is approximately
                    <strong>{{ $forecastAvg }}</strong> units.
                </li>
            @endif
        </ul>

        <div class="modal-footer">
            <button class="btn btn--primary" type="button" onclick="closeInfoModal()">Got it</button>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container">© 2025 NAZEM — Graduation Project</div>
</footer>

<script>
    function openInfoModal() {
        const modal = document.getElementById('infoModal');
        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeInfoModal() {
        const modal = document.getElementById('infoModal');
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
    }

    window.addEventListener('click', function (e) {
        const modal = document.getElementById('infoModal');
        if (e.target === modal) {
            closeInfoModal();
        }
    });

    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeInfoModal();
        }
    });

    const pieStatusCtx = document.getElementById('pieStatus');
    if (pieStatusCtx) {
        new Chart(pieStatusCtx, {
            type: 'pie',
            data: {
                labels: ['Out', 'Low', 'Safe', 'Expiry'],
                datasets: [{
                    data: [
                        {{ $statusCounts['out'] ?? 0 }},
                        {{ $statusCounts['low'] ?? 0 }},
                        {{ $statusCounts['safe'] ?? 0 }},
                        {{ $statusCounts['expiry'] ?? 0 }}
                    ],
                    backgroundColor: ['#e11d23', '#f59e0b', '#16a34a', '#6b7280'],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    const barTopCtx = document.getElementById('barTop');
    if (barTopCtx) {
        new Chart(barTopCtx, {
            type: 'bar',
            data: {
                labels: @json($topLabels),
                datasets: [{
                    label: 'Qty',
                    data: @json($topValues),
                    backgroundColor: 'rgba(96, 165, 250, 0.55)',
                    borderColor: 'rgba(96, 165, 250, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    const lineSalesCtx = document.getElementById('lineSales');
    if (lineSalesCtx) {
        new Chart(lineSalesCtx, {
            type: 'line',
            data: {
                labels: @json($salesLabels),
                datasets: [{
                    label: 'Sold qty',
                    data: @json($salesValues),
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.18)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointHoverRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    const lineForecastCtx = document.getElementById('lineForecast');
    if (lineForecastCtx) {
        new Chart(lineForecastCtx, {
            type: 'line',
            data: {
                labels: @json($forecastLabels),
                datasets: [{
                    label: 'Expected Demand',
                    data: @json($forecastValues),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.14)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 2,
                    pointHoverRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
</script>

</body>
</html>