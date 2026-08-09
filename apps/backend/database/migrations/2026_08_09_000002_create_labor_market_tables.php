<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ══════════════════════════════════════════════════════════════
        // 1. NITAQAT - نطاقات (السعودة)
        // ══════════════════════════════════════════════════════════════
        Schema::create('nitaqat_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('entity_number', 20); // رقم المنشأة
            $table->string('activity_code', 10); // رمز النشاط
            $table->string('activity_name'); // اسم النشاط
            $table->string('size_category', 20); // فئة الحجم: صغير/متوسط/كبير/عملاق
            $table->string('current_band', 20); // النطاق الحالي: أحمر/أصفر/أخضر منخفض/أخضر متوسط/أخضر مرتفع/بلاتيني
            $table->string('target_band', 20)->nullable(); // النطاق المستهدف
            $table->integer('total_employees')->default(0);
            $table->integer('saudi_employees')->default(0);
            $table->integer('non_saudi_employees')->default(0);
            $table->decimal('saudization_percentage', 5, 2)->default(0);
            $table->decimal('required_percentage', 5, 2)->default(0);
            $table->decimal('green_low_threshold', 5, 2)->nullable();
            $table->decimal('green_mid_threshold', 5, 2)->nullable();
            $table->decimal('green_high_threshold', 5, 2)->nullable();
            $table->decimal('platinum_threshold', 5, 2)->nullable();
            $table->integer('saudis_needed_for_green')->default(0);
            $table->integer('max_non_saudis_allowed')->default(0);
            $table->date('last_sync_date')->nullable();
            $table->json('historical_bands')->nullable(); // تاريخ تغيرات النطاق
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'entity_number']);
        });

        Schema::create('nitaqat_simulations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('scenario_name');
            $table->integer('hire_saudis')->default(0);
            $table->integer('terminate_non_saudis')->default(0);
            $table->decimal('projected_percentage', 5, 2);
            $table->string('projected_band', 20);
            $table->json('details')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════════════
        // 2. GOSI - التأمينات الاجتماعية
        // ══════════════════════════════════════════════════════════════
        Schema::create('gosi_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('employee_id');
            $table->string('gosi_number', 20)->nullable(); // رقم الاشتراك
            $table->string('subscriber_type', 20); // saudi, non_saudi
            $table->date('subscription_start_date');
            $table->date('subscription_end_date')->nullable();
            $table->string('status', 20)->default('active'); // active, suspended, terminated
            $table->decimal('basic_salary', 12, 2); // الراتب الأساسي الخاضع
            $table->decimal('housing_allowance', 12, 2)->default(0);
            $table->decimal('total_subscribable_salary', 12, 2); // الراتب الخاضع للاشتراك
            $table->decimal('employee_share', 12, 2)->default(0); // حصة الموظف
            $table->decimal('employer_share', 12, 2)->default(0); // حصة صاحب العمل
            $table->decimal('occupational_hazards', 12, 2)->default(0); // أخطار مهنية
            $table->decimal('saned_contribution', 12, 2)->default(0); // ساند (التعطل عن العمل)
            $table->boolean('is_saned_eligible')->default(false);
            $table->json('salary_history')->nullable(); // تاريخ الرواتب
            $table->date('last_payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
            $table->index('gosi_number');
        });

        Schema::create('gosi_monthly_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->integer('year');
            $table->integer('month');
            $table->string('status', 20)->default('draft'); // draft, submitted, paid, rejected
            $table->integer('total_subscribers')->default(0);
            $table->integer('saudi_subscribers')->default(0);
            $table->integer('non_saudi_subscribers')->default(0);
            $table->decimal('total_employee_share', 14, 2)->default(0);
            $table->decimal('total_employer_share', 14, 2)->default(0);
            $table->decimal('total_occupational_hazards', 14, 2)->default(0);
            $table->decimal('total_saned', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->decimal('penalties', 12, 2)->default(0); // غرامات التأخير
            $table->string('payment_reference')->nullable();
            $table->date('submission_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->json('submission_details')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'year', 'month']);
        });

        Schema::create('gosi_injury_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('employee_id');
            $table->date('injury_date');
            $table->string('injury_type', 50); // work_injury, occupational_disease, commute_injury
            $table->text('description');
            $table->string('severity', 20); // minor, moderate, severe, fatal
            $table->string('body_part')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_reported_to_gosi')->default(false);
            $table->string('gosi_claim_number')->nullable();
            $table->string('claim_status', 20)->nullable(); // pending, approved, rejected
            $table->integer('sick_leave_days')->default(0);
            $table->decimal('medical_expenses', 12, 2)->default(0);
            $table->decimal('compensation_amount', 12, 2)->default(0);
            $table->json('documents')->nullable();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════════════
        // 3. WPS - حماية الأجور
        // ══════════════════════════════════════════════════════════════
        Schema::create('wps_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('payroll_run_id')->nullable();
            $table->integer('year');
            $table->integer('month');
            $table->string('file_type', 20)->default('SIF'); // SIF = Salary Information File
            $table->string('status', 20)->default('draft'); // draft, generated, submitted, accepted, rejected
            $table->string('bank_code', 10); // رمز البنك
            $table->string('employer_mol_id', 20); // رقم المنشأة في وزارة العمل
            $table->integer('total_records')->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->string('file_path')->nullable();
            $table->string('file_hash')->nullable();
            $table->string('reference_number')->nullable(); // رقم المرجع من مدد
            $table->date('submission_date')->nullable();
            $table->json('rejection_reasons')->nullable();
            $table->json('employee_details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'year', 'month']);
        });

        Schema::create('wps_file_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wps_file_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('employee_id_number', 20); // رقم الهوية/الإقامة
            $table->string('employee_name');
            $table->string('bank_code', 10);
            $table->string('iban', 34);
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('housing_allowance', 12, 2)->default(0);
            $table->decimal('other_allowances', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2);
            $table->string('payment_type', 20)->default('bank_transfer'); // bank_transfer, cash, cheque
            $table->integer('working_days')->default(30);
            $table->integer('absent_days')->default(0);
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('wps_file_id')->references('id')->on('wps_files')->cascadeOnDelete();
        });

        // ══════════════════════════════════════════════════════════════
        // 4. MUQEEM - المقيم (إدارة المقيمين)
        // ══════════════════════════════════════════════════════════════
        Schema::create('muqeem_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('employee_id');
            $table->string('iqama_number', 20); // رقم الإقامة
            $table->string('border_number', 20)->nullable(); // رقم الحدود
            $table->string('passport_number', 20)->nullable();
            $table->string('nationality', 5);
            $table->string('sponsor_id', 20); // رقم الكفيل
            $table->date('iqama_issue_date');
            $table->date('iqama_expiry_date');
            $table->string('iqama_status', 20)->default('valid'); // valid, expired, cancelled, transferred
            $table->string('occupation_code', 10)->nullable(); // رمز المهنة
            $table->string('occupation_name')->nullable();
            $table->date('entry_date')->nullable(); // تاريخ الدخول
            $table->string('visa_type', 20)->nullable(); // work, visit, transit
            $table->integer('dependents_count')->default(0);
            $table->decimal('fees_paid', 10, 2)->default(0);
            $table->date('last_exit_reentry')->nullable(); // آخر تأشيرة خروج وعودة
            $table->boolean('final_exit_issued')->default(false);
            $table->json('travel_history')->nullable();
            $table->json('violation_flags')->nullable();
            $table->date('last_sync_date')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
            $table->index('iqama_number');
        });

        Schema::create('muqeem_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('muqeem_record_id');
            $table->string('transaction_type', 30); // renewal, exit_reentry, final_exit, transfer, occupation_change, dependent_add
            $table->string('status', 20)->default('pending'); // pending, processing, completed, rejected
            $table->date('request_date');
            $table->date('completion_date')->nullable();
            $table->decimal('fees', 10, 2)->default(0);
            $table->string('reference_number')->nullable();
            $table->json('details')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamps();

            $table->foreign('muqeem_record_id')->references('id')->on('muqeem_records')->cascadeOnDelete();
        });

        // ══════════════════════════════════════════════════════════════
        // 5. QIWA - قوى (العقود الإلكترونية)
        // ══════════════════════════════════════════════════════════════
        Schema::create('qiwa_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('employee_id');
            $table->string('qiwa_contract_id')->nullable(); // معرف العقد في قوى
            $table->string('contract_type', 20); // definite, indefinite, part_time, seasonal, temporary
            $table->string('status', 20)->default('draft'); // draft, pending_employee, active, expired, terminated
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('duration_months')->nullable();
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('housing_allowance', 12, 2)->default(0);
            $table->decimal('transportation_allowance', 12, 2)->default(0);
            $table->decimal('total_salary', 12, 2);
            $table->string('job_title');
            $table->string('job_title_ar');
            $table->string('work_location')->nullable();
            $table->integer('working_hours_per_week')->default(48);
            $table->integer('annual_leave_days')->default(21);
            $table->integer('probation_period_days')->default(90);
            $table->integer('notice_period_days')->default(60);
            $table->boolean('is_remote')->default(false);
            $table->boolean('employee_accepted')->default(false);
            $table->date('employee_accepted_date')->nullable();
            $table->boolean('employer_signed')->default(false);
            $table->date('employer_signed_date')->nullable();
            $table->date('submission_date')->nullable();
            $table->string('termination_reason')->nullable();
            $table->text('additional_terms')->nullable();
            $table->json('benefits')->nullable(); // مزايا إضافية
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
            $table->index('qiwa_contract_id');
        });

        Schema::create('qiwa_contract_amendments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('qiwa_contract_id');
            $table->string('amendment_type', 30); // salary_change, title_change, location_change, renewal
            $table->string('status', 20)->default('pending');
            $table->json('old_values');
            $table->json('new_values');
            $table->string('reason')->nullable();
            $table->date('effective_date');
            $table->boolean('employee_accepted')->default(false);
            $table->timestamps();

            $table->foreign('qiwa_contract_id')->references('id')->on('qiwa_contracts')->cascadeOnDelete();
        });

        // ══════════════════════════════════════════════════════════════
        // 6. AJEER - أجير (الإعارة المؤقتة)
        // ══════════════════════════════════════════════════════════════
        Schema::create('ajeer_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('contract_type', 20); // lending, temporary, seasonal, event
            $table->string('direction', 10); // inbound (مستعار), outbound (معار)
            $table->unsignedBigInteger('employee_id')->nullable(); // for outbound
            $table->string('worker_name')->nullable(); // for inbound
            $table->string('worker_id_number', 20);
            $table->string('worker_nationality', 5)->nullable();
            $table->string('lending_entity_name')->nullable(); // المنشأة المُعيرة
            $table->string('lending_entity_number', 20)->nullable();
            $table->string('borrowing_entity_name')->nullable(); // المنشأة المُستعيرة
            $table->string('borrowing_entity_number', 20)->nullable();
            $table->string('occupation');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('agreed_amount', 12, 2)->default(0);
            $table->string('payment_frequency', 20)->default('monthly');
            $table->string('status', 20)->default('draft'); // draft, pending, active, completed, cancelled
            $table->string('ajeer_reference')->nullable(); // رقم العقد في أجير
            $table->text('terms')->nullable();
            $table->json('documents')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // ══════════════════════════════════════════════════════════════
        // 7. MUDAD - مدد (نظام حماية الأجور المحدث)
        // ══════════════════════════════════════════════════════════════
        Schema::create('mudad_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('payroll_run_id')->nullable();
            $table->integer('year');
            $table->integer('month');
            $table->string('status', 20)->default('draft'); // draft, validated, submitted, compliant, non_compliant
            $table->string('establishment_number', 20);
            $table->integer('total_employees')->default(0);
            $table->integer('paid_on_time')->default(0);
            $table->integer('paid_late')->default(0);
            $table->integer('unpaid')->default(0);
            $table->decimal('total_wages', 14, 2)->default(0);
            $table->decimal('total_paid', 14, 2)->default(0);
            $table->decimal('compliance_percentage', 5, 2)->default(0);
            $table->string('compliance_status', 20)->nullable(); // green, yellow, red
            $table->date('payment_deadline');
            $table->date('submission_date')->nullable();
            $table->string('reference_number')->nullable();
            $table->json('discrepancies')->nullable(); // الفروقات
            $table->json('warnings')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'year', 'month']);
        });

        // ══════════════════════════════════════════════════════════════
        // 8. HRDF/HADAF - هدف (صندوق تنمية الموارد البشرية)
        // ══════════════════════════════════════════════════════════════
        Schema::create('hrdf_programs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('program_type', 30); // tamheer, tawteen, support_salary, training, doroob
            $table->string('program_name');
            $table->string('program_name_ar');
            $table->string('status', 20)->default('active');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('max_beneficiaries')->default(0);
            $table->integer('current_beneficiaries')->default(0);
            $table->decimal('support_amount_per_month', 10, 2)->default(0);
            $table->integer('support_duration_months')->default(0);
            $table->decimal('total_claimed', 14, 2)->default(0);
            $table->decimal('total_received', 14, 2)->default(0);
            $table->string('hrdf_contract_number')->nullable();
            $table->json('eligibility_criteria')->nullable();
            $table->json('documents')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'program_type']);
        });

        Schema::create('hrdf_beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hrdf_program_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('status', 20)->default('active'); // active, completed, dropped, suspended
            $table->date('enrollment_date');
            $table->date('end_date')->nullable();
            $table->decimal('monthly_support', 10, 2)->default(0);
            $table->integer('months_supported')->default(0);
            $table->decimal('total_support_received', 12, 2)->default(0);
            $table->string('national_id', 20);
            $table->json('monthly_claims')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('hrdf_program_id')->references('id')->on('hrdf_programs')->cascadeOnDelete();
            $table->index('employee_id');
        });

        // ══════════════════════════════════════════════════════════════
        // 9. LABOR OFFICE - مكتب العمل (التأشيرات والمخالفات)
        // ══════════════════════════════════════════════════════════════
        Schema::create('work_permits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('permit_type', 30); // new, renewal, transfer, occupation_change
            $table->string('status', 20)->default('pending'); // pending, approved, rejected, expired
            $table->string('visa_number', 20)->nullable();
            $table->string('occupation_code', 10)->nullable();
            $table->string('occupation_name')->nullable();
            $table->string('nationality', 5)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('fees_paid', 10, 2)->default(0);
            $table->decimal('monthly_levy', 10, 2)->default(0); // المقابل المالي
            $table->string('mol_reference')->nullable(); // رقم المرجع
            $table->text('rejection_reason')->nullable();
            $table->json('documents')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('labor_violations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('violation_number', 30)->nullable();
            $table->string('violation_type', 50); // saudization, wps, contract, safety, housing, etc.
            $table->string('severity', 20); // minor, moderate, major, critical
            $table->text('description');
            $table->date('violation_date');
            $table->date('due_date')->nullable(); // موعد التصحيح
            $table->string('status', 20)->default('open'); // open, corrected, appealed, paid, escalated
            $table->decimal('fine_amount', 12, 2)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->date('payment_date')->nullable();
            $table->text('corrective_action')->nullable();
            $table->string('appeal_status', 20)->nullable();
            $table->text('appeal_notes')->nullable();
            $table->json('documents')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('mol_levies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->integer('year');
            $table->integer('month');
            $table->integer('non_saudi_count')->default(0);
            $table->integer('exempt_count')->default(0); // المعفيين
            $table->integer('billable_count')->default(0);
            $table->decimal('rate_per_worker', 8, 2)->default(400); // المقابل المالي شهري
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->string('status', 20)->default('pending'); // pending, paid, overdue
            $table->date('due_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('payment_reference')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'year', 'month']);
        });

        // ══════════════════════════════════════════════════════════════
        // 10. TAQAT/JADARAT - طاقات/جدارات (بوابة التوظيف الوطنية)
        // ══════════════════════════════════════════════════════════════
        Schema::create('taqat_job_postings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('job_opening_id')->nullable();
            $table->string('taqat_posting_id')->nullable(); // معرف الإعلان في طاقات
            $table->string('job_title');
            $table->string('job_title_ar');
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->string('city');
            $table->string('job_type', 20); // full_time, part_time, remote, contract
            $table->decimal('salary_from', 10, 2)->nullable();
            $table->decimal('salary_to', 10, 2)->nullable();
            $table->string('experience_level', 20)->nullable();
            $table->string('education_level', 30)->nullable();
            $table->string('gender_preference', 10)->nullable(); // male, female, both
            $table->integer('positions_count')->default(1);
            $table->string('status', 20)->default('draft'); // draft, published, closed, filled
            $table->date('publish_date')->nullable();
            $table->date('closing_date')->nullable();
            $table->integer('applications_count')->default(0);
            $table->json('skills_required')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // ══════════════════════════════════════════════════════════════
        // 11. MUSANED - مساند (العمالة المنزلية)
        // ══════════════════════════════════════════════════════════════
        Schema::create('musaned_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('contract_number')->nullable();
            $table->string('worker_name');
            $table->string('worker_nationality', 5);
            $table->string('worker_id_number', 20)->nullable();
            $table->string('worker_gender', 10);
            $table->string('occupation'); // خادمة/سائق/طباخ/حارس/مربية
            $table->string('recruitment_office')->nullable();
            $table->string('status', 20)->default('pending'); // pending, active, trial, completed, terminated
            $table->date('contract_start');
            $table->date('contract_end')->nullable();
            $table->integer('contract_duration_years')->default(2);
            $table->decimal('monthly_salary', 10, 2);
            $table->date('trial_period_end')->nullable();
            $table->decimal('visa_fees', 10, 2)->default(0);
            $table->decimal('recruitment_fees', 10, 2)->default(0);
            $table->decimal('insurance_amount', 10, 2)->default(0);
            $table->json('documents')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // ══════════════════════════════════════════════════════════════
        // 12. SANED - ساند (التأمين ضد التعطل)
        // ══════════════════════════════════════════════════════════════
        Schema::create('saned_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('employee_id');
            $table->boolean('is_eligible')->default(false);
            $table->date('eligibility_start_date')->nullable();
            $table->integer('contribution_months')->default(0); // أشهر الاشتراك
            $table->decimal('last_3_months_avg_salary', 12, 2)->default(0);
            $table->string('termination_type', 30)->nullable(); // layoff, contract_end, mutual
            $table->date('termination_date')->nullable();
            $table->boolean('claim_filed')->default(false);
            $table->date('claim_date')->nullable();
            $table->string('claim_status', 20)->nullable(); // pending, approved, rejected, exhausted
            $table->integer('benefit_months')->default(0); // عدد أشهر الاستحقاق
            $table->decimal('monthly_benefit', 10, 2)->default(0);
            $table->json('payment_history')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saned_records');
        Schema::dropIfExists('musaned_contracts');
        Schema::dropIfExists('taqat_job_postings');
        Schema::dropIfExists('mol_levies');
        Schema::dropIfExists('labor_violations');
        Schema::dropIfExists('work_permits');
        Schema::dropIfExists('hrdf_beneficiaries');
        Schema::dropIfExists('hrdf_programs');
        Schema::dropIfExists('mudad_submissions');
        Schema::dropIfExists('ajeer_contracts');
        Schema::dropIfExists('qiwa_contract_amendments');
        Schema::dropIfExists('qiwa_contracts');
        Schema::dropIfExists('muqeem_transactions');
        Schema::dropIfExists('muqeem_records');
        Schema::dropIfExists('wps_file_records');
        Schema::dropIfExists('wps_files');
        Schema::dropIfExists('gosi_injury_reports');
        Schema::dropIfExists('gosi_monthly_submissions');
        Schema::dropIfExists('gosi_subscriptions');
        Schema::dropIfExists('nitaqat_simulations');
        Schema::dropIfExists('nitaqat_records');
    }
};
