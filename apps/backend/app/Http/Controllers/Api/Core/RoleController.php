<?php

namespace App\Http\Controllers\Api\Core;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Role::withCount(['users', 'permissions'])->orderBy('name')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:100|alpha_dash|unique:roles,slug',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'slug' => $validated['slug'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $role->permissions()->sync($validated['permission_ids'] ?? []);

        return response()->json($role->load('permissions'), 201);
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json($role->load('permissions', 'users:id,name,email'));
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role->update(collect($validated)->except('permission_ids')->toArray());

        if (array_key_exists('permission_ids', $validated)) {
            $role->permissions()->sync($validated['permission_ids']);
        }

        $this->flushRoleUsersCache($role);

        return response()->json($role->load('permissions'));
    }

    public function destroy(Role $role): JsonResponse
    {
        if ($role->is_system) {
            return response()->json(['message' => 'مينفعش تمسح دور أساسي في النظام.'], 422);
        }

        $this->flushRoleUsersCache($role);
        $role->delete();

        return response()->json(null, 204);
    }

    /** إضافة/إزالة دور إضافي لمستخدم معيّن (فوق دوره الأساسي). */
    public function syncUserRoles(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $user->additionalRoles()->sync($validated['role_ids']);
        $user->forgetPermissionsCache();

        return response()->json($user->load('additionalRoles'));
    }

    private function flushRoleUsersCache(Role $role): void
    {
        $role->users()->each(fn (User $user) => $user->forgetPermissionsCache());
    }
}
