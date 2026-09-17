<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerRate extends Model
{
    protected $fillable = [
        'vehicle_type_id',
        'service_id',
        'rate',
        'effective_from',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'integer',
            'effective_from' => 'date',
        ];
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
