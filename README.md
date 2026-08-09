# ERP System

Vexi Ultimate متعدد القطاعات (مقاولات / عقارات / مشاريع) — Laravel API + React Admin Dashboard.

## الستاك

| الطبقة | التقنية | الإصدار |
|---|---|---|
| Backend | Laravel + Sanctum | 13.x (PHP 8.3+) |
| Frontend | React + Vite + TypeScript + Tailwind | 19.2 / 8 / 7 / 4 |
| Database | MySQL 8 | — |

## التشغيل السريع

```bash
# 1) الباك إند
cd apps/backend
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate && php artisan db:seed
php artisan serve                 # http://localhost:8000

# 2) الفرونت إند (في terminal تاني)
cd apps/frontend
npm install
npm run dev                       # http://localhost:5173
```

بيانات الدخول بعد الـ seed: `admin@erp.local` / `password123` — **غيّرها قبل أي نشر.**

## الوثائق

- [`apps/backend/README.md`](apps/backend/README.md) — كل الـ endpoints بالتفصيل
- [`apps/frontend/README.md`](apps/frontend/README.md) — الشاشات والبنية
- [`docs/api/financial-cycle.md`](docs/api/financial-cycle.md) — الدورة المالية والقيود المتولّدة
- [`CHANGELOG.md`](CHANGELOG.md) — سجل التغييرات

## النشر

| الاستضافة | الدليل | مناسبة لإيه |
|---|---|---|
| Render + Supabase | [`docs/deployment/DEPLOY.md`](docs/deployment/DEPLOY.md) | الاستخدام الفعلي — PostgreSQL 500 ميجا، من غير قيود على الاتصالات |
| InfinityFree | [`docs/deployment/INFINITYFREE.md`](docs/deployment/INFINITYFREE.md) | التجربة والعرض — MySQL 5.6 بحد 50 ميجا، وفيه تحقق JS بيأثر على الـ API |

الاتنين مجانيين. لو النظام هيشتغل على بيانات حقيقية، Render أنسب — التفاصيل
في قسم القيود جوه دليل InfinityFree.

## الموديولات الشغالة

محاسبة (شجرة حسابات، قيود، دفتر أستاذ) · مشاريع · عقود · عقارات · مشتريات (موردين، أوامر شراء، فواتير موردين) · أصول ثابتة · موارد بشرية · رواتب · توظيف · مخزون · مبيعات · فوترة إلكترونية (ZATCA) · تصنيع · CRM · دعم فني · بنوك · موازنات · جودة · مستندات · سندات قبض وصرف · تقارير مالية · لوحة مؤشرات · **إدارة مستخدمين** · فندقة (أنواع غرف، غرف، نزلاء) · أسطول ونقل (مركبات، سائقين، رحلات، صيانة، وقود)

كل الموديولات دي ليها شاشات في الواجهة — ٣٧ شاشة بالإجمالي.

> الفندقة في الـ backend أشمل من كده بكتير (حجوزات، تسجيل وصول/مغادرة، تدبير منزلي،
> نقاط بيع، قنوات حجز خارجية — شوف `apps/backend/app/Http/Controllers/Api/Hotel`)،
> لكن شاشة الحجوزات محتاجة واجهة مخصصة (اختيار أكتر من غرفة + تحقق إتاحة بالتاريخ)
> زي `InvoiceForm.tsx` مش شاشة CRUD عامة — لسه مبنيّة.

## المبدأ المعماري

> دفتر الأستاذ هو المصدر الوحيد للحقيقة المالية.

أي عملية ليها أثر مالي بتولّد قيد مُرحّل. كل التقارير واللوحات بتُحسب من القيود دي مباشرة — مفيش جدول موازي ممكن يتعارض معاها.
