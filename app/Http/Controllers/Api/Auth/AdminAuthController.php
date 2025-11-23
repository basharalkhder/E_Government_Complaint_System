<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. التحقق من البيانات
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. محاولة المصادقة (Authentication)
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::guard('sanctum')->user();

        // 💡 الشرط الحاسم: يجب أن يكون الدور إما 'admin' أو 'employee'
        if (!$user->hasRole('admin') && !$user->hasRole('employee')) {
            // تسجيل الخروج وإرسال رسالة خطأ
            Auth::guard('web')->logout();
            return response()->json(['message' => 'Access denied. You must be an admin or employee.'], 403);
        }

        // 4. إصدار Sanctum Token
        $token = $user->createToken('admin_auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->only(['id', 'name', 'email', 'role_id']),
            'role' => $user->role->name,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.'], 200);
    }
}
