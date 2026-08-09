<?php

namespace App\Observers;

use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use Illuminate\Validation\ValidationException;

/**
 * الحماية المركزية للفترات المحاسبية المقفلة.
 *
 * مسجّلة على JournalEntry في AppServiceProvider، فبتغطي كل مسارات الترحيل
 * في النظام دفعة واحدة: إصدار فواتير المبيعات، اعتماد فواتير الموردين،
 * سندات القبض والصرف وإلغاؤها، ترحيل الرواتب، قيود الإهلاك، والقيود اليدوية.
 * أي مسار ترحيل جديد بيتضاف مستقبلاً بيبقى محمي تلقائياً من غير أي كود إضافي.
 */
class JournalEntryObserver
{
    public function creating(JournalEntry $entry): void
    {
        // القيد المسودة مسموح في أي وقت — الممنوع هو الترحيل على فترة مقفلة
        if ($entry->status === 'posted') {
            $this->guard($entry->entry_date, 'إنشاء قيد مُرحّل');
        }
    }

    public function updating(JournalEntry $entry): void
    {
        // التاريخ الأصلي: لو القيد كان مُرحّل في فترة اتقفلت، ما ينفعش يتعدّل
        $originalDate = $entry->getOriginal('entry_date');

        if ($entry->getOriginal('status') === 'posted' && $originalDate) {
            $this->guard($originalDate, 'تعديل قيد مُرحّل');
        }

        // والتاريخ الجديد كمان لازم يكون في فترة مفتوحة
        if ($entry->status === 'posted') {
            $this->guard($entry->entry_date, 'ترحيل قيد');
        }
    }

    public function deleting(JournalEntry $entry): void
    {
        if ($entry->status === 'posted') {
            $this->guard($entry->entry_date, 'حذف قيد مُرحّل');
        }
    }

    private function guard(mixed $date, string $action): void
    {
        if (! $date) {
            return;
        }

        if (! AccountingPeriod::isDateClosed($date)) {
            return;
        }

        $formatted = $date instanceof \DateTimeInterface
            ? $date->format('Y-m-d')
            : (string) $date;

        throw ValidationException::withMessages([
            'entry_date' => [
                "الفترة المحاسبية بتاريخ {$formatted} مقفلة — مينفعش {$action} فيها. "
                .'لو محتاج تعدّل، افتح الفترة الأول (صلاحية admin) أو سجّل التسوية في فترة مفتوحة.',
            ],
        ]);
    }
}
