<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NAZEM | Alerts</title>
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
            <a class="active" href="{{ route('alerts') }}">Alerts</a>
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
        <h2 class="page-title">Alerts</h2>

        {{-- Filters + Search --}}
        <div class="card" style="margin-bottom:16px;">
            <div class="filters">
                <button class="btn btn--ghost active" data-filter="all">
                    All <span id="cntAll" class="badge">{{ $counts['all'] }}</span>
                </button>
                <button class="btn btn--ghost" data-filter="low">
                    Low <span id="cntLow" class="badge">{{ $counts['low'] }}</span>
                </button>
                <button class="btn btn--ghost" data-filter="out">
                    Out <span id="cntOut" class="badge">{{ $counts['out'] }}</span>
                </button>
                <button class="btn btn--ghost" data-filter="expiry">
                    Expiry <span id="cntExp" class="badge">{{ $counts['expiry'] }}</span>
                </button>

                <div class="searchbar" style="margin-left:auto">
                    <input id="q" class="input" type="search" placeholder="Search item…">
                    <select id="sort" class="select">
                        <option value="severity">Sort: Severity</option>
                        <option value="expirySoon">Sort: Expiry soon</option>
                        <option value="name">Sort: Name</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Alerts list --}}
        <ul id="list" class="alert-list">
            @forelse($alerts as $alert)
                <li class="alert-card alert-{{ $alert->type }}"
                    data-type="{{ $alert->type }}"
                    data-name="{{ strtolower($alert->name) }}"
                    data-days="{{ $alert->days_left ?? 99999 }}"
                    data-severity="{{ $alert->severity }}">

                    <div class="alert-header">
                        @php
                            $pill = $alert->type === 'out'
                                ? 'pill-out'
                                : ($alert->type === 'low' ? 'pill-low' : 'pill-exp');
                        @endphp
                        <span class="pill {{ $pill }}">
                            {{ strtoupper($alert->type) }}
                        </span>
                        <small class="muted">
                            {{ $alert->item_id ? '#'.$alert->item_id : '' }}
                        </small>
                    </div>

                    <p class="alert-title">{{ $alert->name }}</p>
                    <p class="alert-meta">{{ $alert->meta }}</p>
                </li>
            @empty
                <p class="helper">No alerts.</p>
            @endforelse
        </ul>

        <p class="helper">
            Rules: Out (qty=0), Low (qty ≤ min), Expiry (≤ {{ $expiryDays }} days)
        </p>
    </div>
</main>

<script>
    const EXPIRY_DAYS = {{ $expiryDays }};
    const listEl   = document.getElementById('list');
    const items    = [...listEl.querySelectorAll('li[data-type]')];
    const buttons  = [...document.querySelectorAll('[data-filter]')];
    const q        = document.getElementById('q');
    const sortSel  = document.getElementById('sort');

    let current = 'all';

    function renderAlerts() {
        const qv = q.value.trim().toLowerCase();
        const mode = sortSel.value;

        let filtered = items.slice();

        // filter by type
        if (current !== 'all') {
            filtered = filtered.filter(li => li.dataset.type === current);
        }

        // search by name
        if (qv) {
            filtered = filtered.filter(li =>
                li.dataset.name.indexOf(qv) !== -1
            );
        }

        // sort
        filtered.sort((a, b) => {
            if (mode === 'name') {
                return a.dataset.name.localeCompare(b.dataset.name);
            }
            if (mode === 'expirySoon') {
                return (+a.dataset.days || 99999) - (+b.dataset.days || 99999);
            }
            // severity (out > low > expiry)
            return (+b.dataset.severity || 0) - (+a.dataset.severity || 0);
        });

        // hide all then show only filtered
        items.forEach(li => li.style.display = 'none');
        filtered.forEach(li => li.style.display = '');

        // active button
        buttons.forEach(b => b.classList.toggle('active', b.dataset.filter === current));

        // لو مافي عناصر ظاهرة
        if (!filtered.length) {
            if (!document.getElementById('no-alerts-msg')) {
                const p = document.createElement('p');
                p.id = 'no-alerts-msg';
                p.className = 'helper';
                p.textContent = 'No alerts.';
                listEl.appendChild(p);
            }
        } else {
            const msg = document.getElementById('no-alerts-msg');
            if (msg) msg.remove();
        }
    }

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            current = btn.dataset.filter;
            renderAlerts();
        });
    });

    q.addEventListener('input', renderAlerts);
    sortSel.addEventListener('change', renderAlerts);

    // أول تحميل
    renderAlerts();
</script>

</body>
</html>
