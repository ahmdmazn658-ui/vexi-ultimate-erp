<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * core/permissions — تحقق بسيط من الدور (role) قبل تنفيذ إجراءات حساسة
 * (ترحيل قيود، تشغيل الرواتب...). الأدوار المتاحة حاليًا على المستخدم:
 * admin | manager | employee | accountant (شوف App\Models\User).
 *
 * الاستخدام في الراوت: ->middleware('role:admin,accountant')
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            return response()->json([
                'message' => 'ما عندكش صلاحية تنفيذ هذا الإجراء.',
            ], 403);
        }

        return $next($request);
    }
}
