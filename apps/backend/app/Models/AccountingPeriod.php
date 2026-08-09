<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class AccountingPeriod extends Model
{
    use HasFactory;

    /**
     * لما تبقى true، حماية الفترات المقفلة بتتوقف مؤقتاً.
     * مستخدمة في قيد الإقفال السنوي بس — لأنه بطبيعته بيتكتب على آخر
     * يوم في السنة المالية بعد ما الفترات بتتقفل.
     */
    private static bool $guardDisabled = false;

    protected $fillable = [
        'name', 'fiscal_year', 'period_number', 'start_date', 'end_date',
        'status', 'closed_at', 'closed_by', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'closed_at' => 'datetime',
        'fiscal_year' => 'integer',
        'period_number' => 'integer',
    ];

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /** الفترة اللي التاريخ ده واقع فيها (لو موجودة). */
    public static function forDate(string|Carbon $date): ?self
    {
        $day = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        return self::query()
            ->whereDate('start_date', '<=', $day)
            ->whereDate('end_date', '>=', $day)
            ->first();
    }

    /**
     * هل التاريخ ده واقع في فترة مقفلة؟
     * التواريخ اللي مش داخل أي فترة معرّفة بتعتبر مفتوحة — عشان النظام
     * يفضل شغال عادي لو المستخدم لسه ما عرّفش فترات.
     */
    public static function isDateClosed(string|Carbon $date): bool
    {
        if (self::$guardDisabled) {
            return false;
        }

        return self::forDate($date)?->isClosed() ?? false;
    }

    /** ينفّذ callback مع تعطيل الحماية مؤقتاً (قيد الإقفال السنوي). */
    public static function withoutGuard(callable $callback): mixed
    {
        self::$guardDisabled = true;

        try {
            return $callback();
        } finally {
            self::$guardDisabled = false;
        }
    }

    /** ينشئ 12 فترة شهرية لسنة مالية (بيتخطى الموجود). */
    public static function generateForYear(int $fiscalYear): int
    {
        $created = 0;

        for ($month = 1; $month <= 12; $month++) {
            $start = Carbon::create($fiscalYear, $month, 1);

            $period = self::firstOrCreate(
                ['fiscal_year' => $fiscalYear, 'period_number' => $month],
                [
                    'name' => $start->format('Y-m'),
                    'start_date' => $start->toDateString(),
                    'end_date' => $start->copy()->endOfMonth()->toDateString(),
                    'status' => 'open',
                ]
            );

            if ($period->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }
}
