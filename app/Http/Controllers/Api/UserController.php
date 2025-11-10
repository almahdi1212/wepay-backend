<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use App\Models\User;

class UserController extends Controller
{
    /**
     * 📋 عرض جميع المستخدمين
     */
    public function index()
    {
        $users = User::select('id', 'username', 'created_at')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['data' => $users]);
    }

    /**
     * ➕ إضافة مستخدم جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
        ]);

        try {
            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'message' => '✅ تم إنشاء المستخدم بنجاح',
                'user' => $user,
            ], 201);
        } catch (QueryException $e) {
            // 🟡 معالجة حالة تكرار اسم المستخدم
            if ($e->getCode() == 23000) {
                return response()->json([
                    'message' => '⚠️ اسم المستخدم مستخدم بالفعل، يرجى اختيار اسم آخر',
                ], 409);
            }

            return response()->json([
                'message' => '❌ حدث خطأ أثناء إنشاء المستخدم',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ✏️ تعديل بيانات المستخدم
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'username' => 'required|string|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:6',
        ]);

        $updateData = [
            'username' => $request->username,
        ];

        if (!empty($request->password)) {
            $updateData['password'] = Hash::make($request->password);
        }

        try {
            $user->update($updateData);

            return response()->json(['message' => '✅ تم تحديث المستخدم بنجاح']);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                return response()->json([
                    'message' => '⚠️ اسم المستخدم مستخدم بالفعل، يرجى اختيار اسم آخر',
                ], 409);
            }

            return response()->json([
                'message' => '❌ حدث خطأ أثناء تحديث المستخدم',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🗑️ حذف مستخدم
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => '🗑️ تم حذف المستخدم بنجاح']);
    }

    /**
     * 🔑 تغيير كلمة المرور
     */
    public function changePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|different:current_password',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => '❌ كلمة المرور الحالية غير صحيحة'], 401);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json(['message' => '✅ تم تغيير كلمة المرور بنجاح']);
    }
}
