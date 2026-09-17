<?php

namespace App\Models;

use App\Enums\ExpenseAllocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Expense extends Model
{
    protected $fillable = [
        'bookkeeping_period_id',
        'expense_category_id',
        'expense_date',
        'attribution_month',
        'allocation',
        'name',
        'amount',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'attribution_month' => 'date',
            'allocation' => ExpenseAllocation::class,
            'amount' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Expense $expense): void {
            if ($expense->attribution_month === null && $expense->expense_date) {
                $expense->attribution_month = $expense->expense_date->copy()->startOfMonth();
            }
        });
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(BookkeepingPeriod::class, 'bookkeeping_period_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Pengeluaran yang masuk ke laporan pada rentang tanggal.
     * - Harian selalu dihitung dari expense_date
     * - Biaya periode (listrik, PDAM) hanya jika $includePeriodExpenses = true
     */
    public function scopeAffectingRange(Builder $query, Carbon $from, Carbon $until, bool $includePeriodExpenses = false): Builder
    {
        $fromDate = $from->toDateString();
        $untilDate = $until->toDateString();
        $fromMonth = $from->copy()->startOfMonth()->toDateString();
        $untilMonth = $until->copy()->startOfMonth()->toDateString();

        return $query->where(function (Builder $q) use ($fromDate, $untilDate, $fromMonth, $untilMonth, $includePeriodExpenses) {
            $q->where(function (Builder $daily) use ($fromDate, $untilDate) {
                $daily->where('allocation', ExpenseAllocation::Daily->value)
                    ->whereBetween('expense_date', [$fromDate, $untilDate]);
            });

            if ($includePeriodExpenses) {
                $q->orWhere(function (Builder $period) use ($fromMonth, $untilMonth) {
                    $period->where('allocation', ExpenseAllocation::Period->value)
                        ->whereBetween('attribution_month', [$fromMonth, $untilMonth]);
                });
            }
        });
    }
}
