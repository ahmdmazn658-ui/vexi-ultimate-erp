<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;use App\Models\SaasPlan;
class SaasPlanSeeder extends Seeder {public function run():void {foreach([['key'=>'starter','name'=>'Starter','name_ar'=>'الأساسية','monthly_price'=>299,'annual_price'=>2990,'max_users'=>5,'max_storage_gb'=>10,'included_modules'=>['accounting','sales','purchase','inventory','hr']],['key'=>'growth','name'=>'Growth','name_ar'=>'النمو','monthly_price'=>999,'annual_price'=>9990,'max_users'=>25,'max_storage_gb'=>100,'included_modules'=>['*']],['key'=>'enterprise','name'=>'Enterprise','name_ar'=>'المؤسسات','monthly_price'=>2999,'annual_price'=>29990,'max_users'=>null,'max_storage_gb'=>1000,'included_modules'=>['*']]] as $p)SaasPlan::updateOrCreate(['key'=>$p['key']],$p);}}
