<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        
        $userRole = Auth::user()->role->name ?? null;

        // 3. التحقق مما إذا كان دور المستخدم موجوداً في قائمة الأدوار المسموح بها
        if (in_array($userRole, $roles)) {
            return $next($request); // السماح بالوصول
        }

        // 4. رفض الوصول إذا لم يكن لديه الدور
        return response()->json(['message' => 'Unauthorized access. Insufficient privileges.'], 403);
    }
}
