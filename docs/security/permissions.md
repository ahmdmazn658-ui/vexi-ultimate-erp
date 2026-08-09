# core/permissions — الأدوار والصلاحيات

نظام صلاحيات دقيق (granular permissions) فوق نظام الأدوار البسيط اللي كان
موجود (عمود `users.role` بـ 4 قيم ثابتة). **العمود القديم لسه شغال بالكامل**
— كل الـ routes الحالية اللي بتستخدم `middleware('role:admin,accountant')`
فضلت زي ما هي من غير أي تغيير، فمفيش أي كسر (breaking change).

## البنية

| الجدول | الوصف |
|---|---|
| `permissions` | صلاحية دقيقة، بصيغة slug زي `accounting.journal-entries.post` |
| `roles` | دور (الأربعة الأساسية + أي دور مخصص جديد) |
| `permission_role` | ربط دور بمجموعة صلاحيات |
| `role_user` | أدوار **إضافية** للمستخدم فوق دوره الأساسي في `users.role` |

## الاستخدام

**فحص دور كامل (زي القديم، لسه متاح):**
```php
Route::post('...', [Controller::class, 'action'])->middleware('role:admin,accountant');
```

**فحص صلاحية دقيقة (الطريقة الجديدة المفضّلة للـ routes الجديدة):**
```php
Route::post('...', [Controller::class, 'action'])->middleware('permission:accounting.journal-entries.post');
```

في الكود:
```php
if ($user->hasPermission('hotel.channels.manage')) { ... }
if ($user->hasRole('admin', 'manager')) { ... }
```

المستخدم اللي `role = admin` عنده كل الصلاحيات تلقائيًا (super admin) — نفس
سلوك `EnsureUserHasRole` القديم بالظبط.

## إدارة الأدوار (API)

- `GET /api/v1/core/permissions` — كل الصلاحيات مجمّعة حسب الموديول.
- `GET/POST/PUT/DELETE /api/v1/core/roles` — CRUD للأدوار (الأدوار الأساسية
  الأربعة `is_system=true` ومينفعش تتمسح).
- `POST /api/v1/core/users/{user}/roles` — إسناد أدوار إضافية لمستخدم.

كل الـ endpoints دي محمية بـ `permission:core.roles.manage` — يعني بره الأدوار
الأربعة الأساسية، أي دور يحصل على الصلاحية دي هيقدر يدير الأدوار والصلاحيات.

## التشغيل

```bash
php artisan migrate
php artisan db:seed --class=PermissionSeeder   # مضاف تلقائيًا في DatabaseSeeder كمان
```

الـ seeder بيوزّع الصلاحيات على الأدوار الأربعة بنفس التوزيع اللي كان متبني
في الـ routes القديمة، فمفيش صلاحية فعلية بتتغير عند الترقية.

## الخطوة الجاية (لما يتاح وقت)

الـ routes القديمة (11 مكان) لسه بتستخدم `role:` بدل `permission:`. التحويل
التدريجي لكل route لصلاحية دقيقة بيدي تحكم أفضل (مثلاً manager يقدر يعتمد
فواتير موردين من غير ما يقدر يفتح فترة محاسبية مقفلة)، لكن مش لازم يحصل
دفعة واحدة — النظامين شغالين مع بعض من غير تعارض.
