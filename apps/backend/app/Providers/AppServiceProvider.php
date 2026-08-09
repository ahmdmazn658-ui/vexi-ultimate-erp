<?php

namespace App\Providers;

use App\Models\JournalEntry;
use App\Observers\JournalEntryObserver;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // حماية الفترات المحاسبية المقفلة — مركزية على كل مسارات الترحيل
        JournalEntry::observe(JournalEntryObserver::class);

        $this->configureLegacyDatabase();
        $this->configureHttps();
    }

    /**
     * توافق مع MySQL 5.6 (الاستضافات المشتركة زي InfinityFree لسه عليها 5.6).
     *
     * InnoDB في 5.6 بحد أقصى 767 بايت للمفتاح. عمود utf8mb4 بطول 255 بياخد
     * 1020 بايت، يعني أي `unique` على عمود نصّي (زي users.email) بيفشل بـ
     * "Specified key was too long". تقصير الطول الافتراضي لـ 191 بيحل ده
     * (191 × 4 = 764 بايت).
     *
     * متحكوم بمتغير بيئة عشان النشر على MySQL 8 / PostgreSQL يفضل 255 زي ما هو.
     */
    private function configureLegacyDatabase(): void
    {
        $length = env('DB_STRING_LENGTH');

        if ($length !== null && $length !== '') {
            Builder::defaultStringLength((int) $length);
        }
    }

    /**
     * إجبار الروابط على https لما الاستضافة بتنهي SSL عند البروكسي.
     *
     * من غير ده، `url()` بيولّد روابط http (زي رابط تحميل المستند) والمتصفح
     * بيرفضها كـ mixed content على صفحة https.
     */
    private function configureHttps(): void
    {
        if (env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }
    }
}
