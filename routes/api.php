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
use App\Http\Controllers\Api\AuthController; // ✅ فقط هذا الـ use (احذف المكرر)

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| كل المسارات تبدأ بـ /api
| مثال: https://wepay-backend-y41w.onrender.com/api/categories
|
*/

/* 🔐 تسجيل الدخول */
Route::post('/login', [AuthController::class, 'login']);

/* 🧠 اختبار بسيط للتأكد من الاتصال */
Route::get('/test-db', function () {
    try {
        if (!Schema::hasTable('categories')) {
            return response()->json([
                'status' => false,
                'message' => '⚠️ جدول categories غير موجود في قاعدة البيانات'
            ], 500);
        }

        DB::table('categories')->insert([
            'name' => 'اختبار قاعدة البيانات',
            'approx_weight' => 1.0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => '✅ قاعدة البيانات تعمل ويمكن الكتابة فيها'
        ], 200);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => '❌ خطأ أثناء الاتصال بقاعدة البيانات',
            'error' => $e->getMessage(),
        ], 500);
    }
});

/*
|--------------------------------------------------------------------------
| 🟡 المسارات المحمية (تحتاج Token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /* 🔴 تسجيل الخروج */
    Route::post('/logout', [AuthController::class, 'logout']);

    /* 🧩 قسم التصنيفات (Categories) */
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
        Route::get('/{id}', [CategoryController::class, 'show']);
    });

    /* 💱 قسم أسعار الصرف (Exchange Rates) */
    Route::prefix('exchange-rate')->group(function () {
        Route::get('/', [ExchangeRateController::class, 'index']);
        Route::post('/', [ExchangeRateController::class, 'update']);
    });

    /* 🚚 قسم الشحنات (Shipments) */
    Route::prefix('shipments')->group(function () {
        Route::get('/', [ShipmentController::class, 'index']);
        Route::get('/{tracking_number}', [ShipmentController::class, 'show']);
        Route::post('/', [ShipmentController::class, 'store']);
        Route::put('/{tracking_number}', [ShipmentController::class, 'update']);
        Route::delete('/{tracking_number}', [ShipmentController::class, 'destroy']);
    });

    /* 📰 قسم آخر التحديثات (Updates) */
    Route::get('/updates', [UpdateController::class, 'index']);
    Route::post('/updates', [UpdateController::class, 'store']);
    Route::delete('/updates/{id}', [UpdateController::class, 'destroy']);

    /* 💰 قسم سعر الشحن (Shipping Rate) */
    Route::get('/shipping-rate', [ShippingRateController::class, 'index']);
    Route::post('/shipping-rate', [ShippingRateController::class, 'store']);

    /* 🧍 بيانات المستخدم الحالي */
    Route::get('/user', function (\Illuminate\Http\Request $request) {
        return $request->user();
    });
});
