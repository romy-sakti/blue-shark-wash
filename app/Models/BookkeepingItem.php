<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookkeepingItem extends Model
{
    protected $fillable = [
        'bookkeeping_period_id',
        'vehicle_type_id',
        'service_id',
        'custom_name',
        'is_custom',
        'quantity',
        'normal_price',
        'adjustment_amount',
        'actual_revenue',
        'worker_cost',
        'worker_total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'normal_price' => 'integer',
            'adjustment_amount' => 'integer',
            'actual_revenue' => 'integer',
            'worker_cost' => 'integer',
            'worker_total' => 'integer',
            'is_custom' => 'boolean',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(BookkeepingPeriod::class, 'bookkeeping_period_id');
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function displayName(): string
    {
        return $this->custom_name ?: ($this->service?->name ?? 'Layanan');
    }

    public function lineRevenue(): int
    {
        return $this->quantity * $this->normal_price;
    }
}
