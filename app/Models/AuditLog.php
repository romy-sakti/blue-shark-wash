<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'action',
        'old_values',
        'new_values',
        'reason',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function activityLabel(): string
    {
        return match ($this->action) {
            'created' => 'Mencatat',
            'updated' => 'Mengubah',
            'deleted' => 'Menghapus',
            'finalized' => 'Finalkan',
            'locked' => 'Mengunci',
            'unlocked' => 'Membuka kunci',
            'logged_in' => 'Masuk',
            default => ucfirst((string) $this->action),
        };
    }

    public function actionColor(): string
    {
        return match ($this->action) {
            'created', 'finalized' => 'success',
            'updated', 'unlocked' => 'warning',
            'deleted' => 'danger',
            'locked' => 'gray',
            'logged_in' => 'info',
            default => 'gray',
        };
    }

    public function subjectLabel(): string
    {
        $type = class_basename((string) $this->auditable_type);

        return match ($type) {
            'BookkeepingPeriod' => 'Pembukuan',
            'Expense' => 'Pengeluaran',
            'Service' => 'Layanan',
            'WorkerRate' => 'Tarif pekerja',
            'User' => 'Pengguna',
            default => $type !== '' ? $type : 'Data',
        };
    }

    public function summary(): string
    {
        $parts = [$this->activityLabel(), strtolower($this->subjectLabel())];
        $values = $this->new_values ?: $this->old_values ?: [];

        if (($values['period_date'] ?? null) || ($values['summary']['period_date'] ?? null)) {
            $date = $values['period_date'] ?? $values['summary']['period_date'];
            $parts[] = tanggal_id($date);
        }

        if (filled($values['name'] ?? null)) {
            $parts[] = $values['name'];
        }

        if (isset($values['summary']['actual_revenue'])) {
            $parts[] = 'omzet '.rupiah($values['summary']['actual_revenue']);
        } elseif (isset($values['amount'])) {
            $parts[] = rupiah($values['amount']);
        } elseif (isset($values['normal_price'])) {
            $parts[] = rupiah($values['normal_price']);
        } elseif (isset($values['rate'])) {
            $parts[] = rupiah($values['rate']).' / unit';
        }

        if (filled($this->reason)) {
            $parts[] = '('.$this->reason.')';
        }

        return implode(' · ', array_filter($parts, fn ($part) => filled($part)));
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, string>
     */
    public static function displayValues(array $values): array
    {
        $rows = [];
        $labels = [
            'period_date' => 'Tanggal pembukuan',
            'status' => 'Status',
            'notes' => 'Catatan',
            'additional_income' => 'Pendapatan tambahan',
            'discount' => 'Potongan',
            'name' => 'Nama',
            'amount' => 'Nominal',
            'expense_date' => 'Tanggal pengeluaran',
            'allocation' => 'Pembebanan',
            'normal_price' => 'Harga',
            'worker_cost' => 'Biaya pekerja',
            'is_active' => 'Aktif',
            'rate' => 'Tarif',
            'effective_from' => 'Berlaku mulai',
            'actual_revenue' => 'Omzet',
            'net_profit' => 'Laba bersih',
            'total_units' => 'Unit',
            'quantity' => 'Jumlah',
            'custom_name' => 'Layanan lain',
            'email' => 'Email',
            'jenis_kendaraan' => 'Jenis kendaraan',
            'layanan' => 'Layanan',
            'kategori' => 'Kategori',
        ];
        $money = ['amount', 'additional_income', 'discount', 'normal_price', 'worker_cost', 'rate', 'actual_revenue', 'net_profit', 'worker_total'];
        $dates = ['period_date', 'expense_date', 'effective_from'];

        foreach ($values as $key => $value) {
            if (in_array($key, ['id', 'service_id', 'vehicle_type_id', 'expense_category_id', 'created_by', 'updated_by', 'password'], true)) {
                continue;
            }

            if ($key === 'summary' && is_array($value)) {
                foreach (self::displayValues($value) as $nestedLabel => $nestedValue) {
                    $rows[$nestedLabel] = $nestedValue;
                }

                continue;
            }

            if ($key === 'items' && is_array($value)) {
                $rows['Rincian layanan'] = collect($value)
                    ->map(function ($item) {
                        $name = $item['custom_name'] ?? ('Layanan #'.($item['service_id'] ?? '—'));
                        $qty = $item['quantity'] ?? 0;

                        return $name.' × '.$qty.(isset($item['actual_revenue']) ? ' ('.rupiah($item['actual_revenue']).')' : '');
                    })
                    ->filter()
                    ->implode(', ');

                continue;
            }

            if ($key === 'expenses' && is_array($value)) {
                $rows['Pengeluaran harian'] = collect($value)
                    ->map(fn ($item) => ($item['name'] ?? 'Pengeluaran').' '.rupiah($item['amount'] ?? 0))
                    ->implode(', ') ?: '—';

                continue;
            }

            if (is_array($value) || is_object($value)) {
                continue;
            }

            $label = $labels[$key] ?? str_replace('_', ' ', (string) $key);

            if ($value === null || $value === '') {
                $rows[$label] = '—';
            } elseif (in_array($key, $money, true)) {
                $rows[$label] = rupiah($value);
            } elseif (in_array($key, $dates, true)) {
                $rows[$label] = tanggal_id($value);
            } elseif ($key === 'status') {
                $rows[$label] = match ((string) $value) {
                    'draft' => 'Draft',
                    'final' => 'Final',
                    'locked' => 'Terkunci',
                    default => (string) $value,
                };
            } elseif ($key === 'allocation') {
                $rows[$label] = match ((string) $value) {
                    'daily' => 'Harian',
                    'period' => 'Bulanan / periode',
                    default => (string) $value,
                };
            } elseif (is_bool($value) || $key === 'is_active') {
                $rows[$label] = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'Ya' : 'Tidak';
            } else {
                $rows[$label] = (string) $value;
            }
        }

        return $rows;
    }
}
