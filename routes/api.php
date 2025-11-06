<?php

use Illuminate\Support\Facades\Route;

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
| مثال: http://127.0.0.1:8000/api/categories
|
*/

/* 🧩 قسم التصنيفات (Categories)
----------------------------------- */
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);       // جلب جميع الأصناف
    Route::post('/', [CategoryController::class, 'store']);      // إضافة صنف جديد
    Route::put('/{id}', [CategoryController::class, 'update']);  // تعديل صنف
    Route::delete('/{id}', [CategoryController::class, 'destroy']); // حذف صنف
    Route::get('/{id}', [App\Http\Controllers\Api\CategoryController::class, 'show']);

});

/* 💱 قسم أسعار الصرف (Exchange Rates)
----------------------------------- */
Route::prefix('exchange-rate')->group(function () {
    Route::get('/', [ExchangeRateController::class, 'index']);   // جلب آخر سعر صرف
    Route::post('/', [ExchangeRateController::class, 'update']); // إضافة أو تحديث سعر صرف جديد
});


/*  قسم تتبع الشحنة  
----------------------------------- */
Route::prefix('shipments')->group(function () {
    Route::get('/', [ShipmentController::class, 'index']);
    Route::get('/{tracking_number}', [ShipmentController::class, 'show']);
    Route::post('/', [ShipmentController::class, 'store']);
    Route::put('/{tracking_number}', [ShipmentController::class, 'update']);
    Route::delete('/{tracking_number}', [ShipmentController::class, 'destroy']);
});

/*  قسم اخر التحديثات     
----------------------------------- */
Route::get('/updates', [UpdateController::class, 'index']);        // جلب كل التحديثات
Route::post('/updates', [UpdateController::class, 'store']);       // إنشاء تحديث جديد
Route::delete('/updates/{id}', [UpdateController::class, 'destroy']); // حذف تحديث


/*  قسم سعر الشحن     
----------------------------------- */
Route::get('/shipping-rate', [ShippingRateController::class, 'index']); // جلب السعر
Route::post('/shipping-rate', [ShippingRateController::class, 'store']); // تعديل السعر