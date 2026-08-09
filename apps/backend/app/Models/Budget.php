<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'period', 'period_start', 'period_end',
        'account_id', 'project_id', 'budgeted_amount', 'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'budgeted_amount' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * الفعلي = صافي حركة الحساب (مدين - دائن) في القيود المُرحّلة خلال الفترة.
     * لحسابات المصروفات ده بيمثل الصرف الفعلي المقارن بالموازنة.
     */
    public function actualAmount(): float
    {
        $debit = (float) JournalEntryLine::query()
            ->where('account_id', $this->account_id)
            ->whereHas('entry', function ($q) {
                $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$this->period_start, $this->period_end]);
            })
            ->sum('debit');

        $credit = (float) JournalEntryLine::query()
            ->where('account_id', $this->account_id)
            ->whereHas('entry', function ($q) {
                $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$this->period_start, $this->period_end]);
            })
            ->sum('credit');

        return $debit - $credit;
    }

    public function varianceAmount(): float
    {
        return (float) $this->budgeted_amount - $this->actualAmount();
    }
}
