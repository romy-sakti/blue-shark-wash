<?php

namespace App\Providers;

use App\Models\Expense;
use App\Models\Service;
use App\Models\WorkerRate;
use App\Observers\FinancialAuditObserver;
use App\Services\BookkeepingCalculator;
use App\Services\ReportService;
use App\Services\WorkerRateResolver;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WorkerRateResolver::class);
        $this->app->singleton(ReportService::class);
        $this->app->singleton(BookkeepingCalculator::class);
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale', 'id'));
        Expense::observe(FinancialAuditObserver::class);
        Service::observe(FinancialAuditObserver::class);
        WorkerRate::observe(FinancialAuditObserver::class);
    }
}
