<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use Illuminate\Http\Request;

class ShippingRateController extends Controller
{
    // 🔹 جلب سعر الشحن الحالي
    public function index()
    {
        $rate = ShippingRate::latest()->first();
        return response()->json([
            'success' => true,
            'rate_per_kg' => $rate ? $rate->rate_per_kg : 12.00, // افتراضي
        ]);
    }

    // 🔹 تحديث أو إنشاء سعر الشحن
    public function store(Request $request)
    {
        $request->validate([
            'rate_per_kg' => 'required|numeric|min:0',
        ]);

        $rate = ShippingRate::create($request->all());
        return response()->json([
            'success' => true,
            'data' => $rate,
        ]);
    }
}
