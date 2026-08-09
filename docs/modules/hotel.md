# موديول الفندقة (Hotel)

نظام فندقي متكامل: غرف وحجوزات، housekeeping، نقاط بيع (POS)، وربط مباشر
بنظام المحاسبة عبر Folio → Invoice. مبني تحت `apps/backend` بنفس معمارية
باقي الموديولات (Laravel + Sanctum)، والمسارات كلها تحت `/api/v1/hotel/*`.

## الكيانات الأساسية

| الجدول | الوصف |
|---|---|
| `hotel_room_types` | أنواع الغرف وسعرها الافتراضي |
| `hotel_rooms` | الغرف الفعلية، بحالة تجمع الإشغال + التنظيف |
| `hotel_guests` | بيانات النزلاء |
| `hotel_channels` | قنوات الحجز (مباشر، Booking.com، Expedia...) |
| `hotel_reservations` + `hotel_reservation_rooms` | الحجز وغرفه |
| `hotel_folios` + `hotel_folio_charges` | فاتورة الإقامة المفتوحة وبنودها |
| `hotel_housekeeping_tasks` | مهام التنظيف/الصيانة لكل غرفة |
| `hotel_pos_outlets` / `hotel_pos_products` / `hotel_pos_orders` / `hotel_pos_order_items` | نقاط البيع (مطعم، بار، minibar) |

## دورة العمل

1. **حجز جديد** — `POST /hotel/reservations` (بيتحقق من عدم تعارض الغرف).
2. **تسجيل وصول** — `POST /hotel/reservations/{id}/check-in`: بيفتح
   الـ folio ويسجّل رسوم الغرفة عن كل ليالي الإقامة تلقائيًا.
3. **أثناء الإقامة** — أوردرات POS بـ `room_charge: true` بتتحوّل تلقائيًا
   لبند في الـ folio بدل دفع فوري.
4. **تسجيل مغادرة** — `POST /hotel/reservations/{id}/check-out`: بيقفل
   الـ folio، **يولّد Invoice حقيقي** في `invoices`/`invoice_items` (زي أي
   فاتورة مبيعات عادية — القيد المحاسبي بيتولّد من نفس آلية الفواتير
   الموجودة)، وبيحرر الغرف بحالة `vacant_dirty` + مهمة تنظيف تلقائية.

## Channel Manager

بنية `hotel_channels` جاهزة لربط أي مزوّد خارجي (Booking.com, Expedia...)،
لكن **المزامنة الفعلية محتاجة API credentials حقيقية من المزوّد نفسه** —
ده مش حاجة ممكن تتبني من غير حساب فعلي عند المزوّد. الـ endpoint
`POST /hotel/channels/{id}/sync` جاهز كنقطة دخول؛ التنفيذ الفعلي لكل مزوّد
(HTTP client، مزامنة الإتاحة/الأسعار، استقبال حجوزات جديدة) محتاج يتضاف
لما يتحدد المزوّد المطلوب فعليًا ويتوفر API key.

## التشغيل محليًا

```bash
cd apps/backend
php artisan migrate
php artisan db:seed --class=HotelSeeder   # بيانات تجريبية: غرف + منتجات POS
```
