<?php

namespace App\Concerns;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

/**
 * يضاف لـ User. بيربط العمود القديم users.role (اللي لسه شغال ومستخدم في
 * كل الـ routes الحالية عبر middleware('role:...')) بنظام الأدوار
 * والصلاحيات الدقيقة الجديد، من غير ما يكسر أي حاجة موجودة.
 *
 * - hasRole()       بيتحقق من العمود القديم *و* الأدوار الإضافية.
 * - hasPermission() بيتحقق من صلاحيات كل أدوار المستخدم (القديم + الجديد).
 *   المستخدم اللي role="admin" عنده كل الصلاحيات تلقائيًا (super admin).
 */
trait HasPermissions
{
    public function additionalRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /** كل الأدوار الفعلية للمستخدم: الدور الأساسي (لو ليه صف في جدول roles) + الأدوار الإضافية. */
    public function allRoles()
    {
        $primary = $this->role
            ? Role::query()->where('slug', $this->role)->get()
            : collect();

        return $primary->merge($this->additionalRoles)->unique('id');
    }

    public function hasRole(string ...$slugs): bool
    {
        if (in_array($this->role, $slugs, true)) {
            return true;
        }

        return $this->additionalRoles()->whereIn('slug', $slugs)->exists();
    }

    public function hasPermission(string $slug): bool
    {
        // admin دايمًا super admin — نفس السلوك الحالي في EnsureUserHasRole
        // اللي بيدي admin صلاحية الوصول لأي route محمي بأي دور.
        if ($this->role === 'admin') {
            return true;
        }

        $permissions = Cache::remember(
            "user:{$this->id}:permissions",
            now()->addMinutes(10),
            fn () => $this->allRoles()->pluck('id')->isEmpty()
                ? collect()
                : \App\Models\Permission::query()
                    ->whereHas('roles', fn ($q) => $q->whereIn('roles.id', $this->allRoles()->pluck('id')))
                    ->pluck('slug')
        );

        return $permissions->contains($slug);
    }

    public function forgetPermissionsCache(): void
    {
        Cache::forget("user:{$this->id}:permissions");
    }
}
