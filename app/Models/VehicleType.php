<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'sort_order',
        'is_active',
        'appears_on_form',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'appears_on_form' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class)->orderBy('sort_order')->orderBy('name');
    }

    public function workerRates(): HasMany
    {
        return $this->hasMany(WorkerRate::class)->orderByDesc('effective_from');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOnDailyForm(Builder $query): Builder
    {
        return $query->where('appears_on_form', true)->where('is_active', true);
    }
}
