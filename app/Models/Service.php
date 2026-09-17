<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'vehicle_type_id',
        'name',
        'normal_price',
        'worker_cost',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'normal_price' => 'integer',
            'worker_cost' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function bookkeepingItems(): HasMany
    {
        return $this->hasMany(BookkeepingItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
