<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomeAdjustment extends Model
{
    protected $fillable = [
        'bookkeeping_period_id',
        'income_adjustment_type_id',
        'amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(BookkeepingPeriod::class, 'bookkeeping_period_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(IncomeAdjustmentType::class, 'income_adjustment_type_id');
    }
}
