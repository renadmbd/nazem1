<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NAZEM | Data</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('static/css/style.css') }}">

    <style>
        .upload-row {
            display: grid;
            grid-template-columns: 1.2fr 220px 1fr;
            gap: 28px;
            align-items: center;
        }

        .upload-file {
            margin-bottom: 0;
        }

        .import-btn {
            height: 56px;
            width: 220px;
            border-radius: 14px;
            font-size: 15px;
        }

        .upload-helper {
            text-align: center;
            margin: 0;
            line-height: 1.5;
            color: #6b7280;
        }

        .table-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .refresh-btn {
            background: none;
            border: none;
            font-size: 30px;
            cursor: pointer;
            line-height: 1;
        }

        .delete-btn {
            background: #e11d23;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
        }

        .delete-btn:hover {
            background: #b91c1c;
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
        }

        .inventory-table th {
            background: #f3f6fb;
            padding: 12px;
            text-align: center;
        }

        .inventory-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }

        .inventory-table tr:hover {
            background: #f8fafc;
        }

        @media (max-width: 900px) {
            .upload-row {
                grid-template-columns: 1fr;
            }

            .import-btn {
                width: 100%;
            }

            .upload-helper {
                text-align: left;
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
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <a class="active" href="{{ route('data') }}">Data</a>
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

        <h2 class="page-title">Data Management</h2>

        @if (session('success'))
            <p class="helper" style="color: green; text-align:left;">
                {{ session('success') }}
            </p>
        @endif

        @if (session('sell_msg'))
            <p class="helper" style="color: green; text-align:left;">
                {{ session('sell_msg') }}
            </p>
        @endif

        {{-- Upload CSV --}}
        <div class="card">
            <h3 class="card__title">Upload Excel/CSV</h3>

            <form method="POST" action="{{ route('data.import') }}" enctype="multipart/form-data">
                @csrf

                <div class="upload-row">
                    <div class="field upload-file">
                        <label>Choose file</label>
                        <input class="input" type="file" name="file" accept=".csv" required>
                    </div>

                    <button class="btn btn--primary import-btn" type="submit">
                        Import
                    </button>

                    <p class="helper upload-helper">
                        Columns: <b>id, category, name, price, qty,<br>
                        min_qty, expiry (YYYY-MM-DD)</b>
                    </p>
                </div>
            </form>
        </div>

        {{-- Preview Table --}}
        <div class="card" style="margin-top:16px;">

            <div class="table-actions">
                <form method="GET" action="{{ route('data') }}">
                    <button type="submit" class="refresh-btn" title="Refresh">↻</button>
                </form>

                <form method="POST" action="{{ route('data.deleteAll') }}">
                    @csrf
                    <button type="submit"
                            class="delete-btn"
                            onclick="return confirm('Are you sure you want to delete all data?')">
                        Delete All
                    </button>
                </form>
            </div>

            @if($items->isEmpty())
                <p class="helper">No data available. Please import a file.</p>
            @else
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category</th>
                            <th>Name</th>
                            <th>Qty</th>
                            <th>Min</th>
                            <th>Expiry</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->category }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->stock_availability }}</td>
                                <td>{{ $item->min_qty }}</td>
                                <td>{{ $item->expiration_date }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Quick Sell --}}
        <div class="card" style="margin-top:16px;">
            <h3 class="card__title">Quick Sell</h3>

            <form method="POST"
                  action="{{ route('data.quickSell') }}"
                  class="grid"
                  style="grid-template-columns: 1fr 1fr 1fr; gap:12px; align-items:end;">
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
                    <button class="btn btn--ghost" type="submit" style="height:58px; width:100%;">
                        Sell
                    </button>
                </div>
            </form>
        </div>

    </div>
</main>

</body>
</html>