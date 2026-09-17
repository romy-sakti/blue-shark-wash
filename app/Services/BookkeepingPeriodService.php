<?php

namespace App\Services;

use App\Enums\ExpenseAllocation;
use App\Enums\PeriodStatus;
use App\Models\BookkeepingItem;
use App\Models\BookkeepingPeriod;
use App\Models\DailySummary;
use App\Models\Expense;
use App\Models\IncomeAdjustmentType;
use App\Models\Service;
use App\Models\User;
use App\Support\CalculatedBookkeeping;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookkeepingPeriodService
{
    public function __construct(
        private BookkeepingCalculator $calculator,
        private WorkerRateResolver $workerRateResolver,
        private AuditLogger $auditLogger,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function save(array $data, User $user, ?BookkeepingPeriod $period = null): BookkeepingPeriod
    {
        $periodDate = Carbon::parse($data['period_date'])->toDateString();
        $reason = $data['correction_reason'] ?? null;

        return DB::transaction(function () use ($data, $user, $period, $periodDate, $reason) {
            if ($period) {
                $this->assertCanMutate($period, $user, $reason);
                $old = $this->snapshot($period);
            } else {
                $existing = BookkeepingPeriod::query()->whereDate('period_date', $periodDate)->first();

                if ($existing) {
                    throw ValidationException::withMessages([
                        'period_date' => 'Pembukuan untuk tanggal ini sudah ada. Buka data tersebut untuk mengubahnya.',
                    ]);
                }

                $old = null;
                $period = new BookkeepingPeriod;
            }

            $period->fill([
                'period_date' => $periodDate,
                'notes' => $data['notes'] ?? null,
                'status' => $period->exists ? $period->status : PeriodStatus::Draft,
                'additional_income' => parse_rupiah($data['additional_income'] ?? 0),
                'discount' => parse_rupiah($data['discount'] ?? 0),
                'updated_by' => $user->id,
            ]);

            if (! $period->exists) {
                $period->created_by = $user->id;
            }

            $period->save();

            $this->syncItems($period, $data['quantities'] ?? [], $data['extra_lines'] ?? []);
            $this->syncDailyExpenses($period, $data['daily_expenses'] ?? [], $user);
            $this->syncQuickAdjustments($period);
            $this->rebuildSummary($period);
            $period->load(['items.service', 'items.vehicleType', 'expenses', 'summary']);

            $this->auditLogger->log(
                $period,
                $old === null ? 'created' : 'updated',
                $old,
                $this->snapshot($period),
                $reason,
                $user->id,
            );

            return $period;
        });
    }

    public function rebuildSummary(BookkeepingPeriod $period): CalculatedBookkeeping
    {
        $period->load(['items.service.vehicleType', 'expenses']);

        $lines = $period->items->map(fn (BookkeepingItem $item) => [
            'service_id' => $item->service_id,
            'vehicle_type_id' => $item->vehicle_type_id,
            'service_name' => $item->displayName(),
            'vehicle_type_name' => $item->is_custom ? 'Lainnya' : ($item->vehicleType?->name ?? 'Lainnya'),
            'quantity' => $item->quantity,
            'normal_price' => $item->normal_price,
            'worker_cost' => $item->worker_cost,
        ])->all();

        $dailyOperational = (int) $period->expenses
            ->where('allocation', ExpenseAllocation::Daily)
            ->sum('amount');

        $calculated = $this->calculator->calculate(
            $lines,
            (int) $period->additional_income,
            (int) $period->discount,
            $dailyOperational,
        );

        DailySummary::query()->updateOrCreate(
            ['bookkeeping_period_id' => $period->id],
            [
                'period_date' => $period->period_date,
                'total_units' => $calculated->totalUnits,
                'normal_revenue' => $calculated->normalRevenue,
                'additional_income' => $calculated->additionalIncome,
                'discount' => $calculated->discount,
                'actual_revenue' => $calculated->actualRevenue,
                'worker_cost_total' => $calculated->workerCostTotal,
                'operational_cost' => $calculated->operationalCost,
                'margin' => $calculated->margin,
                'net_profit' => $calculated->netProfit,
                'vehicle_breakdown' => $calculated->vehicleBreakdown,
                'service_breakdown' => $calculated->serviceBreakdown,
            ],
        );

        return $calculated;
    }

    public function finalize(BookkeepingPeriod $period, User $user): BookkeepingPeriod
    {
        $this->assertCanMutate($period, $user);

        $period->update([
            'status' => PeriodStatus::Final,
            'finalized_at' => now(),
            'updated_by' => $user->id,
        ]);

        $this->auditLogger->log(
            $period,
            'finalized',
            oldValues: ['status' => PeriodStatus::Draft->value],
            newValues: $this->snapshot($period->fresh(['items.service', 'expenses', 'summary']) ?? $period),
            userId: $user->id,
        );

        return $period;
    }

    public function lock(BookkeepingPeriod $period, User $user): BookkeepingPeriod
    {
        if (! $user->isOwner()) {
            throw ValidationException::withMessages([
                'status' => 'Hanya owner yang dapat mengunci periode.',
            ]);
        }

        $previousStatus = $period->status?->value;

        $period->update([
            'status' => PeriodStatus::Locked,
            'locked_at' => now(),
            'locked_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->auditLogger->log(
            $period,
            'locked',
            oldValues: ['status' => $previousStatus ?? PeriodStatus::Final->value],
            newValues: $this->snapshot($period->fresh(['items.service', 'expenses', 'summary']) ?? $period),
            userId: $user->id,
        );

        return $period;
    }

    public function unlock(BookkeepingPeriod $period, User $user, string $reason): BookkeepingPeriod
    {
        if (! $user->isOwner()) {
            throw ValidationException::withMessages([
                'status' => 'Hanya owner yang dapat membuka kunci periode.',
            ]);
        }

        $period->update([
            'status' => PeriodStatus::Final,
            'locked_at' => null,
            'locked_by' => null,
            'updated_by' => $user->id,
        ]);

        $this->auditLogger->log(
            $period,
            'unlocked',
            oldValues: ['status' => PeriodStatus::Locked->value],
            newValues: $this->snapshot($period->fresh(['items.service', 'expenses', 'summary']) ?? $period),
            reason: $reason,
            userId: $user->id,
        );

        return $period;
    }

    public function delete(BookkeepingPeriod $period, User $user): void
    {
        if ($period->isLocked() && ! $user->isOwner()) {
            throw ValidationException::withMessages([
                'status' => 'Periode terkunci tidak dapat dihapus.',
            ]);
        }

        $snapshot = $this->snapshot($period);
        $this->auditLogger->log($period, 'deleted', $snapshot, userId: $user->id);
        $period->delete();
    }

    /**
     * @param  array<int|string, mixed>  $quantities
     * @param  array<int, array<string, mixed>>  $extraLines
     */
    private function syncItems(BookkeepingPeriod $period, array $quantities, array $extraLines): void
    {
        $keepIds = [];

        foreach ($quantities as $serviceId => $quantity) {
            $quantity = (int) $quantity;

            if ($quantity <= 0) {
                continue;
            }

            $service = Service::query()->with('vehicleType')->find((int) $serviceId);

            if (! $service) {
                continue;
            }

            $workerCost = $this->workerRateResolver->resolve($service, $period->period_date);
            $normalPrice = (int) $service->normal_price;

            $item = BookkeepingItem::query()->updateOrCreate(
                [
                    'bookkeeping_period_id' => $period->id,
                    'service_id' => $service->id,
                    'is_custom' => false,
                ],
                [
                    'vehicle_type_id' => $service->vehicle_type_id,
                    'custom_name' => null,
                    'quantity' => $quantity,
                    'normal_price' => $normalPrice,
                    'adjustment_amount' => 0,
                    'actual_revenue' => $quantity * $normalPrice,
                    'worker_cost' => $workerCost,
                    'worker_total' => $quantity * $workerCost,
                ],
            );

            $keepIds[] = $item->id;
        }

        foreach ($extraLines as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $amount = parse_rupiah($row['amount'] ?? 0);
            $quantity = max(1, (int) ($row['quantity'] ?? 1));
            $workerCost = parse_rupiah($row['worker_cost'] ?? 0);

            if ($name === '' || $amount <= 0) {
                continue;
            }

            $payload = [
                'vehicle_type_id' => null,
                'service_id' => null,
                'custom_name' => $name,
                'is_custom' => true,
                'quantity' => $quantity,
                'normal_price' => $amount,
                'adjustment_amount' => 0,
                'actual_revenue' => $quantity * $amount,
                'worker_cost' => $workerCost,
                'worker_total' => $quantity * $workerCost,
            ];

            if (! empty($row['id'])) {
                $item = BookkeepingItem::query()
                    ->where('bookkeeping_period_id', $period->id)
                    ->where('is_custom', true)
                    ->whereKey($row['id'])
                    ->first();

                if ($item) {
                    $item->update($payload);
                    $keepIds[] = $item->id;

                    continue;
                }
            }

            $keepIds[] = $period->items()->create($payload)->id;
        }

        $period->items()
            ->when($keepIds !== [], fn ($q) => $q->whereNotIn('id', $keepIds), fn ($q) => $q)
            ->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $expenses
     */
    private function syncDailyExpenses(BookkeepingPeriod $period, array $expenses, User $user): void
    {
        $keepIds = [];

        foreach ($expenses as $row) {
            $amount = parse_rupiah($row['amount'] ?? 0);
            $categoryId = (int) ($row['expense_category_id'] ?? 0);
            $name = trim((string) ($row['name'] ?? ''));

            if ($amount <= 0 || $categoryId <= 0 || $name === '') {
                continue;
            }

            $payload = [
                'expense_category_id' => $categoryId,
                'expense_date' => $period->period_date,
                'attribution_month' => $period->period_date->copy()->startOfMonth(),
                'allocation' => ExpenseAllocation::Daily,
                'name' => $name,
                'amount' => $amount,
                'notes' => $row['notes'] ?? null,
                'created_by' => $user->id,
            ];

            if (! empty($row['id'])) {
                $expense = Expense::query()
                    ->where('bookkeeping_period_id', $period->id)
                    ->whereKey($row['id'])
                    ->first();

                if ($expense) {
                    $expense->update($payload);
                    $keepIds[] = $expense->id;

                    continue;
                }
            }

            $created = $period->expenses()->create($payload);
            $keepIds[] = $created->id;
        }

        $period->expenses()
            ->where('allocation', ExpenseAllocation::Daily)
            ->when($keepIds !== [], fn ($q) => $q->whereNotIn('id', $keepIds), fn ($q) => $q)
            ->delete();
    }

    private function syncQuickAdjustments(BookkeepingPeriod $period): void
    {
        $period->incomeAdjustments()->delete();

        $increaseType = IncomeAdjustmentType::query()
            ->where('direction', 'increase')
            ->orderBy('id')
            ->first();

        $decreaseType = IncomeAdjustmentType::query()
            ->where('direction', 'decrease')
            ->orderBy('id')
            ->first();

        if ($period->additional_income > 0 && $increaseType) {
            $period->incomeAdjustments()->create([
                'income_adjustment_type_id' => $increaseType->id,
                'amount' => $period->additional_income,
                'notes' => 'Pendapatan tambahan',
            ]);
        }

        if ($period->discount > 0 && $decreaseType) {
            $period->incomeAdjustments()->create([
                'income_adjustment_type_id' => $decreaseType->id,
                'amount' => $period->discount,
                'notes' => 'Potongan',
            ]);
        }
    }

    private function assertCanMutate(BookkeepingPeriod $period, User $user, ?string $reason = null): void
    {
        if (! $period->isLocked()) {
            return;
        }

        if (! $user->isOwner()) {
            throw ValidationException::withMessages([
                'status' => 'Periode terkunci. Perubahan hanya melalui owner dengan alasan koreksi.',
            ]);
        }

        if (blank($reason)) {
            throw ValidationException::withMessages([
                'correction_reason' => 'Alasan koreksi wajib diisi untuk mengubah periode terkunci.',
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(BookkeepingPeriod $period): array
    {
        $period->loadMissing(['items.service', 'expenses', 'summary']);

        return [
            'period_date' => optional($period->period_date)?->toDateString(),
            'status' => $period->status?->value,
            'notes' => $period->notes,
            'additional_income' => $period->additional_income,
            'discount' => $period->discount,
            'items' => $period->items->map(fn (BookkeepingItem $item) => [
                'custom_name' => $item->is_custom ? $item->custom_name : ($item->service?->name ?? $item->custom_name),
                'quantity' => $item->quantity,
                'actual_revenue' => $item->actual_revenue,
            ])->all(),
            'expenses' => $period->expenses->map(fn (Expense $expense) => [
                'name' => $expense->name,
                'amount' => $expense->amount,
            ])->all(),
            'summary' => [
                'total_units' => $period->summary?->total_units,
                'actual_revenue' => $period->summary?->actual_revenue,
                'net_profit' => $period->summary?->net_profit,
            ],
        ];
    }
}
