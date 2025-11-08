<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// 🟦 Controllers
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ExchangeRateController;
use App\Http\Controllers\Api\ShipmentController;        
use App\Http\Controllers\Api\UpdateController;
use App\Http\Controllers\Api\ShippingRateController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| هذه المسارات تُستخدم للوصول إلى واجهات الـ API الخاصة بالنظام.
| وهي تعمل جميعها عبر الرابط الأساسي: /api
| مثال: https://wepay-backend-y41w.onrender.com/api/categories
|
*/

/* 🧩 قسم التصنيفات (Categories)
----------------------------------- */
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);       // جلب جميع الأصناف
    Route::post('/', [CategoryController::class, 'store']);      // إضافة صنف جديد
    Route::put('/{id}', [CategoryController::class, 'update']);  // تعديل صنف
    Route::delete('/{id}', [CategoryController::class, 'destroy']); // حذف صنف
    Route::get('/{id}', [CategoryController::class, 'show']);
});

/* 💱 قسم أسعار الصرف (Exchange Rates)
----------------------------------- */
Route::prefix('exchange-rate')->group(function () {
    Route::get('/', [ExchangeRateController::class, 'index']);   // جلب آخر سعر صرف
    Route::post('/', [ExchangeRateController::class, 'update']); // إضافة أو تحديث سعر صرف جديد
});

/* 🚚 قسم تتبع الشحنة  
----------------------------------- */
Route::prefix('shipments')->group(function () {
    Route::get('/', [ShipmentController::class, 'index']);
    Route::get('/{tracking_number}', [ShipmentController::class, 'show']);
    Route::post('/', [ShipmentController::class, 'store']);
    Route::put('/{tracking_number}', [ShipmentController::class, 'update']);
    Route::delete('/{tracking_number}', [ShipmentController::class, 'destroy']);
});

/* 📰 قسم آخر التحديثات     
----------------------------------- */
Route::get('/updates', [UpdateController::class, 'index']);        // جلب كل التحديثات
Route::post('/updates', [UpdateController::class, 'store']);       // إنشاء تحديث جديد
Route::delete('/updates/{id}', [UpdateController::class, 'destroy']); // حذف تحديث

/* 💰 قسم سعر الشحن     
----------------------------------- */
Route::get('/shipping-rate', [ShippingRateController::class, 'index']); // جلب السعر
Route::post('/shipping-rate', [ShippingRateController::class, 'store']); // تعديل السعر

/* 🧠 اختبار قاعدة البيانات (اختبار داخلي فقط)
----------------------------------- */
Route::get('/test-db', function () {
    try {
        // تحقق من وجود جدول التصنيفات
        if (!Schema::hasTable('categories')) {
            return response()->json([
                'status' => false,
                'message' => '⚠️ جدول categories غير موجود في قاعدة البيانات'
            ], 500);
        }

        // تحقق من إمكانية الكتابة في قاعدة البيانات
        DB::table('categories')->insert([
            'name' => 'اختبار قاعدة البيانات',
            'approx_weight' => 1.0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => '✅ قاعدة البيانات تعمل بشكل سليم ويمكن الكتابة فيها'
        ], 200);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => '❌ خطأ أثناء الاتصال بقاعدة البيانات',
            'error' => $e->getMessage(),
        ], 500);
    }
});
