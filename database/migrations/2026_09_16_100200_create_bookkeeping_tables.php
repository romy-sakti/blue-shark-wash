<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookkeeping_periods', function (Blueprint $table) {
            $table->id();
            $table->date('period_date')->unique();
            $table->text('notes')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('additional_income')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('bookkeeping_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bookkeeping_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedBigInteger('normal_price')->default(0);
            $table->bigInteger('adjustment_amount')->default(0);
            $table->unsignedBigInteger('actual_revenue')->default(0);
            $table->unsignedBigInteger('worker_cost')->default(0);
            $table->unsignedBigInteger('worker_total')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['bookkeeping_period_id', 'service_id']);
        });

        Schema::create('income_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bookkeeping_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('income_adjustment_type_id')->nullable()->constrained()->nullOnDelete();
            $table->bigInteger('amount')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bookkeeping_period_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('expense_category_id')->constrained()->restrictOnDelete();
            $table->date('expense_date');
            $table->date('attribution_month');
            $table->string('allocation')->default('daily');
            $table->string('name');
            $table->unsignedBigInteger('amount')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['expense_date', 'allocation']);
            $table->index('attribution_month');
        });

        Schema::create('daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bookkeeping_period_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('period_date')->unique();
            $table->unsignedInteger('total_units')->default(0);
            $table->unsignedBigInteger('normal_revenue')->default(0);
            $table->unsignedBigInteger('additional_income')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('actual_revenue')->default(0);
            $table->unsignedBigInteger('worker_cost_total')->default(0);
            $table->unsignedBigInteger('operational_cost')->default(0);
            $table->bigInteger('margin')->default(0);
            $table->bigInteger('net_profit')->default(0);
            $table->json('vehicle_breakdown')->nullable();
            $table->json('service_breakdown')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_summaries');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('income_adjustments');
        Schema::dropIfExists('bookkeeping_items');
        Schema::dropIfExists('bookkeeping_periods');
    }
};
