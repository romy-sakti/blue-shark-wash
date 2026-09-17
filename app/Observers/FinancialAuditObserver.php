<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\Service;
use App\Models\WorkerRate;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;

class FinancialAuditObserver
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function created(Model $model): void
    {
        if ($this->shouldSkip($model)) {
            return;
        }

        $this->auditLogger->log($model, 'created', newValues: $this->payload($model));
    }

    public function updated(Model $model): void
    {
        if ($this->shouldSkip($model)) {
            return;
        }

        $tracked = $this->trackedFields($model);
        $changes = array_intersect_key($model->getChanges(), array_flip($tracked));

        if ($changes === []) {
            return;
        }

        $this->auditLogger->log(
            $model,
            'updated',
            $this->normalize(array_intersect_key($model->getOriginal(), array_flip($tracked))),
            $this->payload($model),
        );
    }

    public function deleted(Model $model): void
    {
        if ($this->shouldSkip($model)) {
            return;
        }

        $this->auditLogger->log($model, 'deleted', oldValues: $this->payload($model));
    }

    private function shouldSkip(Model $model): bool
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return true;
        }

        return $model instanceof Expense && filled($model->bookkeeping_period_id);
    }

    /**
     * @return list<string>
     */
    private function trackedFields(Model $model): array
    {
        if ($model instanceof Expense) {
            return ['name', 'amount', 'expense_date', 'allocation', 'notes', 'expense_category_id'];
        }

        if ($model instanceof Service) {
            return ['name', 'normal_price', 'worker_cost', 'is_active', 'vehicle_type_id'];
        }

        if ($model instanceof WorkerRate) {
            return ['rate', 'effective_from', 'vehicle_type_id', 'service_id', 'notes'];
        }

        return array_keys($model->getAttributes());
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Model $model): array
    {
        if ($model instanceof Expense) {
            return $this->normalize([
                'name' => $model->name,
                'amount' => $model->amount,
                'expense_date' => optional($model->expense_date)?->toDateString(),
                'allocation' => $model->allocation?->value,
                'kategori' => $model->category?->name,
                'notes' => $model->notes,
            ]);
        }

        if ($model instanceof Service) {
            return $this->normalize([
                'name' => $model->name,
                'jenis_kendaraan' => $model->vehicleType?->name,
                'normal_price' => $model->normal_price,
                'worker_cost' => $model->worker_cost,
                'is_active' => $model->is_active,
            ]);
        }

        if ($model instanceof WorkerRate) {
            return $this->normalize([
                'jenis_kendaraan' => $model->vehicleType?->name,
                'layanan' => $model->service?->name,
                'rate' => $model->rate,
                'effective_from' => optional($model->effective_from)?->toDateString(),
                'notes' => $model->notes,
            ]);
        }

        return $this->normalize($model->attributesToArray());
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function normalize(array $values): array
    {
        foreach ($values as $key => $value) {
            if ($value instanceof \BackedEnum) {
                $values[$key] = $value->value;
            } elseif ($value instanceof \DateTimeInterface) {
                $values[$key] = $value->format('Y-m-d');
            }
        }

        return $values;
    }
}
