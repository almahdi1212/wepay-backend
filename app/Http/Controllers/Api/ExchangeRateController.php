<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    // 🟢 جلب آخر سعر صرف مسجل
    public function index()
    {
        $rate = ExchangeRate::latest()->first();
        return response()->json($rate);
    }

    // 🟡 تحديث أو إضافة سعر صرف جديد
    public function update(Request $request)
    {
        $request->validate([
            'rate' => 'required|numeric|min:0',
            'currency_from' => 'required|string|max:3',
            'currency_to' => 'required|string|max:3',
        ]);

        $rate = ExchangeRate::create($request->all());
        return response()->json($rate, 201);
    }
}
