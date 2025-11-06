<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Update;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    // 🔹 جلب كل التحديثات
    public function index()
{
    $updates = Update::all();

    return response()->json([
        'success' => true,
        'count' => $updates->count(),
        'data' => $updates,
    ], 200);
}


    // 🔹 إضافة تحديث جديد
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
        ]);

        $update = Update::create($request->all());
        return response()->json($update, 201);
    }

    // 🔹 حذف تحديث
    public function destroy($id)
    {
        Update::findOrFail($id)->delete();
        return response()->json(['message' => 'Update deleted successfully']);
    }
}
