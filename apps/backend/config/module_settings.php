<?php

/**
 * Module Settings Configuration
 * إعدادات جميع الموديولات - مستوحاة من Odoo مع تحسينات للسوق السعودي
 * 
 * Structure: module => group => key => [type, default, label_ar, label_en, options?]
 */

return [

    // ══════════════════════════════════════════════════════════════════════
    // 💰 ACCOUNTING - المحاسبة
    // ══════════════════════════════════════════════════════════════════════
    'accounting' => [
        'general' => [
            'fiscal_year_start' => ['type' => 'string', 'default' => '01-01', 'label_ar' => 'بداية السنة المالية', 'label_en' => 'Fiscal Year Start'],
            'default_currency' => ['type' => 'string', 'default' => 'SAR', 'label_ar' => 'العملة الافتراضية', 'label_en' => 'Default Currency'],
            'multi_currency_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل تعدد العملات', 'label_en' => 'Enable Multi-Currency'],
            'multi_company_enabled' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'تفعيل تعدد الشركات', 'label_en' => 'Enable Multi-Company'],
            'auto_post_journal_entries' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'ترحيل القيود تلقائياً', 'label_en' => 'Auto-Post Journal Entries'],
            'lock_date' => ['type' => 'string', 'default' => null, 'label_ar' => 'تاريخ القفل', 'label_en' => 'Lock Date'],
            'tax_lock_date' => ['type' => 'string', 'default' => null, 'label_ar' => 'تاريخ قفل الضريبة', 'label_en' => 'Tax Lock Date'],
            'decimal_places' => ['type' => 'integer', 'default' => 2, 'label_ar' => 'عدد الخانات العشرية', 'label_en' => 'Decimal Places'],
            'chart_template' => ['type' => 'string', 'default' => 'saudi_commercial', 'label_ar' => 'قالب شجرة الحسابات', 'label_en' => 'Chart of Accounts Template', 'options' => ['saudi_commercial', 'saudi_industrial', 'saudi_services', 'saudi_contractor', 'ifrs_standard']],
            'accounting_method' => ['type' => 'string', 'default' => 'accrual', 'label_ar' => 'أساس المحاسبة', 'label_en' => 'Accounting Method', 'options' => ['accrual', 'cash']],
        ],
        'journal_entries' => [
            'auto_numbering' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'الترقيم التلقائي', 'label_en' => 'Auto Numbering'],
            'numbering_prefix' => ['type' => 'string', 'default' => 'JV-', 'label_ar' => 'بادئة الترقيم', 'label_en' => 'Numbering Prefix'],
            'require_approval' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'يتطلب موافقة', 'label_en' => 'Require Approval'],
            'approval_threshold' => ['type' => 'float', 'default' => 50000, 'label_ar' => 'حد الموافقة', 'label_en' => 'Approval Threshold'],
            'allow_future_date' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'السماح بتاريخ مستقبلي', 'label_en' => 'Allow Future Date'],
            'require_attachment' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'إلزام إرفاق مستند', 'label_en' => 'Require Attachment'],
            'allow_edit_posted' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'السماح بتعديل المرحل', 'label_en' => 'Allow Edit Posted'],
        ],
        'period_closing' => [
            'auto_close_monthly' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'إقفال شهري تلقائي', 'label_en' => 'Auto Monthly Close'],
            'closing_day' => ['type' => 'integer', 'default' => 5, 'label_ar' => 'يوم الإقفال', 'label_en' => 'Closing Day'],
            'require_reconciliation' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'يتطلب تسوية', 'label_en' => 'Require Reconciliation'],
            'retained_earnings_account' => ['type' => 'string', 'default' => null, 'label_ar' => 'حساب الأرباح المبقاة', 'label_en' => 'Retained Earnings Account'],
        ],
        'cost_centers' => [
            'enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل مراكز التكلفة', 'label_en' => 'Enable Cost Centers'],
            'mandatory' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'إلزامي في القيود', 'label_en' => 'Mandatory in Entries'],
            'max_levels' => ['type' => 'integer', 'default' => 5, 'label_ar' => 'أقصى عدد مستويات', 'label_en' => 'Max Levels'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 🧾 E-INVOICING - الفوترة الإلكترونية (ZATCA)
    // ══════════════════════════════════════════════════════════════════════
    'e_invoicing' => [
        'zatca' => [
            'enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل الفوترة الإلكترونية', 'label_en' => 'Enable E-Invoicing'],
            'environment' => ['type' => 'string', 'default' => 'sandbox', 'label_ar' => 'البيئة', 'label_en' => 'Environment', 'options' => ['sandbox', 'simulation', 'production']],
            'phase' => ['type' => 'string', 'default' => 'phase2', 'label_ar' => 'المرحلة', 'label_en' => 'Phase', 'options' => ['phase1', 'phase2']],
            'clearance_mode' => ['type' => 'string', 'default' => 'clearance', 'label_ar' => 'وضع الربط', 'label_en' => 'Clearance Mode', 'options' => ['clearance', 'reporting']],
            'otp' => ['type' => 'string', 'default' => null, 'label_ar' => 'رمز OTP', 'label_en' => 'OTP Code'],
            'csr_common_name' => ['type' => 'string', 'default' => null, 'label_ar' => 'اسم CSR', 'label_en' => 'CSR Common Name'],
            'compliance_csid' => ['type' => 'string', 'default' => null, 'label_ar' => 'شهادة الامتثال', 'label_en' => 'Compliance CSID'],
            'production_csid' => ['type' => 'string', 'default' => null, 'label_ar' => 'شهادة الإنتاج', 'label_en' => 'Production CSID'],
            'private_key' => ['type' => 'string', 'default' => null, 'label_ar' => 'المفتاح الخاص', 'label_en' => 'Private Key'],
            'auto_submit' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إرسال تلقائي للهيئة', 'label_en' => 'Auto Submit to ZATCA'],
            'retry_failed' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إعادة المحاولة تلقائياً', 'label_en' => 'Auto Retry Failed'],
            'retry_attempts' => ['type' => 'integer', 'default' => 3, 'label_ar' => 'عدد المحاولات', 'label_en' => 'Retry Attempts'],
        ],
        'invoice' => [
            'default_type' => ['type' => 'string', 'default' => 'standard', 'label_ar' => 'نوع الفاتورة الافتراضي', 'label_en' => 'Default Invoice Type', 'options' => ['standard', 'simplified']],
            'prefix_standard' => ['type' => 'string', 'default' => 'INV-', 'label_ar' => 'بادئة الفاتورة الضريبية', 'label_en' => 'Standard Invoice Prefix'],
            'prefix_simplified' => ['type' => 'string', 'default' => 'SINV-', 'label_ar' => 'بادئة المبسطة', 'label_en' => 'Simplified Invoice Prefix'],
            'prefix_credit_note' => ['type' => 'string', 'default' => 'CN-', 'label_ar' => 'بادئة إشعار الدائن', 'label_en' => 'Credit Note Prefix'],
            'prefix_debit_note' => ['type' => 'string', 'default' => 'DN-', 'label_ar' => 'بادئة إشعار المدين', 'label_en' => 'Debit Note Prefix'],
            'default_payment_terms' => ['type' => 'integer', 'default' => 30, 'label_ar' => 'شروط السداد (أيام)', 'label_en' => 'Payment Terms (days)'],
            'auto_generate_qr' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'توليد QR تلقائي', 'label_en' => 'Auto Generate QR'],
            'show_discount' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إظهار الخصم', 'label_en' => 'Show Discount'],
            'allow_zero_amount' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'السماح بفاتورة صفرية', 'label_en' => 'Allow Zero Amount'],
        ],
        'tax' => [
            'default_vat_rate' => ['type' => 'float', 'default' => 15.0, 'label_ar' => 'نسبة ض.ق.م الافتراضية', 'label_en' => 'Default VAT Rate'],
            'prices_include_tax' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'الأسعار شاملة الضريبة', 'label_en' => 'Prices Include Tax'],
            'tax_rounding_method' => ['type' => 'string', 'default' => 'per_line', 'label_ar' => 'طريقة تقريب الضريبة', 'label_en' => 'Tax Rounding', 'options' => ['per_line', 'global']],
            'withholding_tax_enabled' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'ضريبة الاستقطاع', 'label_en' => 'Withholding Tax'],
            'withholding_tax_rate' => ['type' => 'float', 'default' => 5.0, 'label_ar' => 'نسبة ضريبة الاستقطاع', 'label_en' => 'Withholding Tax Rate'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 💰 FINANCE - المالية
    // ══════════════════════════════════════════════════════════════════════
    'finance' => [
        'payments' => [
            'auto_allocate' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'توزيع تلقائي', 'label_en' => 'Auto Allocate'],
            'allow_overpayment' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'السماح بالدفع الزائد', 'label_en' => 'Allow Overpayment'],
            'default_payment_method' => ['type' => 'string', 'default' => 'bank_transfer', 'label_ar' => 'طريقة الدفع الافتراضية', 'label_en' => 'Default Payment Method', 'options' => ['cash', 'bank_transfer', 'cheque', 'credit_card', 'mada']],
            'receipt_prefix' => ['type' => 'string', 'default' => 'RV-', 'label_ar' => 'بادئة سند القبض', 'label_en' => 'Receipt Voucher Prefix'],
            'payment_prefix' => ['type' => 'string', 'default' => 'PV-', 'label_ar' => 'بادئة سند الصرف', 'label_en' => 'Payment Voucher Prefix'],
            'require_approval_above' => ['type' => 'float', 'default' => 100000, 'label_ar' => 'يتطلب موافقة فوق', 'label_en' => 'Require Approval Above'],
        ],
        'treasury' => [
            'cash_flow_forecast_days' => ['type' => 'integer', 'default' => 90, 'label_ar' => 'أيام توقع التدفق النقدي', 'label_en' => 'Cash Flow Forecast Days'],
            'minimum_cash_balance' => ['type' => 'float', 'default' => 50000, 'label_ar' => 'الحد الأدنى للرصيد النقدي', 'label_en' => 'Minimum Cash Balance'],
            'alert_low_balance' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تنبيه انخفاض الرصيد', 'label_en' => 'Alert Low Balance'],
        ],
        'intercompany' => [
            'enabled' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'تفعيل المعاملات بين الشركات', 'label_en' => 'Enable Intercompany'],
            'auto_reconcile' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تسوية تلقائية', 'label_en' => 'Auto Reconcile'],
            'elimination_account' => ['type' => 'string', 'default' => null, 'label_ar' => 'حساب الاستبعاد', 'label_en' => 'Elimination Account'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 🏦 BANKING - البنوك
    // ══════════════════════════════════════════════════════════════════════
    'banking' => [
        'general' => [
            'auto_reconciliation' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تسوية بنكية تلقائية', 'label_en' => 'Auto Reconciliation'],
            'reconciliation_tolerance' => ['type' => 'float', 'default' => 0.01, 'label_ar' => 'هامش التسوية', 'label_en' => 'Reconciliation Tolerance'],
            'import_format' => ['type' => 'string', 'default' => 'csv', 'label_ar' => 'صيغة الاستيراد', 'label_en' => 'Import Format', 'options' => ['csv', 'ofx', 'mt940', 'camt053']],
            'auto_match_rules' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'قواعد المطابقة التلقائية', 'label_en' => 'Auto Match Rules'],
        ],
        'cheques' => [
            'enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل إدارة الشيكات', 'label_en' => 'Enable Cheque Management'],
            'cheque_prefix' => ['type' => 'string', 'default' => 'CHQ-', 'label_ar' => 'بادئة الشيك', 'label_en' => 'Cheque Prefix'],
            'post_dated_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'شيكات آجلة', 'label_en' => 'Post-Dated Cheques'],
            'alert_before_due' => ['type' => 'integer', 'default' => 3, 'label_ar' => 'تنبيه قبل الاستحقاق (أيام)', 'label_en' => 'Alert Before Due (days)'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 📊 BUDGETING - الميزانيات
    // ══════════════════════════════════════════════════════════════════════
    'budgeting' => [
        'general' => [
            'budget_type' => ['type' => 'string', 'default' => 'annual', 'label_ar' => 'نوع الميزانية', 'label_en' => 'Budget Type', 'options' => ['annual', 'quarterly', 'monthly', 'project']],
            'allow_exceed' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'السماح بتجاوز الميزانية', 'label_en' => 'Allow Exceed Budget'],
            'exceed_action' => ['type' => 'string', 'default' => 'warn', 'label_ar' => 'إجراء التجاوز', 'label_en' => 'Exceed Action', 'options' => ['warn', 'block', 'approve']],
            'exceed_threshold_percent' => ['type' => 'float', 'default' => 90, 'label_ar' => 'نسبة التنبيه', 'label_en' => 'Alert Threshold %'],
            'auto_forecast' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تنبؤ تلقائي بالذكاء الاصطناعي', 'label_en' => 'AI Auto Forecast'],
            'variance_analysis' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تحليل الانحرافات', 'label_en' => 'Variance Analysis'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 🏗️ FIXED ASSETS - الأصول الثابتة
    // ══════════════════════════════════════════════════════════════════════
    'fixed_assets' => [
        'general' => [
            'depreciation_method' => ['type' => 'string', 'default' => 'straight_line', 'label_ar' => 'طريقة الإهلاك', 'label_en' => 'Depreciation Method', 'options' => ['straight_line', 'declining_balance', 'units_of_production', 'sum_of_years']],
            'auto_depreciation' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إهلاك تلقائي شهري', 'label_en' => 'Auto Monthly Depreciation'],
            'depreciation_day' => ['type' => 'integer', 'default' => 1, 'label_ar' => 'يوم احتساب الإهلاك', 'label_en' => 'Depreciation Day'],
            'minimum_value' => ['type' => 'float', 'default' => 1000, 'label_ar' => 'الحد الأدنى لقيمة الأصل', 'label_en' => 'Minimum Asset Value'],
            'barcode_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل الباركود', 'label_en' => 'Enable Barcode'],
            'auto_disposal_journal' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'قيد استبعاد تلقائي', 'label_en' => 'Auto Disposal Journal'],
            'revaluation_enabled' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'إعادة التقييم', 'label_en' => 'Enable Revaluation'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 📈 CRM - إدارة علاقات العملاء
    // ══════════════════════════════════════════════════════════════════════
    'crm' => [
        'general' => [
            'lead_scoring_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل تقييم العملاء المحتملين', 'label_en' => 'Enable Lead Scoring'],
            'auto_assign_leads' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'توزيع تلقائي', 'label_en' => 'Auto Assign Leads'],
            'assignment_method' => ['type' => 'string', 'default' => 'round_robin', 'label_ar' => 'طريقة التوزيع', 'label_en' => 'Assignment Method', 'options' => ['round_robin', 'load_balanced', 'territory', 'ai_based']],
            'lead_auto_archive_days' => ['type' => 'integer', 'default' => 90, 'label_ar' => 'أرشفة تلقائية (أيام)', 'label_en' => 'Auto Archive Days'],
            'duplicate_detection' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'كشف التكرار', 'label_en' => 'Duplicate Detection'],
        ],
        'pipeline' => [
            'default_stages' => ['type' => 'json', 'default' => '["New","Qualified","Proposition","Negotiation","Won","Lost"]', 'label_ar' => 'مراحل افتراضية', 'label_en' => 'Default Stages'],
            'probability_per_stage' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'احتمالية لكل مرحلة', 'label_en' => 'Probability Per Stage'],
            'require_lost_reason' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إلزام سبب الخسارة', 'label_en' => 'Require Lost Reason'],
            'auto_move_won' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'نقل تلقائي عند الفوز', 'label_en' => 'Auto Move on Win'],
        ],
        'communication' => [
            'email_tracking' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تتبع الإيميل', 'label_en' => 'Email Tracking'],
            'whatsapp_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل واتساب', 'label_en' => 'Enable WhatsApp'],
            'sms_enabled' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'تفعيل SMS', 'label_en' => 'Enable SMS'],
            'activity_reminders' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تذكيرات الأنشطة', 'label_en' => 'Activity Reminders'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 🛒 SALES - المبيعات
    // ══════════════════════════════════════════════════════════════════════
    'sales' => [
        'general' => [
            'quotation_validity_days' => ['type' => 'integer', 'default' => 15, 'label_ar' => 'صلاحية عرض السعر (أيام)', 'label_en' => 'Quotation Validity (days)'],
            'auto_confirm_order' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'تأكيد تلقائي', 'label_en' => 'Auto Confirm Order'],
            'require_approval' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'يتطلب موافقة', 'label_en' => 'Require Approval'],
            'approval_threshold' => ['type' => 'float', 'default' => 50000, 'label_ar' => 'حد الموافقة', 'label_en' => 'Approval Threshold'],
            'lock_confirmed_orders' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'قفل الطلبات المؤكدة', 'label_en' => 'Lock Confirmed Orders'],
            'allow_discount' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'السماح بالخصم', 'label_en' => 'Allow Discount'],
            'max_discount_percent' => ['type' => 'float', 'default' => 20, 'label_ar' => 'أقصى نسبة خصم', 'label_en' => 'Max Discount %'],
        ],
        'pricing' => [
            'multiple_price_lists' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'قوائم أسعار متعددة', 'label_en' => 'Multiple Price Lists'],
            'pricing_strategy' => ['type' => 'string', 'default' => 'fixed', 'label_ar' => 'استراتيجية التسعير', 'label_en' => 'Pricing Strategy', 'options' => ['fixed', 'formula', 'tiered']],
            'promotions_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل العروض', 'label_en' => 'Enable Promotions'],
            'loyalty_program' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'برنامج ولاء', 'label_en' => 'Loyalty Program'],
        ],
        'commission' => [
            'enabled' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'تفعيل العمولات', 'label_en' => 'Enable Commission'],
            'calculation_method' => ['type' => 'string', 'default' => 'invoice_paid', 'label_ar' => 'طريقة الاحتساب', 'label_en' => 'Calculation Method', 'options' => ['order_confirmed', 'invoice_issued', 'invoice_paid']],
            'default_rate' => ['type' => 'float', 'default' => 5.0, 'label_ar' => 'النسبة الافتراضية', 'label_en' => 'Default Rate %'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 📦 PURCHASE - المشتريات
    // ══════════════════════════════════════════════════════════════════════
    'purchase' => [
        'general' => [
            'require_requisition' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إلزام طلب الشراء', 'label_en' => 'Require Requisition'],
            'three_way_match' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'مطابقة ثلاثية', 'label_en' => '3-Way Match'],
            'auto_create_po' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'إنشاء أمر شراء تلقائي', 'label_en' => 'Auto Create PO'],
            'po_prefix' => ['type' => 'string', 'default' => 'PO-', 'label_ar' => 'بادئة أمر الشراء', 'label_en' => 'PO Prefix'],
            'rfq_prefix' => ['type' => 'string', 'default' => 'RFQ-', 'label_ar' => 'بادئة طلب عرض السعر', 'label_en' => 'RFQ Prefix'],
        ],
        'approval' => [
            'approval_levels' => ['type' => 'integer', 'default' => 2, 'label_ar' => 'مستويات الموافقة', 'label_en' => 'Approval Levels'],
            'level1_threshold' => ['type' => 'float', 'default' => 10000, 'label_ar' => 'حد المستوى الأول', 'label_en' => 'Level 1 Threshold'],
            'level2_threshold' => ['type' => 'float', 'default' => 50000, 'label_ar' => 'حد المستوى الثاني', 'label_en' => 'Level 2 Threshold'],
            'level3_threshold' => ['type' => 'float', 'default' => 200000, 'label_ar' => 'حد المستوى الثالث', 'label_en' => 'Level 3 Threshold'],
        ],
        'vendor' => [
            'rating_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تقييم الموردين', 'label_en' => 'Vendor Rating'],
            'rating_criteria' => ['type' => 'json', 'default' => '["quality","delivery","price","service"]', 'label_ar' => 'معايير التقييم', 'label_en' => 'Rating Criteria'],
            'blacklist_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'القائمة السوداء', 'label_en' => 'Blacklist Enabled'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 📦 INVENTORY - المخزون
    // ══════════════════════════════════════════════════════════════════════
    'inventory' => [
        'general' => [
            'valuation_method' => ['type' => 'string', 'default' => 'weighted_average', 'label_ar' => 'طريقة التقييم', 'label_en' => 'Valuation Method', 'options' => ['fifo', 'lifo', 'weighted_average', 'standard_cost']],
            'negative_stock_allowed' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'السماح بالمخزون السالب', 'label_en' => 'Allow Negative Stock'],
            'auto_reorder' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إعادة طلب تلقائي', 'label_en' => 'Auto Reorder'],
            'expiry_tracking' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تتبع الصلاحية', 'label_en' => 'Expiry Tracking'],
            'batch_tracking' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تتبع الدفعات', 'label_en' => 'Batch Tracking'],
            'serial_tracking' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'تتبع الرقم التسلسلي', 'label_en' => 'Serial Tracking'],
            'barcode_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل الباركود', 'label_en' => 'Enable Barcode'],
        ],
        'warehouse' => [
            'multi_warehouse' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'مستودعات متعددة', 'label_en' => 'Multi Warehouse'],
            'bin_location_enabled' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'مواقع التخزين', 'label_en' => 'Bin Locations'],
            'pick_pack_ship' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'تجهيز/تعبئة/شحن', 'label_en' => 'Pick/Pack/Ship'],
            'wave_picking' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'التجميع الموجي', 'label_en' => 'Wave Picking'],
            'cross_docking' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'Cross-Docking', 'label_en' => 'Cross-Docking'],
        ],
        'counting' => [
            'cycle_count_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'الجرد الدوري', 'label_en' => 'Cycle Count'],
            'count_frequency' => ['type' => 'string', 'default' => 'monthly', 'label_ar' => 'تكرار الجرد', 'label_en' => 'Count Frequency', 'options' => ['daily', 'weekly', 'monthly', 'quarterly']],
            'variance_threshold' => ['type' => 'float', 'default' => 5.0, 'label_ar' => 'حد الانحراف المقبول %', 'label_en' => 'Variance Threshold %'],
            'require_supervisor_approval' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'يتطلب موافقة المشرف', 'label_en' => 'Require Supervisor Approval'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 👥 HR - الموارد البشرية
    // ══════════════════════════════════════════════════════════════════════
    'hr' => [
        'general' => [
            'country' => ['type' => 'string', 'default' => 'SA', 'label_ar' => 'الدولة', 'label_en' => 'Country'],
            'employee_id_format' => ['type' => 'string', 'default' => 'EMP-{0000}', 'label_ar' => 'صيغة الرقم الوظيفي', 'label_en' => 'Employee ID Format'],
            'probation_period_days' => ['type' => 'integer', 'default' => 90, 'label_ar' => 'فترة التجربة (أيام)', 'label_en' => 'Probation Period (days)'],
            'notice_period_days' => ['type' => 'integer', 'default' => 30, 'label_ar' => 'فترة الإشعار (أيام)', 'label_en' => 'Notice Period (days)'],
            'eos_calculation' => ['type' => 'string', 'default' => 'saudi_labor_law', 'label_ar' => 'حساب مكافأة نهاية الخدمة', 'label_en' => 'EOS Calculation', 'options' => ['saudi_labor_law', 'custom', 'disabled']],
            'document_expiry_alert_days' => ['type' => 'integer', 'default' => 30, 'label_ar' => 'تنبيه انتهاء المستندات (أيام)', 'label_en' => 'Document Expiry Alert (days)'],
        ],
        'leave' => [
            'annual_leave_days' => ['type' => 'integer', 'default' => 21, 'label_ar' => 'أيام الإجازة السنوية', 'label_en' => 'Annual Leave Days'],
            'annual_leave_after_5years' => ['type' => 'integer', 'default' => 30, 'label_ar' => 'إجازة بعد 5 سنوات', 'label_en' => 'Leave After 5 Years'],
            'carry_forward' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'ترحيل الرصيد', 'label_en' => 'Carry Forward'],
            'max_carry_forward_days' => ['type' => 'integer', 'default' => 10, 'label_ar' => 'أقصى ترحيل (أيام)', 'label_en' => 'Max Carry Forward'],
            'require_approval' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'يتطلب موافقة', 'label_en' => 'Require Approval'],
            'approval_chain' => ['type' => 'string', 'default' => 'direct_manager', 'label_ar' => 'سلسلة الموافقة', 'label_en' => 'Approval Chain', 'options' => ['direct_manager', 'hr_only', 'manager_then_hr']],
            'sick_leave_days' => ['type' => 'integer', 'default' => 120, 'label_ar' => 'أيام المرضية', 'label_en' => 'Sick Leave Days'],
        ],
        'compliance' => [
            'gosi_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل التأمينات', 'label_en' => 'Enable GOSI'],
            'gosi_employee_rate' => ['type' => 'float', 'default' => 9.75, 'label_ar' => 'نسبة الموظف %', 'label_en' => 'Employee Rate %'],
            'gosi_employer_rate' => ['type' => 'float', 'default' => 11.75, 'label_ar' => 'نسبة صاحب العمل %', 'label_en' => 'Employer Rate %'],
            'nitaqat_tracking' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تتبع نطاقات', 'label_en' => 'Nitaqat Tracking'],
            'wps_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'حماية الأجور', 'label_en' => 'WPS Enabled'],
            'muqeem_integration' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'ربط المقيم', 'label_en' => 'Muqeem Integration'],
            'qiwa_integration' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'ربط قوى', 'label_en' => 'Qiwa Integration'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 💵 PAYROLL - الرواتب
    // ══════════════════════════════════════════════════════════════════════
    'payroll' => [
        'general' => [
            'pay_frequency' => ['type' => 'string', 'default' => 'monthly', 'label_ar' => 'دورة الصرف', 'label_en' => 'Pay Frequency', 'options' => ['weekly', 'biweekly', 'monthly']],
            'pay_day' => ['type' => 'integer', 'default' => 27, 'label_ar' => 'يوم الصرف', 'label_en' => 'Pay Day'],
            'currency' => ['type' => 'string', 'default' => 'SAR', 'label_ar' => 'العملة', 'label_en' => 'Currency'],
            'auto_run' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'تشغيل تلقائي', 'label_en' => 'Auto Run'],
            'require_approval' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'يتطلب موافقة', 'label_en' => 'Require Approval'],
            'post_to_accounting' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'ترحيل للمحاسبة', 'label_en' => 'Post to Accounting'],
        ],
        'salary_structure' => [
            'basic_percentage' => ['type' => 'float', 'default' => 60, 'label_ar' => 'نسبة الراتب الأساسي %', 'label_en' => 'Basic Salary %'],
            'housing_allowance' => ['type' => 'float', 'default' => 25, 'label_ar' => 'بدل السكن %', 'label_en' => 'Housing Allowance %'],
            'transport_allowance' => ['type' => 'float', 'default' => 10, 'label_ar' => 'بدل النقل %', 'label_en' => 'Transport Allowance %'],
            'other_allowances' => ['type' => 'float', 'default' => 5, 'label_ar' => 'بدلات أخرى %', 'label_en' => 'Other Allowances %'],
            'overtime_rate' => ['type' => 'float', 'default' => 1.5, 'label_ar' => 'معامل الإضافي', 'label_en' => 'Overtime Multiplier'],
            'weekend_overtime_rate' => ['type' => 'float', 'default' => 2.0, 'label_ar' => 'معامل إضافي العطلة', 'label_en' => 'Weekend OT Multiplier'],
        ],
        'deductions' => [
            'absence_deduction' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'خصم الغياب', 'label_en' => 'Absence Deduction'],
            'late_deduction' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'خصم التأخير', 'label_en' => 'Late Deduction'],
            'loan_deduction' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'خصم السلف', 'label_en' => 'Loan Deduction'],
            'max_loan_deduction_percent' => ['type' => 'float', 'default' => 33.33, 'label_ar' => 'أقصى خصم سلف %', 'label_en' => 'Max Loan Deduction %'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // ⏰ ATTENDANCE - الحضور والانصراف
    // ══════════════════════════════════════════════════════════════════════
    'attendance' => [
        'general' => [
            'tracking_method' => ['type' => 'string', 'default' => 'biometric', 'label_ar' => 'طريقة التتبع', 'label_en' => 'Tracking Method', 'options' => ['biometric', 'gps', 'face_recognition', 'manual', 'qr_code']],
            'grace_period_minutes' => ['type' => 'integer', 'default' => 15, 'label_ar' => 'فترة السماح (دقائق)', 'label_en' => 'Grace Period (minutes)'],
            'auto_checkout' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'خروج تلقائي', 'label_en' => 'Auto Checkout'],
            'auto_checkout_time' => ['type' => 'string', 'default' => '23:59', 'label_ar' => 'وقت الخروج التلقائي', 'label_en' => 'Auto Checkout Time'],
            'minimum_hours' => ['type' => 'float', 'default' => 8.0, 'label_ar' => 'الحد الأدنى (ساعات)', 'label_en' => 'Minimum Hours'],
            'flexible_hours' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'ساعات مرنة', 'label_en' => 'Flexible Hours'],
        ],
        'overtime' => [
            'auto_calculate' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'حساب تلقائي', 'label_en' => 'Auto Calculate'],
            'require_approval' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'يتطلب موافقة', 'label_en' => 'Require Approval'],
            'max_daily_hours' => ['type' => 'float', 'default' => 4.0, 'label_ar' => 'أقصى ساعات يومية', 'label_en' => 'Max Daily OT Hours'],
            'max_monthly_hours' => ['type' => 'float', 'default' => 40.0, 'label_ar' => 'أقصى ساعات شهرية', 'label_en' => 'Max Monthly OT Hours'],
        ],
        'devices' => [
            'zkteco_enabled' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'ربط ZKTeco', 'label_en' => 'ZKTeco Integration'],
            'hikvision_enabled' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'ربط Hikvision', 'label_en' => 'Hikvision Integration'],
            'sync_interval_minutes' => ['type' => 'integer', 'default' => 5, 'label_ar' => 'فاصل المزامنة (دقائق)', 'label_en' => 'Sync Interval (minutes)'],
        ],
    ],


    // ══════════════════════════════════════════════════════════════════════
    // 🏭 MANUFACTURING - التصنيع
    // ══════════════════════════════════════════════════════════════════════
    'manufacturing' => [
        'general' => [
            'bom_levels' => ['type' => 'integer', 'default' => 5, 'label_ar' => 'مستويات قائمة المواد', 'label_en' => 'BOM Levels'],
            'auto_consume_materials' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'استهلاك تلقائي للمواد', 'label_en' => 'Auto Consume Materials'],
            'backflush_materials' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'صرف عكسي', 'label_en' => 'Backflush Materials'],
            'costing_method' => ['type' => 'string', 'default' => 'standard', 'label_ar' => 'طريقة التكلفة', 'label_en' => 'Costing Method', 'options' => ['standard', 'actual', 'average']],
            'scrap_account' => ['type' => 'string', 'default' => null, 'label_ar' => 'حساب الهالك', 'label_en' => 'Scrap Account'],
            'wip_account' => ['type' => 'string', 'default' => null, 'label_ar' => 'حساب تحت التشغيل', 'label_en' => 'WIP Account'],
        ],
        'production' => [
            'auto_create_mo' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'إنشاء أمر إنتاج تلقائي', 'label_en' => 'Auto Create MO'],
            'mo_prefix' => ['type' => 'string', 'default' => 'MO-', 'label_ar' => 'بادئة أمر الإنتاج', 'label_en' => 'MO Prefix'],
            'require_quality_check' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'فحص جودة إلزامي', 'label_en' => 'Require Quality Check'],
            'allow_partial_production' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إنتاج جزئي', 'label_en' => 'Allow Partial Production'],
            'auto_close_completed' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إغلاق تلقائي للمكتمل', 'label_en' => 'Auto Close Completed'],
        ],
        'mrp' => [
            'enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل تخطيط المواد', 'label_en' => 'Enable MRP'],
            'planning_horizon_days' => ['type' => 'integer', 'default' => 90, 'label_ar' => 'أفق التخطيط (أيام)', 'label_en' => 'Planning Horizon (days)'],
            'safety_stock_days' => ['type' => 'integer', 'default' => 7, 'label_ar' => 'أيام مخزون الأمان', 'label_en' => 'Safety Stock Days'],
            'lead_time_default' => ['type' => 'integer', 'default' => 14, 'label_ar' => 'مهلة التوريد الافتراضية (أيام)', 'label_en' => 'Default Lead Time (days)'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 📋 PROJECTS - المشاريع
    // ══════════════════════════════════════════════════════════════════════
    'projects' => [
        'general' => [
            'project_prefix' => ['type' => 'string', 'default' => 'PRJ-', 'label_ar' => 'بادئة المشروع', 'label_en' => 'Project Prefix'],
            'default_billing_type' => ['type' => 'string', 'default' => 'fixed_price', 'label_ar' => 'نوع الفوترة الافتراضي', 'label_en' => 'Default Billing Type', 'options' => ['fixed_price', 'time_material', 'milestone', 'progress']],
            'time_tracking_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تتبع الوقت', 'label_en' => 'Time Tracking'],
            'gantt_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'مخطط جانت', 'label_en' => 'Gantt Chart'],
            'resource_planning' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تخطيط الموارد', 'label_en' => 'Resource Planning'],
            'risk_management' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'إدارة المخاطر', 'label_en' => 'Risk Management'],
            'evm_enabled' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'تحليل القيمة المكتسبة', 'label_en' => 'Earned Value Management'],
        ],
        'construction' => [
            'enabled' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'تفعيل موديول المقاولات', 'label_en' => 'Enable Construction'],
            'boq_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'جداول الكميات', 'label_en' => 'BOQ Enabled'],
            'progress_billing' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'المستخلصات', 'label_en' => 'Progress Billing'],
            'retention_percent' => ['type' => 'float', 'default' => 10, 'label_ar' => 'نسبة الاحتفاظ %', 'label_en' => 'Retention %'],
            'variation_orders' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'أوامر التغيير', 'label_en' => 'Variation Orders'],
            'subcontractor_management' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إدارة مقاولي الباطن', 'label_en' => 'Subcontractor Mgmt'],
            'hse_module' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'السلامة المهنية', 'label_en' => 'HSE Module'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 🚗 FLEET - إدارة الأسطول
    // ══════════════════════════════════════════════════════════════════════
    'fleet' => [
        'general' => [
            'gps_tracking' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تتبع GPS', 'label_en' => 'GPS Tracking'],
            'gps_provider' => ['type' => 'string', 'default' => null, 'label_ar' => 'مزود GPS', 'label_en' => 'GPS Provider', 'options' => ['wialon', 'cartrack', 'tracksolid', 'custom']],
            'fuel_tracking' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تتبع الوقود', 'label_en' => 'Fuel Tracking'],
            'fuel_card_integration' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'ربط بطاقات الوقود', 'label_en' => 'Fuel Card Integration'],
            'cost_per_km_tracking' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تتبع تكلفة الكيلومتر', 'label_en' => 'Cost Per KM'],
            'odometer_unit' => ['type' => 'string', 'default' => 'km', 'label_ar' => 'وحدة العداد', 'label_en' => 'Odometer Unit', 'options' => ['km', 'miles']],
        ],
        'maintenance' => [
            'preventive_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'الصيانة الوقائية', 'label_en' => 'Preventive Maintenance'],
            'alert_before_km' => ['type' => 'integer', 'default' => 1000, 'label_ar' => 'تنبيه قبل (كم)', 'label_en' => 'Alert Before (km)'],
            'alert_before_days' => ['type' => 'integer', 'default' => 7, 'label_ar' => 'تنبيه قبل (أيام)', 'label_en' => 'Alert Before (days)'],
            'auto_create_wo' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'أمر عمل تلقائي', 'label_en' => 'Auto Create Work Order'],
        ],
        'compliance' => [
            'insurance_tracking' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تتبع التأمين', 'label_en' => 'Insurance Tracking'],
            'registration_tracking' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تتبع الاستمارة', 'label_en' => 'Registration Tracking'],
            'license_tracking' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تتبع الرخص', 'label_en' => 'License Tracking'],
            'violation_tracking' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تتبع المخالفات', 'label_en' => 'Violation Tracking'],
            'alert_expiry_days' => ['type' => 'integer', 'default' => 30, 'label_ar' => 'تنبيه قبل الانتهاء (أيام)', 'label_en' => 'Alert Before Expiry (days)'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 🏪 RETAIL / POS - نقاط البيع
    // ══════════════════════════════════════════════════════════════════════
    'pos' => [
        'general' => [
            'offline_mode' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'وضع بدون إنترنت', 'label_en' => 'Offline Mode'],
            'receipt_printer' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'طابعة الفواتير', 'label_en' => 'Receipt Printer'],
            'barcode_scanner' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'ماسح الباركود', 'label_en' => 'Barcode Scanner'],
            'cash_drawer' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'درج النقد', 'label_en' => 'Cash Drawer'],
            'customer_display' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'شاشة العميل', 'label_en' => 'Customer Display'],
            'scale_integration' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'ربط الميزان', 'label_en' => 'Scale Integration'],
        ],
        'payment' => [
            'cash_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'الدفع نقداً', 'label_en' => 'Cash Payment'],
            'mada_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'مدى', 'label_en' => 'mada'],
            'visa_mastercard' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'فيزا/ماستركارد', 'label_en' => 'Visa/Mastercard'],
            'apple_pay' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'Apple Pay', 'label_en' => 'Apple Pay'],
            'stc_pay' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'STC Pay', 'label_en' => 'STC Pay'],
            'split_payment' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تقسيم الدفع', 'label_en' => 'Split Payment'],
            'credit_sale' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'بيع آجل', 'label_en' => 'Credit Sale'],
        ],
        'receipt' => [
            'header_text' => ['type' => 'string', 'default' => null, 'label_ar' => 'نص رأس الفاتورة', 'label_en' => 'Receipt Header'],
            'footer_text' => ['type' => 'string', 'default' => 'شكراً لزيارتكم', 'label_ar' => 'نص تذييل الفاتورة', 'label_en' => 'Receipt Footer'],
            'show_logo' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إظهار الشعار', 'label_en' => 'Show Logo'],
            'show_vat_number' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إظهار الرقم الضريبي', 'label_en' => 'Show VAT Number'],
            'show_qr_code' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إظهار QR', 'label_en' => 'Show QR Code'],
            'auto_print' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'طباعة تلقائية', 'label_en' => 'Auto Print'],
        ],
        'shift' => [
            'require_cash_count' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إلزام عد النقد', 'label_en' => 'Require Cash Count'],
            'opening_balance_required' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'رصيد افتتاحي إلزامي', 'label_en' => 'Opening Balance Required'],
            'auto_close_time' => ['type' => 'string', 'default' => null, 'label_ar' => 'إغلاق تلقائي', 'label_en' => 'Auto Close Time'],
            'variance_alert' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تنبيه فروقات', 'label_en' => 'Variance Alert'],
            'max_variance_amount' => ['type' => 'float', 'default' => 50, 'label_ar' => 'أقصى فرق مقبول', 'label_en' => 'Max Variance Amount'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 🏨 HOTEL - إدارة الفنادق
    // ══════════════════════════════════════════════════════════════════════
    'hotel' => [
        'general' => [
            'check_in_time' => ['type' => 'string', 'default' => '15:00', 'label_ar' => 'وقت تسجيل الدخول', 'label_en' => 'Check-in Time'],
            'check_out_time' => ['type' => 'string', 'default' => '12:00', 'label_ar' => 'وقت تسجيل الخروج', 'label_en' => 'Check-out Time'],
            'early_checkin_charge' => ['type' => 'float', 'default' => 50, 'label_ar' => 'رسوم الدخول المبكر %', 'label_en' => 'Early Check-in Charge %'],
            'late_checkout_charge' => ['type' => 'float', 'default' => 50, 'label_ar' => 'رسوم الخروج المتأخر %', 'label_en' => 'Late Check-out Charge %'],
            'overbooking_allowed' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'السماح بالحجز الزائد', 'label_en' => 'Overbooking Allowed'],
            'overbooking_percent' => ['type' => 'float', 'default' => 5, 'label_ar' => 'نسبة الحجز الزائد %', 'label_en' => 'Overbooking %'],
            'default_currency' => ['type' => 'string', 'default' => 'SAR', 'label_ar' => 'العملة الافتراضية', 'label_en' => 'Default Currency'],
            'tourism_tax_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'ضريبة السياحة', 'label_en' => 'Tourism Tax'],
            'tourism_tax_rate' => ['type' => 'float', 'default' => 5, 'label_ar' => 'نسبة ضريبة السياحة %', 'label_en' => 'Tourism Tax Rate %'],
        ],
        'reservation' => [
            'auto_confirmation' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'تأكيد تلقائي', 'label_en' => 'Auto Confirmation'],
            'confirmation_deposit' => ['type' => 'float', 'default' => 30, 'label_ar' => 'عربون التأكيد %', 'label_en' => 'Confirmation Deposit %'],
            'cancellation_policy_hours' => ['type' => 'integer', 'default' => 24, 'label_ar' => 'مهلة الإلغاء (ساعات)', 'label_en' => 'Cancellation Policy (hours)'],
            'no_show_charge' => ['type' => 'float', 'default' => 100, 'label_ar' => 'رسوم عدم الحضور %', 'label_en' => 'No-Show Charge %'],
            'group_booking_min' => ['type' => 'integer', 'default' => 5, 'label_ar' => 'حد الحجز الجماعي', 'label_en' => 'Group Booking Min Rooms'],
        ],
        'channels' => [
            'booking_com' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'Booking.com', 'label_en' => 'Booking.com'],
            'expedia' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'Expedia', 'label_en' => 'Expedia'],
            'agoda' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'Agoda', 'label_en' => 'Agoda'],
            'airbnb' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'Airbnb', 'label_en' => 'Airbnb'],
            'almosafer' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'المسافر', 'label_en' => 'Almosafer'],
            'sync_interval_minutes' => ['type' => 'integer', 'default' => 15, 'label_ar' => 'فاصل المزامنة (دقائق)', 'label_en' => 'Sync Interval (min)'],
            'rate_parity' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تكافؤ الأسعار', 'label_en' => 'Rate Parity'],
        ],
        'housekeeping' => [
            'auto_assign' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تعيين تلقائي', 'label_en' => 'Auto Assign'],
            'cleaning_time_minutes' => ['type' => 'integer', 'default' => 30, 'label_ar' => 'وقت التنظيف (دقائق)', 'label_en' => 'Cleaning Time (min)'],
            'inspection_required' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفتيش إلزامي', 'label_en' => 'Inspection Required'],
            'minibar_tracking' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تتبع الميني بار', 'label_en' => 'Minibar Tracking'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 🏢 REAL ESTATE - العقارات
    // ══════════════════════════════════════════════════════════════════════
    'real_estate' => [
        'general' => [
            'contract_prefix' => ['type' => 'string', 'default' => 'LC-', 'label_ar' => 'بادئة العقد', 'label_en' => 'Contract Prefix'],
            'ejar_integration' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'ربط إيجار', 'label_en' => 'Ejar Integration'],
            'auto_invoice' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'فوترة تلقائية', 'label_en' => 'Auto Invoice'],
            'invoice_advance_days' => ['type' => 'integer', 'default' => 5, 'label_ar' => 'إصدار الفاتورة قبل (أيام)', 'label_en' => 'Invoice Advance (days)'],
            'late_payment_penalty' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'غرامة تأخير', 'label_en' => 'Late Payment Penalty'],
            'penalty_rate' => ['type' => 'float', 'default' => 2, 'label_ar' => 'نسبة الغرامة %', 'label_en' => 'Penalty Rate %'],
            'tenant_portal' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'بوابة المستأجر', 'label_en' => 'Tenant Portal'],
        ],
        'maintenance' => [
            'tenant_can_request' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'طلب صيانة من المستأجر', 'label_en' => 'Tenant Can Request'],
            'auto_assign' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'تعيين تلقائي', 'label_en' => 'Auto Assign'],
            'sla_hours' => ['type' => 'integer', 'default' => 48, 'label_ar' => 'مهلة الاستجابة (ساعات)', 'label_en' => 'SLA Hours'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 🍽️ RESTAURANT - المطاعم
    // ══════════════════════════════════════════════════════════════════════
    'restaurant' => [
        'general' => [
            'table_management' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إدارة الطاولات', 'label_en' => 'Table Management'],
            'kds_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'شاشة المطبخ', 'label_en' => 'Kitchen Display'],
            'qr_order' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'طلب عبر QR', 'label_en' => 'QR Ordering'],
            'delivery_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل التوصيل', 'label_en' => 'Enable Delivery'],
            'service_charge' => ['type' => 'float', 'default' => 0, 'label_ar' => 'رسوم الخدمة %', 'label_en' => 'Service Charge %'],
            'tip_enabled' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'البقشيش', 'label_en' => 'Tips Enabled'],
        ],
        'delivery_apps' => [
            'hungerstation' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'هنقرستيشن', 'label_en' => 'HungerStation'],
            'jahez' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'جاهز', 'label_en' => 'Jahez'],
            'marsool' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'مرسول', 'label_en' => 'Marsool'],
            'talabat' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'طلبات', 'label_en' => 'Talabat'],
            'auto_accept_orders' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'قبول تلقائي', 'label_en' => 'Auto Accept Orders'],
            'prep_time_minutes' => ['type' => 'integer', 'default' => 20, 'label_ar' => 'وقت التحضير (دقائق)', 'label_en' => 'Prep Time (min)'],
        ],
        'inventory' => [
            'recipe_costing' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تكلفة الوصفات', 'label_en' => 'Recipe Costing'],
            'auto_deduction' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'خصم تلقائي من المخزون', 'label_en' => 'Auto Inventory Deduction'],
            'waste_tracking' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تتبع الهدر', 'label_en' => 'Waste Tracking'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 🎓 RECRUITMENT - التوظيف
    // ══════════════════════════════════════════════════════════════════════
    'recruitment' => [
        'general' => [
            'careers_page' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'صفحة وظائف', 'label_en' => 'Careers Page'],
            'auto_response' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'رد تلقائي', 'label_en' => 'Auto Response'],
            'ai_resume_screening' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'فرز بالذكاء الاصطناعي', 'label_en' => 'AI Resume Screening'],
            'interview_scheduling' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'جدولة المقابلات', 'label_en' => 'Interview Scheduling'],
            'reference_check' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'التحقق من المراجع', 'label_en' => 'Reference Check'],
            'onboarding_workflow' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'سير عمل الإلحاق', 'label_en' => 'Onboarding Workflow'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 🎫 HELPDESK - الدعم الفني
    // ══════════════════════════════════════════════════════════════════════
    'helpdesk' => [
        'general' => [
            'ticket_prefix' => ['type' => 'string', 'default' => 'TKT-', 'label_ar' => 'بادئة التذكرة', 'label_en' => 'Ticket Prefix'],
            'auto_assign' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تعيين تلقائي', 'label_en' => 'Auto Assign'],
            'assignment_method' => ['type' => 'string', 'default' => 'round_robin', 'label_ar' => 'طريقة التعيين', 'label_en' => 'Assignment Method', 'options' => ['round_robin', 'load_balanced', 'skill_based']],
            'sla_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل SLA', 'label_en' => 'Enable SLA'],
            'auto_escalation' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تصعيد تلقائي', 'label_en' => 'Auto Escalation'],
            'escalation_hours' => ['type' => 'integer', 'default' => 24, 'label_ar' => 'ساعات التصعيد', 'label_en' => 'Escalation Hours'],
            'csat_survey' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'استبيان الرضا', 'label_en' => 'CSAT Survey'],
            'ai_chatbot' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'روبوت المحادثة', 'label_en' => 'AI Chatbot'],
            'knowledge_base' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'قاعدة المعرفة', 'label_en' => 'Knowledge Base'],
        ],
        'channels' => [
            'email' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'البريد الإلكتروني', 'label_en' => 'Email'],
            'whatsapp' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'واتساب', 'label_en' => 'WhatsApp'],
            'live_chat' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'المحادثة المباشرة', 'label_en' => 'Live Chat'],
            'phone' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'الهاتف', 'label_en' => 'Phone'],
            'social_media' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'التواصل الاجتماعي', 'label_en' => 'Social Media'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 📄 DOCUMENT MANAGEMENT - إدارة المستندات
    // ══════════════════════════════════════════════════════════════════════
    'document_management' => [
        'general' => [
            'versioning_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'التحكم في الإصدارات', 'label_en' => 'Version Control'],
            'ocr_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل OCR', 'label_en' => 'Enable OCR'],
            'max_file_size_mb' => ['type' => 'integer', 'default' => 50, 'label_ar' => 'أقصى حجم (ميجا)', 'label_en' => 'Max File Size (MB)'],
            'allowed_extensions' => ['type' => 'json', 'default' => '["pdf","doc","docx","xls","xlsx","jpg","png"]', 'label_ar' => 'الامتدادات المسموحة', 'label_en' => 'Allowed Extensions'],
            'auto_archive_days' => ['type' => 'integer', 'default' => 365, 'label_ar' => 'أرشفة بعد (أيام)', 'label_en' => 'Auto Archive (days)'],
            'retention_years' => ['type' => 'integer', 'default' => 7, 'label_ar' => 'فترة الاحتفاظ (سنوات)', 'label_en' => 'Retention (years)'],
            'digital_signature' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'التوقيع الرقمي', 'label_en' => 'Digital Signature'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // ✅ QUALITY - الجودة
    // ══════════════════════════════════════════════════════════════════════
    'quality' => [
        'general' => [
            'inspection_mandatory' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'الفحص إلزامي', 'label_en' => 'Mandatory Inspection'],
            'auto_create_on_receipt' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'فحص عند الاستلام', 'label_en' => 'Inspect on Receipt'],
            'auto_create_on_production' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'فحص عند الإنتاج', 'label_en' => 'Inspect on Production'],
            'sampling_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'الفحص بالعينة', 'label_en' => 'Sampling Enabled'],
            'default_sample_size' => ['type' => 'integer', 'default' => 10, 'label_ar' => 'حجم العينة الافتراضي %', 'label_en' => 'Default Sample Size %'],
            'ncr_prefix' => ['type' => 'string', 'default' => 'NCR-', 'label_ar' => 'بادئة تقرير عدم المطابقة', 'label_en' => 'NCR Prefix'],
            'capa_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل CAPA', 'label_en' => 'Enable CAPA'],
            'iso_tracking' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'تتبع ISO', 'label_en' => 'ISO Tracking'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 🌐 ECOMMERCE - التجارة الإلكترونية
    // ══════════════════════════════════════════════════════════════════════
    'ecommerce' => [
        'general' => [
            'multi_channel' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'قنوات متعددة', 'label_en' => 'Multi Channel'],
            'real_time_sync' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'مزامنة فورية', 'label_en' => 'Real-time Sync'],
            'sync_interval_minutes' => ['type' => 'integer', 'default' => 5, 'label_ar' => 'فاصل المزامنة (دقائق)', 'label_en' => 'Sync Interval (min)'],
            'auto_fulfill_orders' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'تنفيذ تلقائي', 'label_en' => 'Auto Fulfill'],
        ],
        'platforms' => [
            'salla' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'سلة', 'label_en' => 'Salla'],
            'zid' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'زد', 'label_en' => 'Zid'],
            'shopify' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'Shopify', 'label_en' => 'Shopify'],
            'woocommerce' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'WooCommerce', 'label_en' => 'WooCommerce'],
        ],
        'shipping' => [
            'aramex' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'أرامكس', 'label_en' => 'Aramex'],
            'smsa' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'SMSA', 'label_en' => 'SMSA'],
            'dhl' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'DHL', 'label_en' => 'DHL'],
            'fedex' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'FedEx', 'label_en' => 'FedEx'],
            'free_shipping_threshold' => ['type' => 'float', 'default' => 200, 'label_ar' => 'حد الشحن المجاني', 'label_en' => 'Free Shipping Threshold'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // 🤖 AI - الذكاء الاصطناعي
    // ══════════════════════════════════════════════════════════════════════
    'ai' => [
        'general' => [
            'enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل الذكاء الاصطناعي', 'label_en' => 'Enable AI'],
            'provider' => ['type' => 'string', 'default' => 'openai', 'label_ar' => 'مزود الخدمة', 'label_en' => 'Provider', 'options' => ['openai', 'anthropic', 'gemini', 'local']],
            'model' => ['type' => 'string', 'default' => 'gpt-4o', 'label_ar' => 'النموذج', 'label_en' => 'Model'],
            'api_key' => ['type' => 'string', 'default' => null, 'label_ar' => 'مفتاح API', 'label_en' => 'API Key'],
            'monthly_budget' => ['type' => 'float', 'default' => 100, 'label_ar' => 'الميزانية الشهرية ($)', 'label_en' => 'Monthly Budget ($)'],
            'data_privacy' => ['type' => 'string', 'default' => 'anonymized', 'label_ar' => 'خصوصية البيانات', 'label_en' => 'Data Privacy', 'options' => ['full', 'anonymized', 'none']],
        ],
        'agents' => [
            'ai_accountant' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'المحاسب الذكي', 'label_en' => 'AI Accountant'],
            'ai_cfo' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'المدير المالي الذكي', 'label_en' => 'AI CFO'],
            'ai_sales' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'مساعد المبيعات', 'label_en' => 'AI Sales'],
            'ai_hr' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'مساعد HR', 'label_en' => 'AI HR'],
            'ai_warehouse' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'مساعد المستودع', 'label_en' => 'AI Warehouse'],
            'ai_auditor' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'المدقق الذكي', 'label_en' => 'AI Auditor'],
        ],
        'features' => [
            'anomaly_detection' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'كشف الشذوذ', 'label_en' => 'Anomaly Detection'],
            'forecasting' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'التنبؤ', 'label_en' => 'Forecasting'],
            'ocr_processing' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'معالجة OCR', 'label_en' => 'OCR Processing'],
            'recommendation_engine' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'محرك التوصيات', 'label_en' => 'Recommendation Engine'],
            'natural_language_query' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'استعلام بلغة طبيعية', 'label_en' => 'Natural Language Query'],
            'auto_categorization' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تصنيف تلقائي', 'label_en' => 'Auto Categorization'],
        ],
    ],

    // ══════════════════════════════════════════════════════════════════════
    // ⚙️ SYSTEM / GENERAL SETTINGS - إعدادات النظام
    // ══════════════════════════════════════════════════════════════════════
    'system' => [
        'company' => [
            'name' => ['type' => 'string', 'default' => null, 'label_ar' => 'اسم الشركة', 'label_en' => 'Company Name'],
            'name_ar' => ['type' => 'string', 'default' => null, 'label_ar' => 'اسم الشركة (عربي)', 'label_en' => 'Company Name (Arabic)'],
            'vat_number' => ['type' => 'string', 'default' => null, 'label_ar' => 'الرقم الضريبي', 'label_en' => 'VAT Number'],
            'cr_number' => ['type' => 'string', 'default' => null, 'label_ar' => 'السجل التجاري', 'label_en' => 'CR Number'],
            'address' => ['type' => 'string', 'default' => null, 'label_ar' => 'العنوان', 'label_en' => 'Address'],
            'city' => ['type' => 'string', 'default' => 'Riyadh', 'label_ar' => 'المدينة', 'label_en' => 'City'],
            'country' => ['type' => 'string', 'default' => 'SA', 'label_ar' => 'الدولة', 'label_en' => 'Country'],
            'phone' => ['type' => 'string', 'default' => null, 'label_ar' => 'الهاتف', 'label_en' => 'Phone'],
            'email' => ['type' => 'string', 'default' => null, 'label_ar' => 'البريد الإلكتروني', 'label_en' => 'Email'],
            'website' => ['type' => 'string', 'default' => null, 'label_ar' => 'الموقع', 'label_en' => 'Website'],
            'logo' => ['type' => 'string', 'default' => null, 'label_ar' => 'الشعار', 'label_en' => 'Logo'],
        ],
        'localization' => [
            'default_language' => ['type' => 'string', 'default' => 'ar', 'label_ar' => 'اللغة الافتراضية', 'label_en' => 'Default Language', 'options' => ['ar', 'en']],
            'timezone' => ['type' => 'string', 'default' => 'Asia/Riyadh', 'label_ar' => 'المنطقة الزمنية', 'label_en' => 'Timezone'],
            'date_format' => ['type' => 'string', 'default' => 'dd/MM/yyyy', 'label_ar' => 'صيغة التاريخ', 'label_en' => 'Date Format', 'options' => ['dd/MM/yyyy', 'yyyy-MM-dd', 'MM/dd/yyyy']],
            'number_format' => ['type' => 'string', 'default' => '1,234.56', 'label_ar' => 'صيغة الأرقام', 'label_en' => 'Number Format', 'options' => ['1,234.56', '1.234,56', '1 234.56']],
            'hijri_calendar' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'التقويم الهجري', 'label_en' => 'Hijri Calendar'],
            'weekend_days' => ['type' => 'json', 'default' => '["friday","saturday"]', 'label_ar' => 'أيام العطلة', 'label_en' => 'Weekend Days'],
        ],
        'notifications' => [
            'email_notifications' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إشعارات البريد', 'label_en' => 'Email Notifications'],
            'sms_notifications' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'إشعارات SMS', 'label_en' => 'SMS Notifications'],
            'push_notifications' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إشعارات الدفع', 'label_en' => 'Push Notifications'],
            'whatsapp_notifications' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'إشعارات واتساب', 'label_en' => 'WhatsApp Notifications'],
            'telegram_notifications' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'إشعارات تيليجرام', 'label_en' => 'Telegram Notifications'],
        ],
        'security' => [
            'two_factor_auth' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'المصادقة الثنائية', 'label_en' => 'Two Factor Auth'],
            'password_min_length' => ['type' => 'integer', 'default' => 8, 'label_ar' => 'أقل طول كلمة مرور', 'label_en' => 'Min Password Length'],
            'password_expiry_days' => ['type' => 'integer', 'default' => 90, 'label_ar' => 'انتهاء كلمة المرور (أيام)', 'label_en' => 'Password Expiry (days)'],
            'session_timeout_minutes' => ['type' => 'integer', 'default' => 60, 'label_ar' => 'انتهاء الجلسة (دقائق)', 'label_en' => 'Session Timeout (min)'],
            'ip_restriction' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'تقييد IP', 'label_en' => 'IP Restriction'],
            'audit_log_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'سجل المراجعة', 'label_en' => 'Audit Log'],
            'data_encryption' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تشفير البيانات', 'label_en' => 'Data Encryption'],
            'pdpl_compliance' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'الامتثال لنظام حماية البيانات', 'label_en' => 'PDPL Compliance'],
        ],
        'backup' => [
            'auto_backup' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'نسخ احتياطي تلقائي', 'label_en' => 'Auto Backup'],
            'backup_frequency' => ['type' => 'string', 'default' => 'daily', 'label_ar' => 'تكرار النسخ', 'label_en' => 'Backup Frequency', 'options' => ['hourly', 'daily', 'weekly']],
            'retention_days' => ['type' => 'integer', 'default' => 30, 'label_ar' => 'الاحتفاظ (أيام)', 'label_en' => 'Retention (days)'],
            'storage_provider' => ['type' => 'string', 'default' => 's3', 'label_ar' => 'مزود التخزين', 'label_en' => 'Storage Provider', 'options' => ['s3', 'gcs', 'azure', 'local']],
        ],
    ],



    // ══════════════════════════════════════════════════════════════════════
    // 🇸🇦 LABOR MARKET - سوق العمل السعودي
    // ══════════════════════════════════════════════════════════════════════
    'labor_market' => [
        'nitaqat' => [
            'entity_number' => ['type' => 'string', 'default' => null, 'label_ar' => 'رقم المنشأة', 'label_en' => 'Entity Number'],
            'activity_code' => ['type' => 'string', 'default' => null, 'label_ar' => 'رمز النشاط', 'label_en' => 'Activity Code'],
            'target_band' => ['type' => 'string', 'default' => 'green_mid', 'label_ar' => 'النطاق المستهدف', 'label_en' => 'Target Band', 'options' => ['green_low', 'green_mid', 'green_high', 'platinum']],
            'auto_calculate' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'حساب تلقائي للنسبة', 'label_en' => 'Auto Calculate'],
            'alert_threshold' => ['type' => 'float', 'default' => 2, 'label_ar' => 'تنبيه قبل هبوط النطاق %', 'label_en' => 'Alert Threshold %'],
            'include_part_time' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'احتساب الدوام الجزئي', 'label_en' => 'Include Part-time'],
            'part_time_weight' => ['type' => 'float', 'default' => 0.5, 'label_ar' => 'وزن الدوام الجزئي', 'label_en' => 'Part-time Weight'],
            'remote_work_saudis' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'احتساب العمل عن بعد', 'label_en' => 'Count Remote Workers'],
        ],
        'gosi' => [
            'auto_register' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تسجيل تلقائي للموظفين', 'label_en' => 'Auto Register Employees'],
            'saudi_employee_rate' => ['type' => 'float', 'default' => 9.75, 'label_ar' => 'نسبة الموظف السعودي %', 'label_en' => 'Saudi Employee Rate %'],
            'saudi_employer_rate' => ['type' => 'float', 'default' => 11.75, 'label_ar' => 'نسبة صاحب العمل (سعودي) %', 'label_en' => 'Saudi Employer Rate %'],
            'occupational_hazards_rate' => ['type' => 'float', 'default' => 2.0, 'label_ar' => 'نسبة أخطار مهنية %', 'label_en' => 'Occupational Hazards Rate %'],
            'saned_rate' => ['type' => 'float', 'default' => 1.5, 'label_ar' => 'نسبة ساند %', 'label_en' => 'SANED Rate %'],
            'max_subscribable_salary' => ['type' => 'float', 'default' => 45000, 'label_ar' => 'الحد الأقصى للراتب الخاضع', 'label_en' => 'Max Subscribable Salary'],
            'min_subscribable_salary' => ['type' => 'float', 'default' => 1500, 'label_ar' => 'الحد الأدنى للراتب الخاضع', 'label_en' => 'Min Subscribable Salary'],
            'include_housing' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إدراج بدل السكن', 'label_en' => 'Include Housing Allowance'],
            'auto_monthly_submission' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'إرسال شهري تلقائي', 'label_en' => 'Auto Monthly Submit'],
            'submission_reminder_day' => ['type' => 'integer', 'default' => 10, 'label_ar' => 'يوم التذكير بالإرسال', 'label_en' => 'Submission Reminder Day'],
            'auto_journal_entry' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'قيد محاسبي تلقائي', 'label_en' => 'Auto Journal Entry'],
            'expense_account' => ['type' => 'string', 'default' => null, 'label_ar' => 'حساب مصروف التأمينات', 'label_en' => 'GOSI Expense Account'],
            'liability_account' => ['type' => 'string', 'default' => null, 'label_ar' => 'حساب التأمينات المستحقة', 'label_en' => 'GOSI Liability Account'],
        ],
        'wps' => [
            'enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل حماية الأجور', 'label_en' => 'Enable WPS'],
            'mol_establishment_id' => ['type' => 'string', 'default' => null, 'label_ar' => 'رقم المنشأة في وزارة العمل', 'label_en' => 'MOL Establishment ID'],
            'default_bank_code' => ['type' => 'string', 'default' => null, 'label_ar' => 'رمز البنك الافتراضي', 'label_en' => 'Default Bank Code'],
            'auto_generate' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'توليد تلقائي مع الرواتب', 'label_en' => 'Auto Generate with Payroll'],
            'file_format' => ['type' => 'string', 'default' => 'mudad', 'label_ar' => 'صيغة الملف', 'label_en' => 'File Format', 'options' => ['mudad', 'sif_legacy', 'bank_specific']],
            'deadline_day' => ['type' => 'integer', 'default' => 3, 'label_ar' => 'الموعد النهائي (يوم من الشهر التالي)', 'label_en' => 'Deadline Day'],
            'include_allowances' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إدراج البدلات', 'label_en' => 'Include Allowances'],
            'alert_non_compliant' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تنبيه عدم الامتثال', 'label_en' => 'Alert Non-Compliant'],
        ],
        'qiwa' => [
            'enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل ربط قوى', 'label_en' => 'Enable Qiwa'],
            'auto_sync_contracts' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'مزامنة تلقائية للعقود', 'label_en' => 'Auto Sync Contracts'],
            'default_contract_type' => ['type' => 'string', 'default' => 'indefinite', 'label_ar' => 'نوع العقد الافتراضي', 'label_en' => 'Default Contract Type', 'options' => ['definite', 'indefinite', 'part_time']],
            'default_working_hours' => ['type' => 'integer', 'default' => 48, 'label_ar' => 'ساعات العمل الأسبوعية', 'label_en' => 'Weekly Working Hours'],
            'default_probation_days' => ['type' => 'integer', 'default' => 90, 'label_ar' => 'فترة التجربة (أيام)', 'label_en' => 'Probation Days'],
            'default_notice_period' => ['type' => 'integer', 'default' => 60, 'label_ar' => 'فترة الإشعار (أيام)', 'label_en' => 'Notice Period (days)'],
            'alert_expiring_contracts' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تنبيه العقود المنتهية', 'label_en' => 'Alert Expiring Contracts'],
            'alert_days_before_expiry' => ['type' => 'integer', 'default' => 30, 'label_ar' => 'تنبيه قبل الانتهاء (أيام)', 'label_en' => 'Alert Days Before Expiry'],
            'require_employee_acceptance' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'يتطلب قبول الموظف', 'label_en' => 'Require Employee Acceptance'],
        ],
        'muqeem' => [
            'enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل ربط المقيم', 'label_en' => 'Enable Muqeem'],
            'auto_renewal_alert' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تنبيه التجديد التلقائي', 'label_en' => 'Auto Renewal Alert'],
            'alert_before_expiry_days' => ['type' => 'integer', 'default' => 60, 'label_ar' => 'تنبيه قبل الانتهاء (أيام)', 'label_en' => 'Alert Before Expiry (days)'],
            'iqama_renewal_fees' => ['type' => 'float', 'default' => 650, 'label_ar' => 'رسوم تجديد الإقامة', 'label_en' => 'Iqama Renewal Fees'],
            'exit_reentry_single_fees' => ['type' => 'float', 'default' => 200, 'label_ar' => 'رسوم خروج وعودة مفردة', 'label_en' => 'Single Exit-Reentry Fees'],
            'exit_reentry_multiple_fees' => ['type' => 'float', 'default' => 500, 'label_ar' => 'رسوم خروج وعودة متعددة', 'label_en' => 'Multiple Exit-Reentry Fees'],
            'final_exit_fees' => ['type' => 'float', 'default' => 0, 'label_ar' => 'رسوم خروج نهائي', 'label_en' => 'Final Exit Fees'],
            'transfer_fees' => ['type' => 'float', 'default' => 2000, 'label_ar' => 'رسوم نقل الكفالة', 'label_en' => 'Sponsorship Transfer Fees'],
            'track_dependents' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تتبع المرافقين', 'label_en' => 'Track Dependents'],
            'dependent_monthly_fee' => ['type' => 'float', 'default' => 400, 'label_ar' => 'رسوم المرافقين الشهرية', 'label_en' => 'Dependent Monthly Fee'],
        ],
        'mol_levies' => [
            'enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل المقابل المالي', 'label_en' => 'Enable MOL Levies'],
            'rate_per_worker' => ['type' => 'float', 'default' => 400, 'label_ar' => 'المقابل الشهري لكل عامل', 'label_en' => 'Monthly Rate Per Worker'],
            'exemption_ratio' => ['type' => 'float', 'default' => 1.0, 'label_ar' => 'نسبة الإعفاء (1:1)', 'label_en' => 'Exemption Ratio'],
            'auto_calculate' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'حساب تلقائي', 'label_en' => 'Auto Calculate'],
            'auto_journal_entry' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'قيد محاسبي تلقائي', 'label_en' => 'Auto Journal Entry'],
            'expense_account' => ['type' => 'string', 'default' => null, 'label_ar' => 'حساب مصروف المقابل المالي', 'label_en' => 'Levy Expense Account'],
        ],
        'hrdf' => [
            'enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل دعم هدف', 'label_en' => 'Enable HRDF Support'],
            'tamheer_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'برنامج تمهير', 'label_en' => 'Tamheer Program'],
            'tamheer_monthly_support' => ['type' => 'float', 'default' => 3000, 'label_ar' => 'دعم تمهير الشهري', 'label_en' => 'Tamheer Monthly Support'],
            'tawteen_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'برنامج توطين', 'label_en' => 'Tawteen Program'],
            'salary_support_enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'دعم الرواتب', 'label_en' => 'Salary Support'],
            'salary_support_percentage' => ['type' => 'float', 'default' => 30, 'label_ar' => 'نسبة دعم الراتب %', 'label_en' => 'Salary Support %'],
            'salary_support_max' => ['type' => 'float', 'default' => 3000, 'label_ar' => 'أقصى دعم شهري', 'label_en' => 'Max Monthly Support'],
            'salary_support_duration_months' => ['type' => 'integer', 'default' => 24, 'label_ar' => 'مدة الدعم (أشهر)', 'label_en' => 'Support Duration (months)'],
            'auto_claim_submission' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'تقديم مطالبات تلقائي', 'label_en' => 'Auto Claim Submission'],
            'track_training_hours' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تتبع ساعات التدريب', 'label_en' => 'Track Training Hours'],
        ],
        'ajeer' => [
            'enabled' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'تفعيل أجير', 'label_en' => 'Enable Ajeer'],
            'allow_inbound' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'استعارة عمالة', 'label_en' => 'Allow Inbound Workers'],
            'allow_outbound' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إعارة عمالة', 'label_en' => 'Allow Outbound Workers'],
            'max_lending_days' => ['type' => 'integer', 'default' => 180, 'label_ar' => 'أقصى مدة إعارة (أيام)', 'label_en' => 'Max Lending Days'],
            'require_approval' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'يتطلب موافقة', 'label_en' => 'Require Approval'],
        ],
        'taqat' => [
            'enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل ربط طاقات', 'label_en' => 'Enable Taqat'],
            'auto_post_jobs' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'نشر تلقائي للوظائف', 'label_en' => 'Auto Post Jobs'],
            'default_posting_days' => ['type' => 'integer', 'default' => 30, 'label_ar' => 'مدة الإعلان (أيام)', 'label_en' => 'Posting Duration (days)'],
            'require_saudi_only' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'سعوديين فقط', 'label_en' => 'Saudi Only'],
            'jadarat_integration' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'ربط جدارات', 'label_en' => 'Jadarat Integration'],
        ],
        'mudad' => [
            'enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل مدد', 'label_en' => 'Enable Mudad'],
            'auto_check_compliance' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'فحص امتثال تلقائي', 'label_en' => 'Auto Compliance Check'],
            'compliance_threshold' => ['type' => 'float', 'default' => 80, 'label_ar' => 'حد الامتثال %', 'label_en' => 'Compliance Threshold %'],
            'alert_non_compliant' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تنبيه عدم الامتثال', 'label_en' => 'Alert Non-Compliant'],
            'grace_period_days' => ['type' => 'integer', 'default' => 3, 'label_ar' => 'فترة السماح (أيام)', 'label_en' => 'Grace Period (days)'],
        ],
        'saned' => [
            'enabled' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تفعيل ساند', 'label_en' => 'Enable SANED'],
            'employee_contribution_rate' => ['type' => 'float', 'default' => 0.75, 'label_ar' => 'نسبة اشتراك الموظف %', 'label_en' => 'Employee Rate %'],
            'employer_contribution_rate' => ['type' => 'float', 'default' => 0.75, 'label_ar' => 'نسبة اشتراك صاحب العمل %', 'label_en' => 'Employer Rate %'],
            'track_eligibility' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تتبع الأهلية', 'label_en' => 'Track Eligibility'],
            'min_contribution_months' => ['type' => 'integer', 'default' => 12, 'label_ar' => 'أقل أشهر اشتراك', 'label_en' => 'Min Contribution Months'],
        ],
        'musaned' => [
            'enabled' => ['type' => 'boolean', 'default' => false, 'label_ar' => 'تفعيل مساند', 'label_en' => 'Enable Musaned'],
            'track_contracts' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'تتبع العقود', 'label_en' => 'Track Contracts'],
            'trial_period_days' => ['type' => 'integer', 'default' => 90, 'label_ar' => 'فترة التجربة (أيام)', 'label_en' => 'Trial Period (days)'],
            'insurance_required' => ['type' => 'boolean', 'default' => true, 'label_ar' => 'إلزام التأمين', 'label_en' => 'Insurance Required'],
        ],
    ],
    'data_import_export' => [
        'general' => [
            'max_file_size_mb' => ['type'=>'integer','default'=>50,'label_ar'=>'أقصى حجم للملف','label_en'=>'Max File Size MB'],
            'allowed_formats' => ['type'=>'json','default'=>'["csv","xlsx","xls","json","xml"]','label_ar'=>'الصيغ المسموحة','label_en'=>'Allowed Formats'],
            'duplicate_strategy' => ['type'=>'string','default'=>'update','label_ar'=>'معالجة التكرار','label_en'=>'Duplicate Strategy','options'=>['skip','update','create']],
            'validate_before_import' => ['type'=>'boolean','default'=>true,'label_ar'=>'التحقق قبل الاستيراد','label_en'=>'Validate Before Import'],
            'batch_size' => ['type'=>'integer','default'=>500,'label_ar'=>'حجم الدفعة','label_en'=>'Batch Size'],
            'keep_history' => ['type'=>'boolean','default'=>true,'label_ar'=>'حفظ سجل العمليات','label_en'=>'Keep History'],
        ],
    ],
    'workflow' => [
        'general' => [
            'enabled' => ['type'=>'boolean','default'=>true,'label_ar'=>'تفعيل سير العمل','label_en'=>'Enable Workflows'],
            'run_async' => ['type'=>'boolean','default'=>true,'label_ar'=>'تشغيل غير متزامن','label_en'=>'Run Asynchronously'],
            'max_retries' => ['type'=>'integer','default'=>3,'label_ar'=>'أقصى محاولات','label_en'=>'Max Retries'],
            'notify_on_failure' => ['type'=>'boolean','default'=>true,'label_ar'=>'تنبيه عند الفشل','label_en'=>'Notify on Failure'],
        ],
    ],
    'approvals' => [
        'general' => [
            'enabled' => ['type'=>'boolean','default'=>true,'label_ar'=>'تفعيل الموافقات','label_en'=>'Enable Approvals'],
            'reminder_after_hours' => ['type'=>'integer','default'=>24,'label_ar'=>'التذكير بعد ساعات','label_en'=>'Reminder After Hours'],
            'escalate_after_hours' => ['type'=>'integer','default'=>72,'label_ar'=>'التصعيد بعد ساعات','label_en'=>'Escalate After Hours'],
            'allow_delegation' => ['type'=>'boolean','default'=>true,'label_ar'=>'السماح بالتفويض','label_en'=>'Allow Delegation'],
        ],
    ],
    'integrations' => [
        'general' => [
            'enabled' => ['type'=>'boolean','default'=>true,'label_ar'=>'تفعيل التكاملات','label_en'=>'Enable Integrations'],
            'sync_frequency_minutes' => ['type'=>'integer','default'=>15,'label_ar'=>'تكرار المزامنة بالدقائق','label_en'=>'Sync Frequency Minutes'],
            'timeout_seconds' => ['type'=>'integer','default'=>30,'label_ar'=>'مهلة الاتصال بالثواني','label_en'=>'Timeout Seconds'],
            'log_payloads' => ['type'=>'boolean','default'=>false,'label_ar'=>'تسجيل البيانات المرسلة','label_en'=>'Log Payloads'],
        ],
    ],
    'webhooks' => [
        'general' => [
            'enabled' => ['type'=>'boolean','default'=>true,'label_ar'=>'تفعيل Webhooks','label_en'=>'Enable Webhooks'],
            'max_retries' => ['type'=>'integer','default'=>5,'label_ar'=>'أقصى محاولات الإرسال','label_en'=>'Max Delivery Retries'],
            'retry_backoff_seconds' => ['type'=>'integer','default'=>60,'label_ar'=>'فاصل إعادة المحاولة','label_en'=>'Retry Backoff Seconds'],
        ],
    ],
    'scheduled_reports' => [
        'general' => [
            'enabled' => ['type'=>'boolean','default'=>true,'label_ar'=>'تفعيل التقارير المجدولة','label_en'=>'Enable Scheduled Reports'],
            'default_format' => ['type'=>'string','default'=>'pdf','label_ar'=>'الصيغة الافتراضية','label_en'=>'Default Format','options'=>['pdf','xlsx','csv']],
            'max_recipients' => ['type'=>'integer','default'=>20,'label_ar'=>'أقصى عدد مستلمين','label_en'=>'Max Recipients'],
        ],
    ],
    'custom_fields' => [
        'general' => [
            'enabled' => ['type'=>'boolean','default'=>true,'label_ar'=>'تفعيل الحقول المخصصة','label_en'=>'Enable Custom Fields'],
            'max_per_entity' => ['type'=>'integer','default'=>50,'label_ar'=>'أقصى حقل لكل كيان','label_en'=>'Max Fields Per Entity'],
            'allow_formula_fields' => ['type'=>'boolean','default'=>true,'label_ar'=>'السماح بحقول المعادلات','label_en'=>'Allow Formula Fields'],
        ],
    ],
    'notifications' => [
        'general' => [
            'in_app_enabled' => ['type'=>'boolean','default'=>true,'label_ar'=>'إشعارات داخل النظام','label_en'=>'In-App Notifications'],
            'email_enabled' => ['type'=>'boolean','default'=>true,'label_ar'=>'إشعارات البريد','label_en'=>'Email Notifications'],
            'sms_enabled' => ['type'=>'boolean','default'=>false,'label_ar'=>'إشعارات SMS','label_en'=>'SMS Notifications'],
            'whatsapp_enabled' => ['type'=>'boolean','default'=>false,'label_ar'=>'إشعارات واتساب','label_en'=>'WhatsApp Notifications'],
        ],
    ],
    'audit' => [
        'general' => [
            'enabled' => ['type'=>'boolean','default'=>true,'label_ar'=>'تفعيل سجل التدقيق','label_en'=>'Enable Audit Trail'],
            'retention_days' => ['type'=>'integer','default'=>2555,'label_ar'=>'مدة الاحتفاظ بالأيام','label_en'=>'Retention Days'],
            'track_reads' => ['type'=>'boolean','default'=>false,'label_ar'=>'تسجيل عمليات القراءة','label_en'=>'Track Reads'],
            'track_exports' => ['type'=>'boolean','default'=>true,'label_ar'=>'تسجيل التصدير','label_en'=>'Track Exports'],
        ],
    ],
    'additional_modules' => [
        'general' => [
            'enabled' => ['type'=>'boolean','default'=>true,'label_ar'=>'تفعيل الموديولات الإضافية','label_en'=>'Enable Additional Modules'],
            'default_record_status' => ['type'=>'string','default'=>'draft','label_ar'=>'الحالة الافتراضية للسجلات','label_en'=>'Default Record Status'],
            'retention_days' => ['type'=>'integer','default'=>2555,'label_ar'=>'مدة الاحتفاظ بالسجلات','label_en'=>'Record Retention Days'],
        ],
    ],
,
    'ai_module_defaults' => [
        'general' => [
            'enabled' => ['type'=>'boolean','default'=>true,'label_ar'=>'تفعيل AI في الموديولات','label_en'=>'Enable AI Across Modules'],
            'auto_insights' => ['type'=>'boolean','default'=>true,'label_ar'=>'رؤى تلقائية','label_en'=>'Automatic Insights'],
            'auto_anomaly_detection' => ['type'=>'boolean','default'=>true,'label_ar'=>'كشف الشذوذ تلقائياً','label_en'=>'Automatic Anomaly Detection'],
            'auto_forecasting' => ['type'=>'boolean','default'=>true,'label_ar'=>'التنبؤ التلقائي','label_en'=>'Automatic Forecasting'],
            'human_approval_for_actions' => ['type'=>'boolean','default'=>true,'label_ar'=>'موافقة بشرية قبل الإجراءات','label_en'=>'Human Approval Before Actions'],
            'store_prompts_and_outputs' => ['type'=>'boolean','default'=>false,'label_ar'=>'حفظ المدخلات والمخرجات','label_en'=>'Store Prompts and Outputs'],
        ],
    ],
];
