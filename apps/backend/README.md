# ERP System — Backend API (Laravel)

نواة API حقيقية شغالة لقطاع **المقاولات / العقارات / المشاريع**.

## تشغيل محلي

```bash
cd apps/backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed   # شجرة حسابات ابتدائية + مستخدم أدمن تجريبي
php artisan serve
```

جرّب: `GET http://localhost:8000/api/v1/ping`

> **بيانات دخول تجريبية بعد الـ seed:** `admin@erp.local` / `password123` — غيّرها فوراً قبل أي نشر حقيقي.

## الموديولات الجاهزة والشغالة

### 1. Auth (core/auth) — بدون توكن، عام
| Method | Endpoint | الوصف |
|---|---|---|
| POST | `/api/v1/auth/register` | تسجيل مستخدم جديد (يرجع token) |
| POST | `/api/v1/auth/login` | تسجيل دخول (يرجع token) |
| POST | `/api/v1/auth/logout` | تسجيل خروج (محتاج token) |
| GET | `/api/v1/auth/me` | بيانات المستخدم الحالي (محتاج token) |

> باقي كل الـ endpoints تحت محتاجة header:
> `Authorization: Bearer {token}`

### 2. Accounting — Chart of Accounts
`GET/POST /api/v1/accounting/chart-of-accounts`, `GET/PUT/DELETE .../{id}`

فلاتر: `?type=&active_only=1&flat=1`

> `flat=1` بيرجّع كل الحسابات في مستوى واحد. الوضع الافتراضي بيرجّع الحسابات
> الرئيسية بس والفرعية متداخلة جوّاها (`children`) — كويس لعرض الشجرة، بس أي
> قائمة اختيار حساب محتاجة `flat=1` وإلا هتطلع ناقصة.

### 3. Accounting — Journal Entries (قيود متوازنة تلقائياً)
| Method | Endpoint | الوصف |
|---|---|---|
| GET | `/api/v1/accounting/journal-entries` | عرض القيود |
| POST | `/api/v1/accounting/journal-entries` | إنشاء قيد (بيتحقق إن مدين = دائن) |
| GET | `/api/v1/accounting/journal-entries/{id}` | تفاصيل قيد |
| POST | `/api/v1/accounting/journal-entries/{id}/post` | ترحيل القيد |
| DELETE | `/api/v1/accounting/journal-entries/{id}` | حذف (لو مش مُرحّل) |

### 4. Projects (المشاريع)
`GET/POST /api/v1/projects`, `GET/PUT/DELETE .../{id}`
فلاتر: `?status=in_progress&type=construction&search=...`

### 5. Contracts (العقود) — مرتبطة بالمشاريع
`GET/POST /api/v1/contracts`, `GET/PUT/DELETE .../{id}`
فلاتر: `?project_id=1&status=active`

### 6. Real Estate — Properties (العقارات)
`GET/POST /api/v1/real-estate/properties`, `GET/PUT/DELETE .../{id}`
فلاتر: `?status=available&property_type=residential&min_price=...&max_price=...`

### 7. Procurement — Suppliers & Purchase Orders
`GET/POST /api/v1/procurement/suppliers`, `GET/PUT/DELETE .../{id}`
`GET/POST /api/v1/procurement/purchase-orders` (بيحسب `total_amount` تلقائي من الـ items)

### 8. Fixed Assets (المعدات والأصول)
| Method | Endpoint | الوصف |
|---|---|---|
| GET/POST | `/api/v1/fixed-assets/assets` | عرض/إضافة أصل |
| GET | `.../assets/{id}` | تفاصيل + `book_value` + `annual_depreciation` محسوبين |
| POST | `.../assets/{id}/run-depreciation` | يضيف قسط إهلاك سنة على المجمّع |

### 9. HR — Employees
`GET/POST /api/v1/hr/employees`, `GET/PUT/DELETE .../{id}`
فلاتر: `?department=engineering&project_id=1&status=active`

### 10. Inventory — Warehouses, Products & Stock Movements
| Method | Endpoint | الوصف |
|---|---|---|
| GET/POST | `/api/v1/inventory/warehouses` | مستودعات |
| GET/PUT/DELETE | `.../warehouses/{id}` | — |
| GET/POST | `/api/v1/inventory/products` | منتجات — فلاتر: `?category=&is_active=&low_stock=1&search=` |
| GET/PUT/DELETE | `.../products/{id}` | تفاصيل المنتج (فيها `below_reorder_level` محسوب) |
| GET/POST | `/api/v1/inventory/stock-movements` | حركات دخول/خروج/تسوية — بتحدّث `quantity_on_hand` تلقائياً |

### 11. Sales — Customers & Sales Orders
| Method | Endpoint | الوصف |
|---|---|---|
| GET/POST | `/api/v1/sales/customers` | عملاء — فلاتر: `?customer_type=&is_active=&search=` |
| GET/PUT/DELETE | `.../customers/{id}` | — |
| GET/POST | `/api/v1/sales/orders` | طلب بيع (بيحسب `total_amount` تلقائي من الـ items) |
| GET/PUT/DELETE | `.../orders/{id}` | حذف مسموح فقط لو `draft` |
| POST | `.../orders/{id}/confirm` | يحوّل الطلب لـ `confirmed` ويصرف من المخزون (`stock_movements` type=out)، بيتحقق من كفاية الكمية أولاً. Body: `{ warehouse_id }` |

### 12. E-Invoicing — ZATCA (Phase 1: توليد رقم + QR)
| Method | Endpoint | الوصف |
|---|---|---|
| GET/POST | `/api/v1/e-invoicing/invoices` | إنشاء فاتورة `draft` يدوياً (بنود مباشرة) |
| POST | `.../invoices/from-sales-order/{salesOrderId}` | يولّد فاتورة `draft` من طلب بيع `confirmed`/`delivered` بنفس بنوده |
| GET/PUT/DELETE | `.../invoices/{id}` | حذف مسموح فقط لو `draft` — PUT بيقبل `status: paid\|cancelled` فقط |
| POST | `.../invoices/{id}/issue` | يقفل الفاتورة: يولّد رقم رسمي `INV-YYYY-000001` + QR (ZATCA TLV/Base64) + `zatca_uuid`، ويحوّل الحالة لـ `issued` — **وينشئ تلقائياً قيد محاسبي مُرحّل (posted)**: مدين ذمم مدينة (1130) = الإجمالي، دائن إيرادات مبيعات (4100) = قبل الضريبة، دائن ضريبة قيمة مضافة مستحقة (2160) = الضريبة. القيد بيتربط بالفاتورة عبر `journal_entry_id` وبيظهر جوه `GET /invoices/{id}` |

> **ملاحظة ZATCA:** الـ QR الحالي Phase 1 فقط (البيانات الخمسة الأساسية بدون توقيع رقمي). دمج Phase 2 (شهادة CSID + توقيع XML/UBL مع بوابة فاتورة) هيتضاف لاحقاً في `integrations/zatca`. اسم المنشأة والرقم الضريبي بيتقرّوا من `config/company.php` (متغيرات `COMPANY_NAME`, `COMPANY_VAT_NUMBER` في `.env`).

> **ملاحظة محاسبية:** حسابات الذمم/الإيرادات/الضريبة الافتراضية بتتنشئ تلقائياً (`firstOrCreate`) في شجرة الحسابات أول مرة تتستخدم، بالأكواد `1130`/`4100`/`2160`. لو عندك شجرة حسابات مختلفة، عدّل الأكواد في `app/Support/Accounting/DefaultAccounts.php`.

### 13. Manufacturing — Bill of Materials & Production Orders
| Method | Endpoint | الوصف |
|---|---|---|
| GET/POST | `/api/v1/manufacturing/bom` | قائمة مكونات (BOM) — `{ product_id, name, items: [{component_product_id, quantity}] }`. الكمية = المطلوب لإنتاج وحدة واحدة |
| GET/PUT/DELETE | `.../bom/{id}` | — |
| GET/POST | `/api/v1/manufacturing/production-orders` | أمر إنتاج — لو `bill_of_material_id` متبعتش، بيدور تلقائي على أحدث BOM نشط للمنتج |
| GET/PUT/DELETE | `.../production-orders/{id}` | حذف/إلغاء مسموح فقط لو `draft` |
| POST | `.../production-orders/{id}/start` | يستهلك الخامات من الـ BOM (`quantity × quantity_planned`) لكل خامة، بيتحقق من كفاية المخزون أولاً، وينشئ `stock_movements` type=out. الحالة تبقى `in_progress` |
| POST | `.../production-orders/{id}/complete` | يضيف الكمية المنتَجة لمخزون المنتج النهائي (`stock_movements` type=in)، الحالة تبقى `completed`. Body اختياري: `{ quantity_produced }` (افتراضي = `quantity_planned`) |

### 14. CRM — Leads & Opportunities (Pipeline)
| Method | Endpoint | الوصف |
|---|---|---|
| GET/POST | `/api/v1/crm/leads` | عملاء محتملين — فلاتر: `?status=&assigned_to=&search=` |
| GET/PUT/DELETE | `.../leads/{id}` | — |
| POST | `.../leads/{id}/convert` | يحوّل الـ lead لعميل حقيقي في `customers` (وحدة المبيعات) ويقفله `converted` |
| GET/POST | `/api/v1/crm/opportunities` | فرص بيعية — فلاتر: `?stage=&customer_id=&owner_id=` |
| GET/PUT/DELETE | `.../opportunities/{id}` | — |
| POST | `.../opportunities/{id}/move-stage` | ينقل الفرصة بين مراحل الـ pipeline: `prospecting → qualification → proposal → negotiation → won/lost` |

### 15. Payroll — دورات رواتب مرتبطة بـ HR
| Method | Endpoint | الوصف |
|---|---|---|
| GET/POST | `/api/v1/payroll/runs` | إنشاء دورة رواتب لفترة (`period` فريدة) — بتولّد `payslips` تلقائيًا لكل موظف `active` من `basic_salary` |
| GET | `.../runs/{id}` | تفاصيل الدورة + القسائم + القيد المحاسبي لو اترحّلت |
| DELETE | `.../runs/{id}` | مسموح فقط لو `draft` |
| POST | `.../runs/{id}/post` | 🔒 `admin`/`accountant` فقط — يرحّل الدورة وينشئ قيد محاسبي: مدين مصروفات رواتب (5200) = دائن رواتب مستحقة الدفع (2200) |

### 16. Helpdesk — تذاكر دعم فني
| Method | Endpoint | الوصف |
|---|---|---|
| GET/POST | `/api/v1/helpdesk/tickets` | فلاتر: `?status=&priority=&assigned_to=&customer_id=` |
| GET/PUT/DELETE | `.../tickets/{id}` | — |
| POST | `.../tickets/{id}/resolve` | يقفل التذكرة `resolved` ويسجل `resolved_at` |
| POST | `.../tickets/{id}/close` | يقفل التذكرة نهائيًا `closed` |

### 17. Banking — حسابات بنكية وتسوية
| Method | Endpoint | الوصف |
|---|---|---|
| GET/POST | `/api/v1/banking/accounts` | حسابات بنكية — كل حساب فيه `current_balance` محسوب (افتتاحي + إيداعات - سحوبات) |
| GET/PUT/DELETE | `.../accounts/{id}` | — |
| GET/POST | `/api/v1/banking/transactions` | حركات إيداع/سحب — فلاتر: `?bank_account_id=&type=&is_reconciled=&from=&to=` |
| GET/DELETE | `.../transactions/{id}` | حذف مسموح فقط لو مش متسوّاة |
| POST | `.../transactions/{id}/reconcile` | يعلّم الحركة كمسوّاة بنكيًا (matched مع كشف الحساب) |

### 18. Budgeting — ميزانيات مقارنة تلقائيًا بالفعلي
| Method | Endpoint | الوصف |
|---|---|---|
| GET/POST | `/api/v1/budgeting/budgets` | ميزانية لحساب معيّن خلال فترة (`period_start` → `period_end`)، فلاتر: `?period=&project_id=&account_id=` |
| GET/PUT/DELETE | `.../budgets/{id}` | كل استجابة فيها `actual_amount` و`variance_amount` محسوبين مباشرة من `journal_entry_lines` المُرحّلة (`posted`) لنفس الحساب والفترة |

### 19. Quality — تفتيشات جودة على المشاريع
| Method | Endpoint | الوصف |
|---|---|---|
| GET/POST | `/api/v1/quality/inspections` | فلاتر: `?project_id=&result=` |
| GET/PUT/DELETE | `.../inspections/{id}` | PUT بيستخدم لتسجيل النتيجة (`passed/failed/needs_rework`) والملاحظات والإجراء التصحيحي |

### 20. Recruitment — وظائف شاغرة ومتقدمين
| Method | Endpoint | الوصف |
|---|---|---|
| GET/POST | `/api/v1/recruitment/job-openings` | فلاتر: `?status=&department=` |
| GET/PUT/DELETE | `.../job-openings/{id}` | يشمل عدد المتقدمين وقائمتهم |
| GET/POST | `/api/v1/recruitment/candidates` | فلاتر: `?job_opening_id=&stage=` |
| GET/PUT/DELETE | `.../candidates/{id}` | PUT بيحدّث `stage`: applied → screening → interview → offer → hired/rejected |
| POST | `.../candidates/{id}/hire` | يحوّل المتقدم لموظف حقيقي في `hr/employees` (Body: `employee_code?, position, department, hire_date, basic_salary`) |

### 21. Document Management — مستندات مرتبطة بأي كيان
| Method | Endpoint | الوصف |
|---|---|---|
| GET/POST | `/api/v1/document-management/documents` | فلاتر: `?category=&documentable_type=&documentable_id=&search=` |
| GET | `.../documents/{id}/download` | بيرجّع الملف نفسه (محمي بالتوكن) |
| GET/PUT/DELETE | `.../documents/{id}` | الحذف بيمسح الملف من القرص كمان |

> علاقة polymorphic (`documentable_type` + `documentable_id`) — مربوطة حاليًا بـ `Project`, `Contract`, `Employee`, `Customer` (كل موديل فيه `documents()`). لضم موديل جديد، ضيف بس `morphMany(Document::class, 'documentable')` عليه.

**الرفع.** `POST` بيقبل `multipart` مع الحقل `file` (8 ميجا كحد أقصى، قايمة سماح
للامتدادات)، أو `file_path` نصّي لملف مرفوع بطريقة تانية. التخزين على قرص `local`
(`storage/app/private/documents`) باسم عشوائي، والاسم الأصلي بيتحفظ في
`original_name` عشان التحميل يرجّعه مفهوم.

> الملفات **مش** في `public` والتقديم مش عبر `storage:link`. السبب إن `storage:link`
> بيعمل symlink، والـ symlink معطّل على أغلب الاستضافات المشتركة — فراوت التحميل
> بيشتغل في كل مكان، وكمان بيتحقق من الصلاحية قبل ما يدّي الملف.


### 22. Procurement — Supplier Bills (فواتير الموردين / الذمم الدائنة)
| Method | Endpoint | الوصف |
|---|---|---|
| GET/POST | `/api/v1/procurement/supplier-bills` | فلاتر: `?supplier_id=&project_id=&status=&overdue=1` |
| POST | `.../supplier-bills/from-purchase-order/{purchaseOrder}` | يولّد فاتورة مورد draft من أمر شراء `approved`/`received` بنفس البنود |
| GET/PUT/DELETE | `.../supplier-bills/{id}` | التعديل والحذف للـ draft بس |
| POST | `.../supplier-bills/{id}/approve` | يعتمد الفاتورة ويولّد قيد مُرحّل (محتاج دور `admin/manager/accountant`) |

قيد الاعتماد:
```
مدين: حساب المصروف/الأصل (افتراضي 5900)   = subtotal
مدين: ضريبة مدخلات (1170)                 = vat_amount
    دائن: الذمم الدائنة (2100)            = total_amount
```

### 23. Finance — Payments (سندات القبض والصرف)
| Method | Endpoint | الوصف |
|---|---|---|
| GET | `/api/v1/finance/payments` | فلاتر: `?type=receipt|payment&status=&customer_id=&supplier_id=&bank_account_id=&from=&to=` |
| POST | `/api/v1/finance/payments` | إنشاء سند + ترحيله محاسبياً + حركة بنكية + تخصيصه على فواتير |
| GET | `.../payments/{id}` | يشمل التخصيصات والقيد والحركة البنكية |
| POST | `.../payments/{id}/void` | إلغاء بقيد عكسي (محتاج دور `admin/accountant`) |

```jsonc
// POST /api/v1/finance/payments
{
  "type": "receipt",              // receipt = قبض من عميل | payment = صرف لمورد
  "customer_id": 3,               // supplier_id لو النوع payment
  "bank_account_id": 1,           // اختياري — لو فاضي بيروح على الصندوق (1110)
  "payment_date": "2026-03-01",
  "amount": 5000,
  "method": "bank_transfer",
  "allocations": [                // اختياري — من غيرها بيتسجّل كدفعة على الحساب
    { "target_id": 12, "amount": 5000 }   // رقم فاتورة المبيعات (أو فاتورة المورد)
  ]
}
```

القيود المتولّدة:
```
قبض:  مدين: البنك/الصندوق        دائن: الذمم المدينة (1130)
صرف:  مدين: الذمم الدائنة (2100)  دائن: البنك/الصندوق
```

التحقق قبل الترحيل: إجمالي التخصيصات ≤ قيمة السند، كل فاتورة تخص نفس الطرف، حالتها تسمح بالسداد (`issued` / `approved`)، والمبلغ ≤ المتبقي عليها. بعد الترحيل بيتحدّث `paid_amount` على الفواتير وتتقفل تلقائياً لما تتسدد بالكامل.

> `Invoice` و`SupplierBill` بقى فيهم `balance_due` و`payment_status` (`unpaid` / `partial` / `paid`) محسوبين تلقائياً في كل استجابة.

### 24. Reports — القوائم المالية (reports/financial)
| Method | Endpoint | الوصف |
|---|---|---|
| GET | `/api/v1/reports/trial-balance` | ميزان المراجعة — `?from=&to=&project_id=` مع تحقق `is_balanced` |
| GET | `/api/v1/reports/income-statement` | قائمة الدخل — الافتراضي من بداية السنة لليوم، مع `margin_percent` |
| GET | `/api/v1/reports/balance-sheet` | المركز المالي — `?as_of=` مع تحقق معادلة الميزانية |
| GET | `/api/v1/reports/general-ledger/{account}` | دفتر أستاذ حساب: رصيد افتتاحي + حركات + رصيد جاري تراكمي |
| GET | `/api/v1/reports/ar-aging` | أعمار الذمم المدينة بشرائح: current / 1-30 / 31-60 / 61-90 / +90 |
| GET | `/api/v1/reports/ap-aging` | أعمار الذمم الدائنة بنفس الشرائح |

كل الأرقام محسوبة من `journal_entry_lines` للقيود المُرحّلة (`posted`) فقط — مفيش أي مصدر بيانات موازي، فالتقارير متسقة دايماً مع دفتر الأستاذ. المحرّك كله في `App\Support\Reports\FinancialReports`، وفلتر `project_id` بيدي قوائم مالية لكل مشروع على حدة.

### 25. Dashboards — مؤشرات تنفيذية
`GET /api/v1/dashboards/summary` — بيرجع في استجابة واحدة:
- **مالي:** إيراد/مصروف/صافي ربح الشهر والسنة، ورصيد النقدية من كل الحسابات البنكية
- **ذمم:** المستحق والمتأخر على العملاء وعدد الفواتير المتأخرة، والمستحق للموردين
- **اتجاه المبيعات:** آخر 12 شهر (قيمة وعدد فواتير)
- **أعلى 5 عملاء** بالإيراد من بداية السنة
- **تشغيلي:** مشاريع نشطة، أوامر شراء مفتوحة وقيمتها، فواتير draft، تذاكر مفتوحة، موظفين نشطين

المؤشرات المالية بتستدعي نفس `FinancialReports`، فالرقم في اللوحة = الرقم في القوائم المالية.

### 26. Accounting — Period Closing (الفترات المحاسبية)
| Method | Endpoint | الوصف |
|---|---|---|
| GET | `/api/v1/accounting/periods` | فلاتر: `?fiscal_year=&status=` |
| GET | `.../periods/status?date=` | فحص سريع: هل التاريخ ده في فترة مقفلة؟ |
| POST | `.../periods/generate` | ينشئ 12 فترة شهرية لسنة مالية (`{fiscal_year}`) — دور `admin/accountant` |
| POST | `.../periods/{id}/close` | إقفال فترة — بيرفض لو لسه فيها قيود مسودة |
| POST | `.../periods/{id}/reopen` | إعادة فتح — دور `admin` بس |
| POST | `.../periods/year-end-closing` | قيد الإقفال السنوي (`{fiscal_year}`) |

**الحماية مركزية.** `App\Observers\JournalEntryObserver` مسجّل على `JournalEntry` في
`AppServiceProvider`، فبيمنع إنشاء أو تعديل أو حذف أي قيد **مُرحّل** تاريخه في فترة مقفلة.
ده بيغطي كل مسارات الترحيل دفعة واحدة — إصدار الفواتير، اعتماد فواتير الموردين، سندات القبض
والصرف وإلغاؤها، ترحيل الرواتب، قيود الإهلاك، والقيود اليدوية — وأي مسار جديد بيتضاف مستقبلاً
بيبقى محمي تلقائياً من غير كود إضافي. القيود **المسودة** مسموحة في أي وقت، الممنوع هو الترحيل.

> التواريخ اللي مش داخل أي فترة معرّفة بتعتبر مفتوحة، عشان النظام يفضل شغال عادي لو لسه ما عرّفتش فترات.

**قيد الإقفال السنوي** بيصفّر حسابات الإيرادات والمصروفات وينقل صافي النتيجة للأرباح المحتجزة (3200):
```
مدين: كل حساب إيراد برصيده
    دائن: كل حساب مصروف برصيده
    دائن/مدين: الأرباح المحتجزة (3200) = صافي الربح أو الخسارة
```
بيتنفّذ داخل `AccountingPeriod::withoutGuard()` لأن تاريخه ٣١ ديسمبر — واللي غالباً بيبقى في
فترة اتقفلت بالفعل. بيرفض التكرار لو قيد الإقفال للسنة دي موجود.

### 27. Core — Users (إدارة المستخدمين)
| Method | Endpoint | الوصف |
|---|---|---|
| GET | `/api/v1/core/users` | فلاتر: `?role=&search=` — متاح لأي مستخدم مسجّل |
| GET | `.../users/{id}` | — |
| POST/PUT/DELETE | `.../users`, `.../users/{id}` | دور `admin` بس |

الحقول اللي بتشاور على مستخدم — `project_manager_id`, `assigned_to`, `owner_id`,
`inspector_id` — محتاجة القايمة دي عشان الواجهة تعرف تعرض الأسماء.

الحذف محمي بشرطين: مش ممكن تحذف حسابك الحالي، ومش ممكن تحذف آخر حساب `admin`
(الاتنين بيقفلوا النظام على نفسك).


## دورة العمل المالية الكاملة (end-to-end)

```
مبيعات:   عميل → طلب بيع → confirm → فاتورة → issue (قيد + QR) → سند قبض → تحصيل
مشتريات:  مورد → أمر شراء → approve → فاتورة مورد → approve (قيد) → سند صرف → سداد
                                    ↓
                        دفتر الأستاذ (قيود مُرحّلة)
                                    ↓
              ميزان المراجعة / قائمة الدخل / المركز المالي / أعمار الديون / اللوحة
```

## core/permissions — تحقق من الأدوار

middleware اسمه `role` (مسجل في `bootstrap/app.php`) بيتحقق من عمود `role` بتاع المستخدم (`admin`, `manager`, `employee`, `accountant`). استخدامه في أي راوت:
```php
Route::post('...')->middleware('role:admin,accountant');
```
حاليًا مطبّق على `POST /payroll/runs/{id}/post`، `POST /procurement/supplier-bills/{id}/approve`، و`POST /finance/payments/{id}/void`. ينفع تتوسع فيه على أي إجراء حساس تاني (ترحيل قيود، حذف فواتير...).

## الاختبارات

```bash
cd apps/backend
php artisan test
```

الاختبارات بتشتغل على SQLite في الذاكرة (`phpunit.xml`) فمش محتاجة قاعدة بيانات:

| الملف | بيغطي |
|---|---|
| `tests/Feature/AccountingPeriodGuardTest.php` | حماية الفترات المقفلة: منع الترحيل، السماح بالمسودات، حدود الفترة، `withoutGuard`، توليد الفترات |
| `tests/Feature/FinancialCycleTest.php` | الدورة كاملة: إصدار فاتورة ← تحصيل جزئي ← تحصيل كامل ← إلغاء سند، وتوازن ميزان المراجعة والمركز المالي بعد كل خطوة |

فيه كمان فحوصات مستقلة بتشتغل من غير composer في `scripts/verify/` — شوف الـ README هناك.

## مثال استخدام كامل

```bash
# 1) تسجيل
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Ahmed","email":"ahmed@test.com","password":"password123","password_confirmation":"password123"}'
# → يرجع token

# 2) إنشاء مشروع (استخدم الـ token من فوق)
curl -X POST http://localhost:8000/api/v1/projects \
  -H "Authorization: Bearer TOKEN_HERE" -H "Content-Type: application/json" \
  -d '{"project_code":"PRJ-001","name":"برج الرياض","type":"construction","budget":5000000}'
```

## CORS ونشر الواجهة على دومين منفصل

في التطوير الواجهة بتنادي الـ API عبر proxy في `vite.config.ts`، فالطلبات same-origin ومفيش CORS.
في الإنتاج لما الواجهة تبقى على دومين تاني، حدد الدومين في `.env`:

```
CORS_ALLOWED_ORIGINS=https://erp.example.com,https://admin.example.com
```

المصادقة بالتوكن (Bearer) مش بالكوكيز، فـ `supports_credentials` مقفولة في `config/cors.php`.
لو حوّلت لمصادقة SPA بالكوكيز، خليها `true` وحدد `SANCTUM_STATEFUL_DOMAINS`.

## النشر على Railway

1. اعمل push للمشروع على GitHub (شوف التعليمات في README الرئيسي).
2. من Railway: **New Project → Deploy from GitHub repo** واختر الريبو.
3. Railway هيكتشف `nixpacks.toml` تلقائي وينفذ:
   - `composer install --no-dev --optimize-autoloader`
   - `php artisan migrate --force`
   - تشغيل السيرفر على `$PORT`
4. أضف **MySQL plugin** من Railway — هيولد متغيرات `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` تلقائياً، اربطهم بمتغيرات البيئة في إعدادات الـ service.
5. أضف متغير `APP_KEY` — ولّده محلياً بـ `php artisan key:generate --show` وحطه في Railway variables.

## النشر على Render

1. **New → Web Service** من نفس الـ repo.
2. Build Command: `composer install --no-dev --optimize-autoloader`
3. Start Command: `php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT`
4. أضف PostgreSQL/MySQL instance من Render واربط متغيرات `DB_*`.
5. حط `FORCE_HTTPS=true` — Render بينهي SSL عند البروكسي، ومن غيرها الروابط
   المولّدة (زي رابط تحميل المستند) بتطلع `http` والمتصفح بيرفضها.

الدليل الكامل: [`docs/deployment/DEPLOY.md`](../../docs/deployment/DEPLOY.md).

## النشر على استضافة مشتركة (InfinityFree وغيرها)

الدليل الكامل: [`docs/deployment/INFINITYFREE.md`](../../docs/deployment/INFINITYFREE.md).

نقطتين مهمتين لو هتنشر على استضافة مشتركة:

- **`DB_STRING_LENGTH=191`** لو القاعدة MySQL 5.6. حد المفتاح في InnoDB هناك 767
  بايت، وعمود `utf8mb4` بطول 255 بياخد 1020 بايت — يعني المايجريشن هتقف على
  `users.email`.
- **الواجهة والـ API على دومين واحد.** لما ينسخ ناتج `npm run build` جوه `public/`،
  الراوت اللي في `routes/web.php` بيرجّع `index.html` لأي مسار مش API، فالروابط
  المباشرة بتشتغل. لو الواجهة منشورة لوحدها، الملف مش هيكون موجود والراوت
  بيرجّع رد الـ API العادي.

## الخطوة الجاية

كل موديول جديد هياخد نفس الشكل: Migration + Model + Controller + Routes، ويتضاف في `routes/api.php`.

الأولويات المقترحة دلوقتي:
1. **إشعارات دائن/مدين** (credit & debit notes) على فواتير المبيعات والمشتريات — المرتجعات حالياً محتاجة قيد يدوي.
2. **تصدير التقارير** PDF/Excel من `reports/pdf` و`reports/excel`.
3. **العملات المتعددة** (`core/currencies`) — كل الأرقام دلوقتي بعملة واحدة.
4. **تكامل ZATCA Phase 2** الفعلي (توقيع + رفع للبوابة) في `integrations/zatca`.
5. **تشغيل اختبارات PHPUnit فعليًا** — الاختبارات مكتوبة في `tests/` (15 اختبار
   للدورة المالية وحماية الفترات) بس ما اتشغّلتش لأن `composer install` مكانش
   ممكن في بيئة التطوير المستخدمة.
