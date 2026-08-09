<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * إدارة المستخدمين.
 *
 * الشاشات بتحتاج قائمة المستخدمين عشان حقول زي "مدير المشروع" و"المسؤول عن التذكرة"
 * و"المفتّش" — كانت الحقول دي موجودة في التحقق بس مفيش endpoint يجيب الأسماء.
 */
class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->when($request->query('role'), fn ($q, $r) => $q->where('role', $r))
            ->when($request->query('search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%"))
            ->select('id', 'name', 'email', 'role', 'created_at')
            ->orderBy('name')
            ->paginate($request->integer('per_page', 25));

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,manager,employee,accountant',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json($user->only('id', 'name', 'email', 'role'), 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user->only('id', 'name', 'email', 'role', 'created_at'));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'sometimes|in:admin,manager,employee,accountant',
        ]);

        if (filled($validated['password'] ?? null)) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json($user->only('id', 'name', 'email', 'role'));
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        // منع المستخدم من حذف نفسه — أسهل طريقة تخسر بيها آخر حساب admin
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'مش ممكن تحذف حسابك الحالي.'], 422);
        }

        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return response()->json(['message' => 'ده آخر حساب admin — لازم يفضل واحد على الأقل.'], 422);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json(null, 204);
    }
}
