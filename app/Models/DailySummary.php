<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySummary extends Model
{
    protected $fillable = [
        'bookkeeping_period_id',
        'period_date',
        'total_units',
        'normal_revenue',
        'additional_income',
        'discount',
        'actual_revenue',
        'worker_cost_total',
        'operational_cost',
        'margin',
        'net_profit',
        'vehicle_breakdown',
        'service_breakdown',
    ];

    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'total_units' => 'integer',
            'normal_revenue' => 'integer',
            'additional_income' => 'integer',
            'discount' => 'integer',
            'actual_revenue' => 'integer',
            'worker_cost_total' => 'integer',
            'operational_cost' => 'integer',
            'margin' => 'integer',
            'net_profit' => 'integer',
            'vehicle_breakdown' => 'array',
            'service_breakdown' => 'array',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(BookkeepingPeriod::class, 'bookkeeping_period_id');
    }
}
