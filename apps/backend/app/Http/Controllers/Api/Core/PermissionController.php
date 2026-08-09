<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    /** الصلاحيات كلها مجمّعة حسب الموديول — بتغذّي شاشة تعديل صلاحيات الدور. */
    public function index(): JsonResponse
    {
        $grouped = Permission::query()
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group')
            ->map(fn ($items) => $items->values());

        return response()->json($grouped);
    }
}
