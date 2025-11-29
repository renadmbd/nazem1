<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ===== 1) فلاتر الواجهة (بدون range) =====
        $categoryFilter    = $request->get('category');
        $statusFilter      = $request->get('status');
        $searchQuery       = $request->get('q');
        $forecastProductId = $request->get('forecast_product'); // ممكن تكون null

        // جلب قائمة الكاتيجوري من الداتا نفسها
        $categoryOptions = Item::distinct('category')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        // ===== 2) فلترة الأصناف (items) =====
        $itemBaseQuery = Item::query();

        if (!empty($categoryFilter)) {
            $itemBaseQuery->where('category', $categoryFilter);
        }

        if (!empty($statusFilter)) {
            $itemBaseQuery->where('status', $statusFilter);
        }

        if (!empty($searchQuery)) {
            $itemBaseQuery->where('name', 'like', '%' . $searchQuery . '%');
        }

        $filteredItems   = $itemBaseQuery->get();
        $filteredItemIds = $filteredItems->pluck('id');
        $itemNameMap     = $filteredItems->pluck('name', 'id');

        // ===== 3) KPIs + Status counts =====
        // ===== 3) KPIs + Status counts =====
$totalItems   = $filteredItems->count();
$safeItems    = $filteredItems->where('status', 'safe')->count();
$lowItems     = $filteredItems->where('status', 'low')->count();
$outItems     = $filteredItems->where('status', 'out')->count();
$expiryItems  = $filteredItems->where('status', 'expiry')->count();

$kpis = [
    'totalItems'  => $totalItems,
    'safeItems'   => $safeItems,
    'lowStock'    => $lowItems,
    'outOfStock'  => $outItems,
    'expiryItems' => $expiryItems,
];

$statusCounts = [
    'safe'   => $safeItems,
    'low'    => $lowItems,
    'out'    => $outItems,
    'expiry' => $expiryItems,
];


        //---

        // ===== 4) استعلام الطلبات الأساسي (كل الزمن – بدون range) =====
        $orderBase = Order::query();

        if ($filteredItemIds->isNotEmpty()) {
            $orderBase->whereIn('item_id', $filteredItemIds);
        }

        // ===== 5) Top by Quantity (bar chart) =====
        $top = (clone $orderBase)
            ->selectRaw('item_id, SUM(quantity_sold) as total_qty')
            ->groupBy('item_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $topLabels = $top->map(function ($t) use ($itemNameMap) {
            return $itemNameMap[$t->item_id] ?? ('Item #' . $t->item_id);
        });

        $topValues = $top->pluck('total_qty');

        // ===== 6) Sales history (line chart) – كل التاريخ =====
        $sales = (clone $orderBase)
            ->selectRaw('DATE(created_at) as date, SUM(quantity_sold) as total_sold')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $salesLabels = $sales->pluck('date');
        $salesValues = $sales->pluck('total_sold');

        // ===== 7) Demand Forecast (من XGBoost) =====
        $historyQuery = Order::selectRaw('
                item_id as product_id,
                quantity_sold,
                DATE(created_at) as date
            ');

        if ($forecastProductId) {
            // تنبؤ لمنتج معيّن
            $historyQuery->where('item_id', $forecastProductId);
        } elseif ($filteredItemIds->isNotEmpty()) {
            // أو تنبؤ لكل المنتجات ضمن الفلاتر الحالية
            $historyQuery->whereIn('item_id', $filteredItemIds);
        }

        $history = $historyQuery
            ->orderBy('date')
            ->limit(200)
            ->get()
            ->map(function ($row) {
                return [
                    'product_id'    => (int) $row->product_id,
                    'quantity_sold' => (int) $row->quantity_sold,
                    'date'          => $row->date,
                ];
            })
            ->toArray();

        // أفق التنبؤ: 30 يوم
        $horizon = 30;
        $futureDates = [];
        for ($i = 1; $i <= $horizon; $i++) {
            $futureDates[] = Carbon::today()->addDays($i)->format('Y-m-d');
        }

        $forecastLabels = $futureDates;
        $forecastValues = [];

        if (!empty($history)) {
            $payload = [
                'rows'         => $history,
                'future_dates' => $futureDates,
                'horizon'      => $horizon,
            ];

            $jsonInput = json_encode($payload, JSON_UNESCAPED_UNICODE);

            // نكتب JSON في ملف مؤقت
            $tmpFile = storage_path('app/forecast_input.json');
            file_put_contents($tmpFile, $jsonInput);

            $python  = 'python';
            $script  = base_path('ai_model/predict_demand.py');
            $command = $python . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($tmpFile);

            $output    = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            @unlink($tmpFile);

            if ($returnVar === 0 && !empty($output)) {
                $lastLine = $output[count($output) - 1];
                $result   = json_decode($lastLine, true);

                if ($result && !isset($result['error']) && !empty($result['predictions'])) {
                    $forecastLabels = $result['dates'];
                    $forecastValues = $result['predictions'];
                }
            }
        }

        // ===== 8) قائمة المنتجات للـ dropdown حق التنبؤ =====
        $forecastItems = $filteredItems->sortBy('name')->values();
        $selectedForecastItem = $forecastProductId
            ? Item::find($forecastProductId)
            : null;

        // ===== 9) تمرير البيانات للواجهة =====
        return view('dashboard', [
            'kpis'                     => $kpis,
            'statusCounts'             => $statusCounts,
            'topLabels'                => $topLabels,
            'topValues'                => $topValues,
            'salesLabels'              => $salesLabels,
            'salesValues'              => $salesValues,
            'forecastLabels'           => $forecastLabels,
            'forecastValues'           => $forecastValues,
            'forecastItems'            => $forecastItems,
            'selectedForecastItemId'   => $forecastProductId,
            'selectedForecastItemName' => $selectedForecastItem->name ?? null,
            'categoryOptions'          => $categoryOptions,
            'selectedCategory'         => $categoryFilter,
            'selectedStatus'           => $statusFilter,
            'searchQuery'              => $searchQuery,
        ]);
    }
}
