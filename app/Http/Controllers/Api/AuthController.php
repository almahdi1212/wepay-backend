<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * 🔐 تسجيل الدخول
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => '❌ اسم المستخدم أو كلمة المرور غير صحيحة'
            ], 401);
        }

        // ✅ إنشاء التوكن
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => '✅ تم تسجيل الدخول بنجاح',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username
            ]
        ]);
    }

    /**
     * 🚪 تسجيل الخروج
     */
    public function logout(Request $request)
    {
        // حذف جميع الرموز (tokens) الخاصة بالمستخدم الحالي
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => '🚪 تم تسجيل الخروج بنجاح'
        ]);
    }
}
