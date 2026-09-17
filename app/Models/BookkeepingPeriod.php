<?php

namespace App\Models;

use App\Enums\PeriodStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookkeepingPeriod extends Model
{
    protected $fillable = [
        'period_date',
        'notes',
        'status',
        'additional_income',
        'discount',
        'created_by',
        'updated_by',
        'finalized_at',
        'locked_at',
        'locked_by',
    ];

    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'status' => PeriodStatus::class,
            'additional_income' => 'integer',
            'discount' => 'integer',
            'finalized_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookkeepingItem::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function incomeAdjustments(): HasMany
    {
        return $this->hasMany(IncomeAdjustment::class);
    }

    public function summary(): HasOne
    {
        return $this->hasOne(DailySummary::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function locker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function isLocked(): bool
    {
        return $this->status === PeriodStatus::Locked;
    }

    public function isDraft(): bool
    {
        return $this->status === PeriodStatus::Draft;
    }
}
