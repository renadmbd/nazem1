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
        $categoryFilter    = $request->get('category');
        $statusFilter      = $request->get('status');
        $searchQuery       = $request->get('q');
        $forecastProductId = $request->get('forecast_product');

        $expiryDays = 14;
        $today = Carbon::today();

        $categoryOptions = Item::distinct('category')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        $itemBaseQuery = Item::query();

        if (!empty($categoryFilter)) {
            $itemBaseQuery->where('category', $categoryFilter);
        }

        if (!empty($searchQuery)) {
            $itemBaseQuery->where('name', 'like', '%' . $searchQuery . '%');
        }

        $items = $itemBaseQuery->orderBy('id')->get();

        $filteredItems = $items->filter(function ($item) use ($statusFilter, $today, $expiryDays) {
            $isOut = $item->stock_availability <= 0;
            $isLow = !$isOut && $item->stock_availability <= $item->min_qty;

            $isExpiry = false;
            if ($item->expiration_date) {
                $diff = $today->diffInDays(Carbon::parse($item->expiration_date), false);
                $isExpiry = $diff <= $expiryDays;
            }

            if (empty($statusFilter)) {
                return true;
            }

            return match ($statusFilter) {
                'out'    => $isOut,
                'low'    => $isLow,
                'expiry' => $isExpiry,
                'safe'   => !$isOut && !$isLow && !$isExpiry,
                default  => true,
            };
        })->values();

        $filteredItemIds = $filteredItems->pluck('id');
        $itemNameMap     = $filteredItems->pluck('name', 'id');

        $safeItems = 0;
        $lowItems = 0;
        $outItems = 0;
        $expiryItems = 0;

        foreach ($filteredItems as $item) {
            $isOut = $item->stock_availability <= 0;
            $isLow = !$isOut && $item->stock_availability <= $item->min_qty;

            $isExpiry = false;
            if ($item->expiration_date) {
                $diff = $today->diffInDays(Carbon::parse($item->expiration_date), false);
                $isExpiry = $diff <= $expiryDays;
            }

            if ($isOut) {
                $outItems++;
            } elseif ($isLow) {
                $lowItems++;
            } elseif ($isExpiry) {
                $expiryItems++;
            } else {
                $safeItems++;
            }
        }

        $totalItems = $filteredItems->count();

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

        $orderBase = Order::query();

        if ($filteredItemIds->isNotEmpty()) {
            $orderBase->whereIn('item_id', $filteredItemIds);
        }

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

        $sales = (clone $orderBase)
            ->selectRaw('DATE(created_at) as date, SUM(quantity_sold) as total_sold')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $salesLabels = $sales->pluck('date');
        $salesValues = $sales->pluck('total_sold');

        $historyQuery = Order::selectRaw('
            item_id as product_id,
            quantity_sold,
            DATE(created_at) as date
        ');

        if ($forecastProductId) {
            $historyQuery->where('item_id', $forecastProductId);
        } elseif ($filteredItemIds->isNotEmpty()) {
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

            $tmpFile = storage_path('app/forecast_input.json');

            logger()->info('Forecast tmp file target', [
                'tmpFile' => $tmpFile,
            ]);

            file_put_contents($tmpFile, $jsonInput);

            logger()->info('Forecast tmp file written', [
                'exists' => file_exists($tmpFile),
                'size' => file_exists($tmpFile) ? filesize($tmpFile) : 0,
            ]);

            // إذا احتجتي لاحقًا، استبدلي python بالمسار الكامل من where python
            $python  = 'python';
            $script  = base_path('ai_model/predict_demand.py');
            $command = $python . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($tmpFile);

            $output = [];
            $returnVar = 0;

            exec($command . ' 2>&1', $output, $returnVar);

            //@unlink($tmpFile);

            logger()->info('Forecast command', [
                'command' => $command,
            ]);

            logger()->info('Forecast output', [
                'output' => $output,
            ]);

            logger()->info('Forecast return code', [
                'returnVar' => $returnVar,
            ]);

            if ($returnVar === 0 && !empty($output)) {
                $lastLine = $output[count($output) - 1];
                $result   = json_decode($lastLine, true);

                if ($result && !isset($result['error']) && !empty($result['predictions'])) {
                    $forecastLabels = $result['dates'] ?? $futureDates;
                    $forecastValues = $result['predictions'] ?? [];
                } else {
                    logger()->error('Forecast JSON invalid', [
                        'lastLine' => $lastLine,
                        'decoded'  => $result,
                    ]);
                }
            } else {
                logger()->error('Forecast script failed', [
                    'command'   => $command,
                    'output'    => $output,
                    'returnVar' => $returnVar,
                ]);
            }
        }

        $forecastItems = $filteredItems->sortBy('name')->values();
        $selectedForecastItem = $forecastProductId
            ? Item::find($forecastProductId)
            : null;

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