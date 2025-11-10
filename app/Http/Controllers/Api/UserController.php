<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    /**
     * 📋 عرض جميع المستخدمين
     */
    public function index()
    {
        $users = User::select('id', 'name', 'username', 'created_at')
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
            'name' => 'required|string|max:100',
            'username' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => '✅ تم إنشاء المستخدم بنجاح',
            'user' => $user
        ], 201);
    }

    /**
     * ✏️ تعديل بيانات المستخدم
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // ✅ التحقق من نوع المستخدم (admin أو عادي)
        if ($user->username === 'admin') {
            // للمشرف الأساسي فقط: يمكن تعديل الاسم وكلمة المرور فقط
            $request->validate([
                'name' => 'required|string|max:100',
                'password' => 'nullable|string|min:6',
            ]);

            $updateData = [
                'name' => $request->name,
            ];

            if (!empty($request->password)) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return response()->json(['message' => '✅ تم تحديث بيانات المشرف بنجاح']);
        } else {
            // لباقي المستخدمين
            $request->validate([
                'name' => 'required|string|max:100',
                'username' => 'required|string|unique:users,username,' . $user->id,
                'password' => 'nullable|string|min:6',
            ]);

            $updateData = [
                'name' => $request->name,
                'username' => $request->username,
            ];

            if (!empty($request->password)) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return response()->json(['message' => '✅ تم تحديث المستخدم بنجاح']);
        }
    }

    /**
     * 🗑️ حذف مستخدم
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->username === 'admin') {
            return response()->json([
                'message' => '❌ لا يمكن حذف المستخدم الإداري الرئيسي'
            ], 403);
        }

        $user->delete();

        return response()->json(['message' => '🗑️ تم حذف المستخدم بنجاح']);
    }

    /**
     * 🔑 تغيير كلمة المرور (اختياري)
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
