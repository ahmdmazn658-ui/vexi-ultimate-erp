<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_name', 'account_name', 'account_number', 'iban',
        'currency', 'account_id', 'opening_balance', 'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    /**
     * الرصيد الحالي = الرصيد الافتتاحي + الإيداعات - السحوبات
     */
    public function currentBalance(): float
    {
        $deposits = (float) $this->transactions()->where('type', 'deposit')->sum('amount');
        $withdrawals = (float) $this->transactions()->where('type', 'withdrawal')->sum('amount');

        return (float) $this->opening_balance + $deposits - $withdrawals;
    }
}
