<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * core/permissions — تحقق دقيق بصلاحية واحدة أو أكتر (OR بينهم)، بدل
 * التحقق بالدور بالكامل زي EnsureUserHasRole. الاستخدام في الراوت:
 * ->middleware('permission:accounting.journal-entries.post')
 */
class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user || ! collect($permissions)->contains(fn ($p) => $user->hasPermission($p))) {
            return response()->json([
                'message' => 'ما عندكش صلاحية تنفيذ هذا الإجراء.',
            ], 403);
        }

        return $next($request);
    }
}
