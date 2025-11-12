<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shipment;
use App\Models\ShipmentStatusHistory; // <<-- استيراد الموديل المسؤول عن السجل
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
     * 🔍 عرض تفاصيل شحنة واحدة برقم التتبع مع سجل الحالات (مرتَّبًا زمنياً)
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
     * ➕ إضافة شحنة جديدة + تسجيل أول حالة (التاريخ يُخزّن تلقائياً باستخدام timestamps)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_location' => 'nullable|string|max:255',
            'customer_whatsapp' => 'nullable|string|max:50',
            'price_usd' => 'nullable|numeric',
            'price_lyd' => 'nullable|numeric',
            'quantity' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'user_id' => 'nullable|integer|exists:users,id',
            'status_code' => 'required|integer|min:1|max:4',
        ]);

        $validated['tracking_number'] = strtoupper(Str::random(8));

        $shipment = Shipment::create($validated);

        // تسجيل أول حالة (created_at سيحمل توقيت الإنشاء)
        ShipmentStatusHistory::create([
            'shipment_id' => $shipment->id,
            'status_code' => $validated['status_code'],
            'note' => null,
            'user_id' => auth()->id() ?? $validated['user_id'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ تم إنشاء الشحنة بنجاح',
            'data' => $shipment,
        ]);
    }

    /**
     * ✏️ تعديل شحنة موجودة — وفي حال تغيّر الكود الخاص بالحالة، نسجل السجل مع التاريخ
     */
    public function update(Request $request, $tracking_number)
    {
        $shipment = Shipment::where('tracking_number', $tracking_number)->firstOrFail();

        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_location' => 'nullable|string|max:255',
            'customer_whatsapp' => 'nullable|string|max:50',
            'price_usd' => 'nullable|numeric',
            'price_lyd' => 'nullable|numeric',
            'quantity' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'user_id' => 'nullable|integer|exists:users,id',
            'status_code' => 'required|integer|min:1|max:4',
        ]);

        $oldStatus = $shipment->status_code;
        $shipment->update($validated);

        // إذا تغيّرت الحالة، نسجّل السجل (created_at تلقائياً هو تاريخ التحديث)
        if ($oldStatus != $validated['status_code']) {
            ShipmentStatusHistory::create([
                'shipment_id' => $shipment->id,
                'status_code' => $validated['status_code'],
                'note' => null,
                'user_id' => auth()->id() ?? $validated['user_id'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => '✅ تم تحديث بيانات الشحنة بنجاح',
            'data' => $shipment,
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

    /**
     * 🔁 تحديث حالة عدة شحنات دفعة واحدة + تسجيل السجلات (timestamps محفوظة تلقائياً)
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
