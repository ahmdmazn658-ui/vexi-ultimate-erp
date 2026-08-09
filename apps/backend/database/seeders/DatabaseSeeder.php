<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
use App\Models\TenantModule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ChartOfAccountsSeeder::class,
            PermissionSeeder::class,
            SaasPlanSeeder::class,
        ]);

        // مستخدم أدمن افتراضي للتجربة المحلية فقط (غيّر كلمة المرور فوراً في الإنتاج)
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'local-demo'],
            ['name' => 'Local Demo Workspace', 'status' => 'trial', 'plan_key' => 'starter']
        );
        $user = User::firstOrCreate(
            ['email' => 'admin@erp.local'],
            ['name' => 'Admin', 'password' => Hash::make('password123'), 'role' => 'admin', 'is_active' => true, 'tenant_id' => $tenant->id]
        );
        if (!$user->tenant_id) { $user->update(['tenant_id' => $tenant->id]); }
        foreach (['accounting','sales','purchase','inventory','hr'] as $module) {
            TenantModule::firstOrCreate(['tenant_id' => $tenant->id, 'module' => $module], ['is_enabled' => true]);
        }
    }
}
