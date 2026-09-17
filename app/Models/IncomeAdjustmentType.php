<?php

namespace App\Models;

use App\Enums\AdjustmentDirection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomeAdjustmentType extends Model
{
    protected $fillable = [
        'name',
        'direction',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'direction' => AdjustmentDirection::class,
            'is_active' => 'boolean',
        ];
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(IncomeAdjustment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
