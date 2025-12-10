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

        {{-- ===== Filter tabs + Search + Sort ===== --}}
        <div class="card" style="margin-bottom:16px;">
            <div class="filters" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <button type="button" class="chip chip-tab active" data-filter="all">
                    All <span class="badge">{{ $counts['all'] }}</span>
                </button>
                <button type="button" class="chip chip-tab" data-filter="low">
                    Low <span class="badge">{{ $counts['low'] }}</span>
                </button>
                <button type="button" class="chip chip-tab" data-filter="out">
                    Out <span class="badge">{{ $counts['out'] }}</span>
                </button>
                <button type="button" class="chip chip-tab" data-filter="expiry">
                    Expiry <span class="badge">{{ $counts['expiry'] }}</span>
                </button>

                <div style="margin-left:auto;display:flex;gap:8px;flex:1;max-width:520px;">
                    <input id="q" class="input" type="search" placeholder="Search item…" style="flex:1;">
                    <select id="sort" class="select" style="min-width:170px;">
                        <option value="severity">Sort: Severity</option>
                        <option value="expirySoon">Sort: Expiry soon</option>
                        <option value="name">Sort: Name</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- ===== Alerts list (ملونة) ===== --}}
        <ul id="list" class="alert-list">
            @forelse($alerts as $alert)
                @php
                    $cardClass = 'alert-' . $alert->type; // alert-low / alert-out / alert-expiry
                    $pillClass = $alert->type === 'out'
                        ? 'pill-out'
                        : ($alert->type === 'low'
                            ? 'pill-low'
                            : 'pill-exp');
                @endphp

                <li class="alert-card {{ $cardClass }}"
                    data-type="{{ $alert->type }}"
                    data-name="{{ strtolower($alert->name) }}"
                    data-days="{{ $alert->days_left ?? 99999 }}"
                    data-severity="{{ $alert->severity ?? 0 }}">

                    <div class="alert-header">
                        <span class="pill {{ $pillClass }}">
                            {{ strtoupper($alert->type) }}
                        </span>
                        <span class="alert-id">#{{ $alert->item_id }}</span>
                    </div>

                    <p class="alert-title">{{ $alert->name }}</p>
                    <p class="alert-meta">{{ $alert->meta }}</p>
                </li>
            @empty
            @endforelse
        </ul>

        {{-- رسالة "No alerts" نتحكم فيها بالجافاسكربت --}}
        <p id="empty-msg" class="helper" style="{{ $alerts->isEmpty() ? '' : 'display:none;' }}">
            No alerts.
        </p>

        <p class="helper" style="margin-top:8px;">
            Rules: Out (qty=0), Low (qty ≤ min), Expiry (≤ {{ $expiryDays }} days)
        </p>
    </div>
</main>

<script>
    const EXPIRY_DAYS = {{ $expiryDays }};
    const listEl   = document.getElementById('list');
    const allItems = Array.from(listEl.querySelectorAll('li.alert-card'));
    const buttons  = Array.from(document.querySelectorAll('[data-filter]'));
    const q        = document.getElementById('q');
    const sortSel  = document.getElementById('sort');
    const emptyMsg = document.getElementById('empty-msg');

    let currentFilter = 'all';

    function renderAlerts() {
        const qv   = q.value.trim().toLowerCase();
        const mode = sortSel.value;

        let filtered = allItems.slice();

        // filter by type
        if (currentFilter !== 'all') {
            filtered = filtered.filter(li => li.dataset.type === currentFilter);
        }

        // search
        if (qv) {
            filtered = filtered.filter(li => li.dataset.name.includes(qv));
        }

        // sort
        filtered.sort((a, b) => {
            if (mode === 'name') {
                return a.dataset.name.localeCompare(b.dataset.name);
            }
            if (mode === 'expirySoon') {
                const da = Number(a.dataset.days || 99999);
                const db = Number(b.dataset.days || 99999);
                return da - db; // الأقل أيام يطلع فوق
            }
            // severity (out > low > expiry)
            const sa = Number(a.dataset.severity || 0);
            const sb = Number(b.dataset.severity || 0);
            if (sb !== sa) return sb - sa;
            return a.dataset.name.localeCompare(b.dataset.name);
        });

        // أولاً نخفي الكل
        allItems.forEach(li => li.style.display = 'none');

        // نعرض فقط اللي بعد الفلترة بالترتيب الجديد
        filtered.forEach(li => {
            li.style.display = '';
            listEl.appendChild(li); // يعيد ترتيب العناصر في الـ DOM
        });

        // تفعيل زر الفلتر
        buttons.forEach(b => {
            b.classList.toggle('active', b.dataset.filter === currentFilter);
        });

        // رسالة "No alerts"
        if (filtered.length === 0) {
            emptyMsg.style.display = '';
        } else {
            emptyMsg.style.display = 'none';
        }
    }

    // events
    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            currentFilter = btn.dataset.filter;
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

