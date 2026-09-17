<?php

namespace App\Services;

use App\Models\BookkeepingItem;
use App\Models\DailySummary;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Support\DateRange;
use App\Support\OperatingDays;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $summaryCache = [];

    /**
     * Ringkasan keuangan untuk rentang tanggal (dashboard & laporan).
     *
     * @return array<string, mixed>
     */
    public function summarize(Carbon $from, Carbon $until, bool $includePeriodExpenses = false): array
    {
        $cacheKey = $from->toDateString().'|'.$until->toDateString().'|'.(int) $includePeriodExpenses;

        if (isset($this->summaryCache[$cacheKey])) {
            return $this->summaryCache[$cacheKey];
        }
        $summaries = DailySummary::query()
            ->whereBetween('period_date', [$from->toDateString(), $until->toDateString()])
            ->orderBy('period_date')
            ->get();

        $operational = (int) Expense::query()->affectingRange($from, $until, $includePeriodExpenses)->sum('amount');
        $actualRevenue = (int) $summaries->sum('actual_revenue');
        $normalRevenue = (int) $summaries->sum('normal_revenue');
        $additionalIncome = (int) $summaries->sum('additional_income');
        $discount = (int) $summaries->sum('discount');
        $workerCost = (int) $summaries->sum('worker_cost_total');
        $totalUnits = (int) $summaries->sum('total_units');
        $margin = $actualRevenue - $workerCost;
        $netProfit = $margin - $operational;
        $calendarDays = OperatingDays::calendarCount($from, $until);
        $operatingDays = OperatingDays::count($summaries);

        $vehicleStats = $this->mergeBreakdown($summaries, 'vehicle_breakdown');
        $expensesByCategory = $this->expensesByCategory($from, $until, $includePeriodExpenses);

        $this->summaryCache[$cacheKey] = [
            'from' => $from,
            'until' => $until,
            'days' => $operatingDays,
            'operating_days' => $operatingDays,
            'calendar_days' => $calendarDays,
            'operating_days_label' => OperatingDays::label($summaries, $operatingDays, $calendarDays),
            'total_units' => $totalUnits,
            'normal_revenue' => $normalRevenue,
            'additional_income' => $additionalIncome,
            'discount' => $discount,
            'actual_revenue' => $actualRevenue,
            'worker_cost' => $workerCost,
            'operational_cost' => $operational,
            'total_cost' => $workerCost + $operational,
            'margin' => $margin,
            'net_profit' => $netProfit,
            'margin_percent' => $actualRevenue > 0 ? round(($netProfit / $actualRevenue) * 100, 1) : 0.0,
            'avg_revenue_per_day' => OperatingDays::average($actualRevenue, $operatingDays),
            'avg_units_per_day' => OperatingDays::averageDecimal($totalUnits, $operatingDays),
            'avg_profit_per_day' => OperatingDays::average($netProfit, $operatingDays),
            'revenue_per_unit' => $totalUnits > 0 ? (int) round($actualRevenue / $totalUnits) : 0,
            'profit_per_unit' => $totalUnits > 0 ? (int) round($netProfit / $totalUnits) : 0,
            'expenses_by_category' => $expensesByCategory,
            'vehicle_stats' => $vehicleStats,
            'summaries' => $summaries,
        ];

        return $this->summaryCache[$cacheKey];
    }

    /**
     * Laba rugi sederhana sesuai PRD §25.
     *
     * @return array<string, mixed>
     */
    public function profitLoss(Carbon $from, Carbon $until, bool $includePeriodExpenses = true): array
    {
        return $this->summarize($from, $until, $includePeriodExpenses);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function vehicleAnalysis(Carbon $from, Carbon $until): Collection
    {
        $items = $this->itemsInRange($from, $until);
        $totalRevenue = max(1, (int) $items->sum('actual_revenue'));

        return $items
            ->groupBy('vehicle_type_id')
            ->map(function (Collection $group) use ($totalRevenue) {
                $revenue = (int) $group->sum('actual_revenue');
                $worker = (int) $group->sum('worker_total');
                $units = (int) $group->sum('quantity');

                return [
                    'name' => $group->first()?->is_custom
                        ? 'Lainnya'
                        : ($group->first()?->vehicleType?->name ?? 'Lainnya'),
                    'units' => $units,
                    'revenue' => $revenue,
                    'worker_cost' => $worker,
                    'margin' => $revenue - $worker,
                    'contribution' => round(($revenue / $totalRevenue) * 100, 1),
                ];
            })
            ->sortByDesc('revenue')
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function serviceAnalysis(Carbon $from, Carbon $until): Collection
    {
        $items = $this->itemsInRange($from, $until);
        $operatingDays = $items
            ->map(fn (BookkeepingItem $item) => $item->period?->period_date?->toDateString())
            ->filter()
            ->unique()
            ->count();

        return $items
            ->groupBy(fn (BookkeepingItem $item) => $item->service_id
                ? 's-'.$item->service_id
                : 'c-'.mb_strtolower($item->custom_name ?? $item->displayName()))
            ->map(function (Collection $group) use ($operatingDays) {
                $first = $group->first();
                $revenue = (int) $group->sum('actual_revenue');
                $worker = (int) $group->sum('worker_total');
                $units = (int) $group->sum('quantity');

                return [
                    'name' => $first?->displayName() ?? 'Layanan',
                    'vehicle' => $first?->is_custom ? 'Lainnya' : ($first?->vehicleType?->name ?? '—'),
                    'units' => $units,
                    'revenue' => $revenue,
                    'worker_cost' => $worker,
                    'margin' => $revenue - $worker,
                    'avg_per_day' => OperatingDays::averageDecimal($units, $operatingDays),
                ];
            })
            ->sortByDesc('units')
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function weeklyRows(Carbon $from, Carbon $until): Collection
    {
        $summaries = DailySummary::query()
            ->whereBetween('period_date', [$from->toDateString(), $until->toDateString()])
            ->orderBy('period_date')
            ->get();

        return $summaries
            ->groupBy(fn (DailySummary $row) => $row->period_date->copy()->startOfWeek(Carbon::MONDAY)->toDateString())
            ->map(function (Collection $week, string $start) use ($from, $until) {
                $weekFrom = Carbon::parse($start)->startOfWeek(Carbon::MONDAY);
                $weekUntil = $weekFrom->copy()->endOfWeek(Carbon::SUNDAY);

                if ($weekFrom->lt($from)) {
                    $weekFrom = $from->copy()->startOfDay();
                }

                if ($weekUntil->gt($until)) {
                    $weekUntil = $until->copy()->startOfDay();
                }
                $actualRevenue = (int) $week->sum('actual_revenue');
                $workerCost = (int) $week->sum('worker_cost_total');
                $operational = (int) $week->sum('operational_cost');
                $totalUnits = (int) $week->sum('total_units');

                $vehicleStats = $this->mergeBreakdown($week, 'vehicle_breakdown');

                return [
                    'from' => $weekFrom,
                    'until' => $weekUntil,
                    'total_units' => $totalUnits,
                    'actual_revenue' => $actualRevenue,
                    'worker_cost' => $workerCost,
                    'operational_cost' => $operational,
                    'net_profit' => $actualRevenue - $workerCost - $operational,
                    'vehicle_stats' => $vehicleStats,
                    'vehicle_hint' => collect($vehicleStats)
                        ->map(fn (array $row) => $row['name'].' '.$row['units'])
                        ->implode(' · '),
                ];
            })
            ->values();
    }

    /**
     * @return list<ExpenseCategory>
     */
    public function expenseCategories(): Collection
    {
        return ExpenseCategory::query()->orderBy('sort_order')->orderBy('name')->get();
    }

    /**
     * @return Collection<string, int>
     */
    private function expensesByCategory(Carbon $from, Carbon $until, bool $includePeriodExpenses): Collection
    {
        return Expense::query()
            ->affectingRange($from, $until, $includePeriodExpenses)
            ->with('category')
            ->get()
            ->groupBy(fn (Expense $expense) => $expense->category?->name ?? 'Lainnya')
            ->map(fn (Collection $group) => (int) $group->sum('amount'))
            ->sortDesc();
    }

    /**
     * @return Collection<int, BookkeepingItem>
     */
    private function itemsInRange(Carbon $from, Carbon $until): Collection
    {
        return BookkeepingItem::query()
            ->with(['service', 'vehicleType', 'period'])
            ->whereHas('period', function ($query) use ($from, $until) {
                $query->whereBetween('period_date', [$from->toDateString(), $until->toDateString()]);
            })
            ->get();
    }

    /**
     * @return array<string, array{name:string, units:int, revenue:int, worker_cost:int, margin:int}>
     */
    private function mergeBreakdown(Collection $summaries, string $key): array
    {
        $merged = [];

        foreach ($summaries as $summary) {
            foreach ($summary->{$key} ?? [] as $row) {
                $name = $row['name'] ?? 'Lainnya';
                $merged[$name] ??= [
                    'name' => $name,
                    'units' => 0,
                    'revenue' => 0,
                    'worker_cost' => 0,
                    'margin' => 0,
                ];
                $merged[$name]['units'] += (int) ($row['units'] ?? 0);
                $merged[$name]['revenue'] += (int) ($row['revenue'] ?? 0);
                $merged[$name]['worker_cost'] += (int) ($row['worker_cost'] ?? 0);
                $merged[$name]['margin'] += (int) ($row['margin'] ?? 0);
            }
        }

        return array_values($merged);
    }

    public function rangeLabel(DateRange $range): string
    {
        if ($range->from->isSameDay($range->until)) {
            return tanggal_id($range->from);
        }

        return tanggal_id($range->from).' s/d '.tanggal_id($range->until);
    }
}
