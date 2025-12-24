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
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| 🔐 تسجيل الدخول والخروج
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| 🟢 المسارات العامة (لا تتطلب تسجيل الدخول)
|--------------------------------------------------------------------------
*/

// 🧩 التصنيفات
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

// 💱 أسعار الصرف
Route::get('/exchange-rate', [ExchangeRateController::class, 'index']);

// 💰 سعر الشحن
Route::get('/shipping-rate', [ShippingRateController::class, 'index']);

// 📰 آخر التحديثات
Route::get('/updates', [UpdateController::class, 'index']);

// 🚚 الشحنات (تتبع فقط)
Route::get('/shipments', [ShipmentController::class, 'index']);
Route::get('/shipments/{tracking_number}', [ShipmentController::class, 'show']);
Route::delete('/shipments', [ShipmentController::class, 'destroyAll']);
    // ✅ جديد: تحديث الحالة لعدة شحنات دفعة واحدة
    Route::put('/shipments/bulk-update', [ShipmentController::class, 'bulkUpdate']);

/*
|--------------------------------------------------------------------------
| 🔒 المسارات المحمية (تحتاج Token - auth:sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // 🔴 تسجيل الخروج
    Route::post('/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | 🧩 إدارة التصنيفات
    |--------------------------------------------------------------------------
    */
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | 💱 إدارة أسعار الصرف
    |--------------------------------------------------------------------------
    */
    Route::post('/exchange-rate', [ExchangeRateController::class, 'update']);

    /*
    |--------------------------------------------------------------------------
    | 💰 تعديل سعر الشحن
    |--------------------------------------------------------------------------
    */
    Route::post('/shipping-rate', [ShippingRateController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | 📰 إدارة التحديثات
    |--------------------------------------------------------------------------
    */
    Route::post('/updates', [UpdateController::class, 'store']);
    Route::delete('/updates/{id}', [UpdateController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | 🚚 إدارة الشحنات
    |--------------------------------------------------------------------------
    */
    Route::post('/shipments', [ShipmentController::class, 'store']);
    Route::put('/shipments/{id}', [ShipmentController::class, 'update']);

    Route::delete('/shipments/{tracking_number}', [ShipmentController::class, 'destroy']);
    




    /*
    |--------------------------------------------------------------------------
    | 👤 المستخدم الحالي
    |--------------------------------------------------------------------------
    */
    Route::get('/user', function (\Illuminate\Http\Request $request) {
        return $request->user();
    });

    /*
    |--------------------------------------------------------------------------
    | 👥 إدارة المستخدمين (محمي)
    |--------------------------------------------------------------------------
    | GET /users               -> يعرض قائمة المستخدمين
    | POST /users              -> يضيف مستخدم جديد
    | PUT /users/{id}          -> يحدّث بيانات مستخدم
    | DELETE /users/{id}       -> يحذف مستخدم
    | POST /users/{id}/change-password -> يغيّر كلمة مرور مستخدم
    */
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // تغيير كلمة المرور لنفس المستخدم (body: current_password, new_password)
    Route::post('/users/{id}/change-password', [UserController::class, 'changePassword']);
});

/*
|--------------------------------------------------------------------------
| 🧠 اختبار قاعدة البيانات (داخلي فقط)
|--------------------------------------------------------------------------
*/
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
