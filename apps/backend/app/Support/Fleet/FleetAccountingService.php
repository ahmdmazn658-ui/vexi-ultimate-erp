<?php

namespace App\Support\Fleet;

use App\Models\Fleet\FuelLog;
use App\Models\Fleet\MaintenanceRecord;
use App\Models\Fleet\Violation;
use App\Models\JournalEntry;
use App\Support\Accounting\DefaultAccounts;

/**
 * بيولّد القيد المحاسبي المُرحّل تلقائيًا لكل تكلفة أسطول (صيانة، وقود، مخالفة)
 * لحظة تسجيلها — بنفس مبدأ باقي الموديولات: دفتر الأستاذ هو مصدر الحقيقة
 * المالية الوحيد، فأي تكلفة تشغيلية بتتسجّل بتتقيّد آليًا من غير تدخل يدوي.
 *
 * الطرف الدائن افتراضيًا هو النقدية/البنك (بافتراض إن تكلفة الصيانة/الوقود
 * بتتدفع فورًا زي المصروفات النثرية)، إلا في حالة المخالفة المحمّلة على
 * السائق فبتتقيّد كذمة على السائق بدل مصروف على الشركة.
 */
class FleetAccountingService
{
    public function postMaintenance(MaintenanceRecord $record, ?int $userId = null): ?JournalEntry
    {
        if ((float) $record->cost <= 0) {
            return null;
        }

        $number = 'FLT-MNT-'.str_pad((string) $record->id, 6, '0', STR_PAD_LEFT);
        $vehicle = $record->vehicle;

        $entry = JournalEntry::create([
            'entry_number' => 'JE-'.$number,
            'entry_date' => $record->service_date,
            'project_id' => $vehicle?->project_id,
            'reference' => $number,
            'description' => "قيد صيانة مركبة {$vehicle?->plate_number} - {$number}",
            'status' => 'posted',
            'created_by' => $userId,
        ]);

        $entry->lines()->create([
            'account_id' => DefaultAccounts::vehicleMaintenanceExpense()->id,
            'debit' => $record->cost,
            'credit' => 0,
            'memo' => $record->vendor_name ? "صيانة - {$record->vendor_name}" : 'صيانة مركبة',
        ]);

        $entry->lines()->create([
            'account_id' => DefaultAccounts::cashAndBank()->id,
            'debit' => 0,
            'credit' => $record->cost,
            'memo' => "سداد صيانة مركبة {$vehicle?->plate_number}",
        ]);

        $record->update(['journal_entry_id' => $entry->id]);

        return $entry;
    }

    public function postFuel(FuelLog $log, ?int $userId = null): ?JournalEntry
    {
        if ((float) $log->cost <= 0) {
            return null;
        }

        $number = 'FLT-FUEL-'.str_pad((string) $log->id, 6, '0', STR_PAD_LEFT);
        $vehicle = $log->vehicle;

        $entry = JournalEntry::create([
            'entry_number' => 'JE-'.$number,
            'entry_date' => $log->log_date,
            'project_id' => $vehicle?->project_id,
            'reference' => $number,
            'description' => "قيد تعبئة وقود مركبة {$vehicle?->plate_number} - {$number}",
            'status' => 'posted',
            'created_by' => $userId,
        ]);

        $entry->lines()->create([
            'account_id' => DefaultAccounts::fuelExpense()->id,
            'debit' => $log->cost,
            'credit' => 0,
            'memo' => $log->fuel_station ? "وقود - {$log->fuel_station}" : 'تعبئة وقود',
        ]);

        $entry->lines()->create([
            'account_id' => DefaultAccounts::cashAndBank()->id,
            'debit' => 0,
            'credit' => $log->cost,
            'memo' => "سداد وقود مركبة {$vehicle?->plate_number}",
        ]);

        $log->update(['journal_entry_id' => $entry->id]);

        return $entry;
    }

    /**
     * لو المخالفة على الشركة: مصروف مباشر (مدين مصروف / دائن نقدية).
     * لو على السائق: ذمة مدينة على السائق (مدين ذمم سائقين / دائن نقدية)
     * لحد ما تتحصّل من راتبه أو تتسدد منه لاحقًا.
     */
    public function postViolation(Violation $violation, ?int $userId = null): ?JournalEntry
    {
        if ((float) $violation->amount <= 0) {
            return null;
        }

        $number = 'FLT-VIO-'.str_pad((string) $violation->id, 6, '0', STR_PAD_LEFT);
        $vehicle = $violation->vehicle;

        $entry = JournalEntry::create([
            'entry_number' => 'JE-'.$number,
            'entry_date' => $violation->paid_date ?? $violation->violation_date,
            'project_id' => $vehicle?->project_id,
            'reference' => $number,
            'description' => "قيد مخالفة مرورية مركبة {$vehicle?->plate_number} - {$number}",
            'status' => 'posted',
            'created_by' => $userId,
        ]);

        $debitAccount = $violation->liability === 'driver'
            ? DefaultAccounts::dueFromDrivers()
            : DefaultAccounts::trafficViolationsExpense();

        $entry->lines()->create([
            'account_id' => $debitAccount->id,
            'debit' => $violation->amount,
            'credit' => 0,
            'memo' => $violation->liability === 'driver'
                ? "مخالفة محمّلة على السائق - {$violation->driver?->full_name}"
                : 'مخالفة مرورية على الشركة',
        ]);

        $entry->lines()->create([
            'account_id' => DefaultAccounts::cashAndBank()->id,
            'debit' => 0,
            'credit' => $violation->amount,
            'memo' => "سداد مخالفة مركبة {$vehicle?->plate_number}",
        ]);

        $violation->update(['journal_entry_id' => $entry->id]);

        return $entry;
    }
}
