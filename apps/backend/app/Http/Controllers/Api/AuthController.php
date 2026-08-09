<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use App\Models\SaasPlan;
use App\Models\TenantModule;
use App\Models\TenantSubscription;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/register
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|in:admin,manager,employee,accountant',
        ]);

        [$user, $tenant] = DB::transaction(function () use ($validated) {
            $tenantName = $validated['name'] . ' Workspace';
            $tenant = Tenant::create([
                'name' => $tenantName,
                'slug' => Str::slug($tenantName) . '-' . Str::lower(Str::random(6)),
                'status' => 'trial',
                'plan_key' => 'starter',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'] ?? 'admin',
                'tenant_id' => $tenant->id,
            ]);

            $plan = SaasPlan::where('key', 'starter')->first();
            if ($plan) {
                TenantSubscription::create([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'status' => 'trialing',
                    'billing_cycle' => 'monthly',
                    'starts_at' => now()->toDateString(),
                    'trial_ends_at' => now()->addDays(14)->toDateString(),
                ]);
                foreach ((array) $plan->included_modules as $module) {
                    TenantModule::create(['tenant_id' => $tenant->id, 'module' => $module, 'is_enabled' => true]);
                }
            }
            return [$user, $tenant];
        });

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'tenant' => $tenant,
            'token' => $token,
        ], 201);
    }

    /**
     * POST /api/v1/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['الحساب غير مفعّل.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
