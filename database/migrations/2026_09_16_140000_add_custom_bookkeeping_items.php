<?php

use App\Models\Service;
use App\Models\VehicleType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->boolean('appears_on_form')->default(true)->after('is_active');
        });

        Schema::table('bookkeeping_items', function (Blueprint $table) {
            $table->string('custom_name')->nullable()->after('service_id');
            $table->boolean('is_custom')->default(false)->after('custom_name');
        });

        DB::statement('ALTER TABLE bookkeeping_items MODIFY service_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE bookkeeping_items MODIFY vehicle_type_id BIGINT UNSIGNED NULL');

        $lainnyaId = VehicleType::query()->where('name', 'Lainnya')->value('id');

        $sepedaIds = Service::query()
            ->whereHas('vehicleType', fn ($query) => $query->where('name', 'Sepeda Listrik'))
            ->pluck('id');

        if ($sepedaIds->isNotEmpty()) {
            $serviceNames = Service::query()->whereIn('id', $sepedaIds)->pluck('name', 'id');

            DB::table('bookkeeping_items')
                ->whereIn('service_id', $sepedaIds)
                ->get()
                ->each(function (object $item) use ($lainnyaId, $serviceNames) {
                    DB::table('bookkeeping_items')->where('id', $item->id)->update([
                        'custom_name' => $serviceNames[$item->service_id] ?? 'Cuci Sepeda Listrik',
                        'is_custom' => true,
                        'service_id' => null,
                        'vehicle_type_id' => $lainnyaId,
                    ]);
                });
        }

        VehicleType::query()->where('name', 'Sepeda Listrik')->update([
            'appears_on_form' => false,
            'is_active' => false,
        ]);

        VehicleType::query()->where('name', 'Lainnya')->update([
            'appears_on_form' => false,
        ]);

        Service::query()
            ->whereHas('vehicleType', fn ($query) => $query->where('name', 'Sepeda Listrik'))
            ->update(['is_active' => false]);
    }

    public function down(): void
    {
        Schema::table('bookkeeping_items', function (Blueprint $table) {
            $table->dropColumn(['custom_name', 'is_custom']);
        });

        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->dropColumn('appears_on_form');
        });
    }
};
