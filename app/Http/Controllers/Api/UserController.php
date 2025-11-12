<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{
    /**
     * 📋 عرض جميع المستخدمين
     */
/**
 * 📋 عرض جميع المستخدمين (مسموح للجميع)
 */
public function index()
{
    // ✅ تم إزالة شرط isAdmin()
    $users = User::select('id', 'name', 'username', 'created_at')
        ->orderBy('id', 'desc')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $users,
    ]);
}


    /**
     * ➕ إضافة مستخدم جديد
     */
    public function store(Request $request)
    {
        if (!$this->isAdmin()) {
            return response()->json([
                'error_code' => 'FORBIDDEN',
                'message' => '🚫 غير مصرح لك بإضافة مستخدمين',
            ], 403);
        }

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
        if (!$this->isAdmin()) {
            return response()->json([
                'error_code' => 'FORBIDDEN',
                'message' => '🚫 غير مصرح لك بتعديل المستخدمين',
            ], 403);
        }

        $user = User::findOrFail($id);

        // ✅ لو المستخدم هو admin، يمكن تعديل الاسم وكلمة المرور فقط
        if ($user->username === 'admin') {
            $request->validate([
                'name' => 'required|string|max:100',
                'password' => 'nullable|string|min:6',
            ]);

            $updateData = ['name' => $request->name];

            if (!empty($request->password)) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return response()->json(['message' => '✅ تم تحديث بيانات المشرف بنجاح']);
        }

        // ✅ لباقي المستخدمين
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

    /**
     * 🗑️ حذف مستخدم
     */
    public function destroy($id)
    {
        if (!$this->isAdmin()) {
            return response()->json([
                'error_code' => 'FORBIDDEN',
                'message' => '🚫 غير مصرح لك بحذف المستخدمين',
            ], 403);
        }

        $user = User::findOrFail($id);

        if ($user->username === 'admin') {
            return response()->json([
                'error_code' => 'PROTECTED_ACCOUNT',
                'message' => '❌ لا يمكن حذف المستخدم الإداري الرئيسي',
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

        // ✅ السماح للمستخدم نفسه أو للمشرف admin فقط
        if (!$this->isAdmin() && Auth::id() != $user->id) {
            return response()->json([
                'error_code' => 'FORBIDDEN',
                'message' => '🚫 غير مصرح لك بتغيير كلمة المرور لهذا المستخدم',
            ], 403);
        }

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

    /**
     * 🧠 دالة مساعدة للتحقق من أن المستخدم الحالي هو admin
     */
    private function isAdmin()
    {
        $user = Auth::user();
        return $user && $user->username === 'admin';
    }
}
