<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    // 📦 عرض كل الشحنات
    public function index()
    {
        return response()->json(Shipment::all(), 200);
    }

    // 🔍 عرض شحنة واحدة عبر رقم التتبع
    public function show($tracking_number)
    {
        $shipment = Shipment::where('tracking_number', $tracking_number)->first();

        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'الشحنة غير موجودة أو رقم التتبع غير صحيح',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'tracking_number' => $shipment->tracking_number,
            'status_code' => $shipment->status_code,
            'updated_at' => $shipment->updated_at->format('Y-m-d H:i:s'),
        ], 200);
    }

    // ➕ إنشاء شحنة جديدة
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tracking_number' => 'required|unique:shipments',
            'status_code' => 'nullable|integer|min:1|max:4',
        ]);

        $shipment = Shipment::create([
            'tracking_number' => $validated['tracking_number'],
            'status_code' => $validated['status_code'] ?? 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الشحنة بنجاح',
            'data' => $shipment,
        ], 201);
    }

    // 🔄 تحديث حالة الشحنة
    public function update(Request $request, $tracking_number)
    {
        $shipment = Shipment::where('tracking_number', $tracking_number)->first();

        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'الشحنة غير موجودة'], 404);
        }

        $request->validate(['status_code' => 'required|integer|min:1|max:4']);

        $shipment->update(['status_code' => $request->status_code]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الشحنة بنجاح',
            'data' => $shipment,
        ], 200);
    }

    // ❌ حذف شحنة
    public function destroy($tracking_number)
    {
        $shipment = Shipment::where('tracking_number', $tracking_number)->first();

        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'الشحنة غير موجودة'], 404);
        }

        $shipment->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف الشحنة بنجاح'], 200);
    }
}
