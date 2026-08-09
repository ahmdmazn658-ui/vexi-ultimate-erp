# نشر المشروع مجانًا — Supabase (قاعدة بيانات) + Render (استضافة)

## ليه الاختيار ده؟

- **قاعدة البيانات: Supabase (PostgreSQL)** — مجاني بشكل دائم (مش تجربة بتنتهي)، 500MB
  تخزين، وبيدعم حتى مستقبلًا Auth/Storage لو احتجتهم. المشروع أصلًا فيه إعداد
  `pgsql` جاهز في `config/database.php`.
- **الاستضافة: Render** — خطة مجانية بدون بطاقة ائتمان، بتشغّل الباك إند
  (Docker) والفرونت إند (Static Site) مجانًا. أي نفس المنصة كل حاجة عليها.

> ملاحظة: خطة Render المجانية بتنام بعد 15 دقيقة بدون طلبات، وأول طلب بعد كده
> بياخد حوالي دقيقة لحد ما يصحى — طبيعي في الخطط المجانية.

---

## الخطوة 1 — إنشاء قاعدة البيانات على Supabase

1. روح على supabase.com وسجّل حساب مجاني (بالـ GitHub أسهل).
2. اعمل **New Project** → اختار اسم + باسورد لقاعدة البيانات (احفظه) + المنطقة
   الأقرب لك.
3. لما المشروع يخلص إنشاء، روح **Project Settings → Database → Connection
   parameters** وانسخ: Host, Port, Database name, User, Password.
4. حطّهم في `apps/backend/.env` (أو في متغيرات البيئة على Render لاحقًا):
   ```
   DB_CONNECTION=pgsql
   DB_HOST=...
   DB_PORT=5432
   DB_DATABASE=postgres
   DB_USERNAME=...
   DB_PASSWORD=...
   ```

## الخطوة 2 — رفع المشروع على GitHub

Render بيحتاج المشروع يكون في مستودع Git. لو لسه مش عامل ريبو:

```bash
cd erp-system
git init
git add .
git commit -m "initial commit"
# اعمل ريبو فاضي على github.com ثم:
git remote add origin https://github.com/USERNAME/erp-system.git
git push -u origin main
```

## الخطوة 3 — نشر الباك إند (Laravel API) على Render

المشروع فيه بالفعل `apps/backend/Dockerfile` جاهز.

1. في Render Dashboard: **New → Web Service** → اختار الريبو.
2. Runtime: **Docker**، Dockerfile path: `apps/backend/Dockerfile`،
   Root/Context: `apps/backend`.
3. Plan: **Free**.
4. في **Environment**، ضيف نفس المتغيرات اللي في `.env.example` (خصوصًا
   `APP_KEY` — سيبه فاضي وRender هيولّده، أو شغّل `php artisan key:generate
   --show` محليًا وحطه)، بالإضافة لبيانات Supabase من الخطوة 1، و:
   - `CORS_ALLOWED_ORIGINS` = رابط الفرونت إند بعد ما تنشره (خطوة 4)
   - `SANCTUM_STATEFUL_DOMAINS` = نفس الدومين من غير https://
5. اضغط **Create Web Service**. أول Deploy هيعمل تلقائيًا: composer install ثم
   `php artisan migrate --force` (موجودة في `scripts/00-laravel-deploy.sh`).

## الخطوة 4 — نشر الفرونت إند (React) على Render

1. **New → Static Site** → نفس الريبو.
2. Root directory: `apps/frontend`.
3. Build command: `npm install && npm run build`
4. Publish directory: `dist`
5. Environment variable: `VITE_API_BASE_URL` = رابط الباك إند من الخطوة 3
   (مثلًا `https://erp-backend.onrender.com`).
6. Rewrite rule: `/*` → `/index.html` (عشان React Router يشتغل صح).

> بديل: لو عايز `render.yaml` الموجود في جذر المشروع يعمل الاتنين مرة واحدة،
> استخدم **New → Blueprint** بدل الخطوتين 3 و4 لوحدهم، وRender هيقرأ الملف
> ويجهّز الخدمتين تلقائيًا (لسه هتحتاج تدخل بيانات Supabase يدويًا لأنها
> سرية).

## الخطوة 5 — تحديث بيانات الدخول الافتراضية

بعد نجاح النشر، بيانات الدخول الافتراضية هي `admin@erp.local` / `password123`
— **غيّرها فورًا** من قاعدة البيانات أو من شاشة الإعدادات قبل ما تستخدم
النظام فعليًا.

---

## بدائل لو حبيت تجرب غير كده

| الاحتياج | البديل |
|---|---|
| قاعدة بيانات بميزة الـ branching (نسخ فرعية للتطوير) | Neon (PostgreSQL) |
| استضافة أسرع بدون sleep، بس رصيد مجاني بينفد | Railway (فيه `nixpacks.toml` جاهز في المشروع أصلاً) |
| موقع فرونت إند فقط بسرعة عالية جدًا | Cloudflare Pages / Vercel |
