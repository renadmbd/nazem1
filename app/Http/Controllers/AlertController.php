<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Carbon\Carbon;

class AlertController extends Controller
{
    public function index()
    {
        $items = Item::all();
        $alerts = [];
        $today = Carbon::today();
        $expiryLimit = 14;

        foreach ($items as $item) {
            $qty  = (int)($item->stock_availability ?? 0);
            $min  = (int)($item->min_qty ?? 0);
            $name = $item->name ?? '(Unnamed item)';

            $expiryDate = $item->expiration_date
                ? Carbon::parse($item->expiration_date)
                : null;

            $daysLeft = $expiryDate
                ? $today->diffInDays($expiryDate, false) // ممكن يكون سالب = منتهي
                : null;

            // Out
            if ($qty <= 0) {
                $alerts[] = (object) [
                    'type'      => 'out',
                    'name'      => $name,
                    'meta'      => 'Out of stock',
                    'item_id'   => $item->id,
                    'days_left' => $daysLeft,
                    'severity'  => 3,
                ];
            }
            // Low (أقل من الحد الأدنى ومو صفر)
            elseif ($qty < $min) {
                $alerts[] = (object) [
                    'type'      => 'low',
                    'name'      => $name,
                    'meta'      => "{$qty} / min {$min}",
                    'item_id'   => $item->id,
                    'days_left' => $daysLeft,
                    'severity'  => 2,
                ];
            }

            // Expiry خلال 14 يوم أو أقل (أو منتهي)
            if ($expiryDate && $daysLeft !== null && $daysLeft <= $expiryLimit) {
                $meta = $daysLeft < 0
                    ? 'Expired'
                    : "{$daysLeft} days remaining";

                $alerts[] = (object) [
                    'type'      => 'expiry',
                    'name'      => $name,
                    'meta'      => $meta,
                    'item_id'   => $item->id,
                    'days_left' => $daysLeft,
                    'severity'  => 1,
                ];
            }
        }

        $alertsCollection = collect($alerts);

        $counts = [
            'all'    => $alertsCollection->count(),
            'low'    => $alertsCollection->where('type', 'low')->count(),
            'out'    => $alertsCollection->where('type', 'out')->count(),
            'expiry' => $alertsCollection->where('type', 'expiry')->count(),
        ];

        return view('alerts', [
            'alerts'     => $alerts,
            'counts'     => $counts,
            'expiryDays' => $expiryLimit,
        ]);
    }
}
