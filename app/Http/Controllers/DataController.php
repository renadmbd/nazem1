<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DataController extends Controller
{
    // عرض صفحة الداتا
    public function index()
    {
        $items = Item::orderBy('id')->get();
        return view('data', compact('items'));
    }

    // استيراد CSV
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt',
        ]);

        $file   = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        // قراءة أول سطر = العناوين
        $header = fgetcsv($handle, 1000, ',');

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {

            $data = array_combine($header, $row);

            $id      = $data['id'] ?? null;
            $category = $data['category'] ?? 'general';
            $name    = $data['name'] ?? '';
            $price   = isset($data['price']) ? (float)$data['price'] : 0;
            $qty     = (int)($data['qty'] ?? 0);
            $minQty  = (int)($data['min_qty'] ?? 0);

            // معالجة تاريخ الانتهاء
            $rawExpiry = trim($data['expiry'] ?? '');
            $expiry    = null;

            if ($rawExpiry !== '') {
                try {
                    $expiry = Carbon::createFromFormat('Y-m-d', $rawExpiry)->format('Y-m-d');
                } catch (\Exception $e) {
                    try {
                        $expiry = Carbon::createFromFormat('d/m/Y', $rawExpiry)->format('Y-m-d');
                    } catch (\Exception $e2) {
                        $expiry = null;
                    }
                }
            }

            $status = $this->calculateStatus($qty, $minQty, $expiry);

            // إنشاء السجل — إذا id موجود نستخدمه
            $itemData = [
                'category'           => $category,
                'name'               => $name,
                'price'              => $price,
                'stock_availability' => $qty,
                'min_qty'            => $minQty,
                'status'             => $status,
                'expiration_date'    => $expiry ?? Carbon::today()->format('Y-m-d'),
            ];

            if ($id !== null && $id !== '') {
                $itemData['id'] = (int)$id;
            }

            Item::create($itemData);
        }

        fclose($handle);

        return redirect()
            ->route('data')
            ->with('success', 'Data imported successfully.');
    }

    // Quick Sell
    public function quickSell(Request $request)
    {
        $request->validate([
            'item_id'  => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        $item = Item::find($request->item_id);

        if (! $item) return back()->with('sell_msg', 'Item not found.');
        if ($item->stock_availability < $request->quantity)
            return back()->with('sell_msg', 'Not enough stock.');

        $item->stock_availability -= $request->quantity;
        $item->status = $this->calculateStatus(
            $item->stock_availability,
            $item->min_qty,
            $item->expiration_date
        );
        $item->save();

        Order::create([
            'user_id'       => Auth::id(),
            'item_id'       => $item->id,
            'quantity_sold' => $request->quantity,
        ]);

        return back()->with('sell_msg', 'Sale recorded successfully.');
    }

    // حساب الحالة
    private function calculateStatus(int $qty, int $minQty, $expiryDate): string
    {
        $today  = Carbon::today();
        $expiry = $expiryDate ? Carbon::parse($expiryDate) : null;

        if ($qty <= 0) return 'out';
        if ($expiry && $expiry->lessThanOrEqualTo($today->copy()->addDays(14)))
            return 'expiry';
        if ($qty <= $minQty) return 'low';

        return 'safe';
    }
}
