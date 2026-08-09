# تجهيز ورفع Vexi Ultimate على Render

## البنية

- `vexi-ultimate-api`: Laravel API داخل Docker.
- `vexi-ultimate-web`: React/Vite كـ Static Site.
- قاعدة البيانات: Supabase PostgreSQL للتجربة المجانية.

## 1. GitHub

```bash
git init
git add .
git commit -m "prepare Vexi Ultimate for Render"
git branch -M main
git remote add origin https://github.com/USERNAME/wafi-erp.git
git push -u origin main
```

لا ترفع `.env` أو كلمات المرور أو مفاتيح SMTP.

## 2. Supabase

أنشئ مشروعاً، ثم انسخ Host وPort 5432 وDatabase postgres وUser وPassword من Database Settings.

## 3. Render Blueprint

1. افتح Render.
2. اختر New > Blueprint.
3. اربط مستودع GitHub والفرع `main`.
4. Render يقرأ `render.yaml` وينشئ الخدمتين.

الخطة المجانية مناسبة للتجربة، وقد تتوقف الخدمة عند عدم الاستخدام ثم تستيقظ عند أول طلب.

## 4. متغيرات API

في `vexi-ultimate-api` أضف DB_HOST وDB_DATABASE وDB_USERNAME وDB_PASSWORD، ثم:

```text
APP_URL=https://رابط-api.onrender.com
CORS_ALLOWED_ORIGINS=https://رابط-الواجهة.onrender.com
SANCTUM_STATEFUL_DOMAINS=رابط-الواجهة.onrender.com
```

اترك APP_KEY على generateValue ولا تغيّره بعد بدء الاستخدام.

## 5. متغير الواجهة

في `vexi-ultimate-web` أضف:

```text
VITE_API_BASE_URL=https://رابط-api.onrender.com
```

ثم نفّذ Manual Deploy، لأن Vite يقرأ المتغير وقت البناء.

## 6. التحقق

افتح:

```text
https://رابط-api.onrender.com/api/v1/health
```

يجب أن يرجع `status: ok`. بعدها افتح الواجهة وجرب التسجيل وتسجيل الدخول.

## مشاكل شائعة

- Network Error: راجع VITE_API_BASE_URL و CORS_ALLOWED_ORIGINS.
- 404 عند تحديث رابط React: قاعدة rewrite موجودة في render.yaml.
- فشل المايجريشن: تأكد أن DB_CONNECTION=pgsql وبيانات Supabase صحيحة.
- Queue والبريد: يحتاجان SMTP وWorker دائم قبل الاستخدام الحقيقي.

Render Free مناسب للعرض والتجربة، وليس لبيانات محاسبية إنتاجية أو SLA.
