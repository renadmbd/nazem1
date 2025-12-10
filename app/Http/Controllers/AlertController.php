<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $expiryDays = 14; // نفس القاعدة المكتوبة في أسفل الصفحة

        // تجميع الأصناف (لاحقاً ممكن نفلترها per user_id)
        $items  = Item::orderBy('id')->get();
        $today  = Carbon::today();
        $alerts = collect();

        foreach ($items as $item) {
            $type      = null; // low / out / expiry
            $meta      = '';
            $daysLeft  = null;
            $severity  = 0;   // للتصنيف: out=3, low=2, expiry=1

            // Out / Low
            if ($item->stock_availability <= 0) {
                $type     = 'out';
                $severity = 3;
                $meta     = 'Out of stock';
            } elseif ($item->stock_availability <= $item->min_qty) {
                $type     = 'low';
                $severity = 2;
                $meta     = $item->stock_availability.' / min '.$item->min_qty;
            }

            // Expiry
            if ($item->expiration_date) {
                $diff = $today->diffInDays(Carbon::parse($item->expiration_date), false);

                if ($diff <= $expiryDays) {
                    $type     = 'expiry';
                    $severity = max($severity, 1);
                    $daysLeft = $diff;

                    if ($diff >= 0) {
                        $meta = $diff.' days remaining';
                    } else {
                        $meta = 'Expired '.abs($diff).' days ago';
                    }
                }
            }

            if ($type) {
                $alerts->push((object) [
                    'type'      => $type,
                    'item_id'   => $item->id,
                    'name'      => $item->name,
                    'meta'      => $meta,
                    'days_left' => $daysLeft,
                    'severity'  => $severity,
                ]);
            }
        }

        $counts = [
            'all'    => $alerts->count(),
            'low'    => $alerts->where('type', 'low')->count(),
            'out'    => $alerts->where('type', 'out')->count(),
            'expiry' => $alerts->where('type', 'expiry')->count(),
        ];

        return view('alerts', [
            'alerts'     => $alerts,
            'counts'     => $counts,
            'expiryDays' => $expiryDays,
        ]);
    }
}
