<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shipment;
use App\Models\ShipmentStatusHistory;
use Illuminate\Support\Str;

class ShipmentController extends Controller
{
    /**
     * 📦 عرض جميع الشحنات
     */
    public function index()
    {
        $shipments = Shipment::with('user:id,username,name')->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $shipments,
        ]);
    }

    /**
     * 🔍 عرض تفاصيل شحنة واحدة
     */
    public function show($tracking_number)
    {
        $shipment = Shipment::with([
            'user:id,username,name',
            'statusHistories' => function ($q) {
                $q->with('user:id,username,name')->orderBy('created_at', 'asc');
            },
        ])
        ->where('tracking_number', $tracking_number)
        ->first();

        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => '❌ لم يتم العثور على الشحنة المطلوبة',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $shipment,
        ]);
    }

    /**
     * ➕ إضافة شحنة جديدة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tracking_number' => 'nullable|string|unique:shipments,tracking_number',
            'customer_name' => 'nullable|string|max:255',
            'customer_location' => 'nullable|string|max:255',
            'customer_whatsapp' => 'nullable|string|max:50',
            'price_usd' => 'nullable|numeric',
            'price_lyd' => 'nullable|numeric',
            'quantity' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'status_code' => 'required|integer|min:1|max:4',
        ]);

        // 🎯 إجبار النظام على استخدام user_id للمستخدم الحالي
        $validated['user_id'] = auth()->id();

        // رقم تتبع عشوائي

        // إنشاء الشحنة
        $shipment = Shipment::create($validated);

        // إنشاء أول سجل حالة
        ShipmentStatusHistory::create([
            'shipment_id' => $shipment->id,
            'status_code' => $validated['status_code'],
            'note' => null,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ تم إنشاء الشحنة بنجاح',
            'data' => $shipment,
        ]);
    }

    /**
     * ✏️ تعديل شحنة
     */
    public function update(Request $request, $id)
{
    $shipment = Shipment::findOrFail($id);

    // نحتفظ بالحالة القديمة
    $oldStatus = $shipment->status_code;

    $validated = $request->validate([
        'tracking_number'    => 'nullable|string|unique:shipments,tracking_number,' . $shipment->id,
        'status_code'        => 'nullable|integer|between:1,4',
        'customer_name'      => 'nullable|string',
        'customer_whatsapp'  => 'nullable|string',
        'price_usd'          => 'nullable|numeric',
        'price_lyd'          => 'nullable|numeric',
        'quantity'           => 'nullable|integer|min:1',
        'description'        => 'nullable|string',
    ]);

    // ❌ منع تغيير الموظف المسؤول
    unset($validated['user_id']);

    // تحديث بيانات الشحنة
    $shipment->update($validated);

    // ✅ إذا تغيّرت الحالة → سجل في history
    if (
        isset($validated['status_code']) &&
        $oldStatus != $validated['status_code']
    ) {
        ShipmentStatusHistory::create([
            'shipment_id' => $shipment->id,
            'status_code' => $validated['status_code'],
            'note'        => null,
            'user_id'     => auth()->id(),
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => '✅ تم تحديث بيانات الشحنة بنجاح',
        'data'    => $shipment->fresh(), // ⬅️ مهم
    ]);
}


    /**
     * 🗑️ حذف شحنة
     */
    public function destroy($tracking_number)
    {
        $shipment = Shipment::where('tracking_number', $tracking_number)->firstOrFail();
        $shipment->delete();

        return response()->json([
            'success' => true,
            'message' => '🗑️ تم حذف الشحنة بنجاح',
        ]);
    }

    public function destroyAll()
{
    ShipmentStatusHistory::truncate();
    Shipment::truncate();

    return response()->json([
        'success' => true,
        'message' => '🗑️ تم حذف جميع الشحنات بنجاح'
    ]);
}


    /**
     * 🔁 تحديث عدة شحنات دفعة واحدة
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'tracking_numbers' => 'required|array',
            'tracking_numbers.*' => 'string|exists:shipments,tracking_number',
            'status_code' => 'required|integer|min:1|max:4',
        ]);

        $shipments = Shipment::whereIn('tracking_number', $validated['tracking_numbers'])->get();

        foreach ($shipments as $shipment) {
            $oldStatus = $shipment->status_code;

            $shipment->update(['status_code' => $validated['status_code']]);

            if ($oldStatus != $validated['status_code']) {
                ShipmentStatusHistory::create([
                    'shipment_id' => $shipment->id,
                    'status_code' => $validated['status_code'],
                    'note' => null,
                    'user_id' => auth()->id(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => '✅ تم تحديث الحالات بنجاح',
        ]);
    }
}
