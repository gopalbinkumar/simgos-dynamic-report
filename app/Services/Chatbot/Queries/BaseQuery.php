<?php

namespace App\Services\Chatbot\Queries;

use App\Support\SimgosData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

abstract class BaseQuery
{
    protected function exists(string $table): bool
    {
        return SimgosData::tableExists($table);
    }

    protected function filteredQuery(string $table, array $period): Builder
    {
        $columns = SimgosData::columns($table);
        $query = SimgosData::query($table);

        if (in_array('TANGGAL', $columns, true)) {
            $query->whereBetween('TANGGAL', [
                $period['from']->copy()->startOfDay(),
                $period['to']->copy()->endOfDay(),
            ]);
        } elseif (in_array('TAHUN', $columns, true)) {
            $query->whereBetween('TAHUN', [$period['from']->year, $period['to']->year]);

            if ($period['from']->year === $period['to']->year && in_array('BULAN', $columns, true)) {
                $query->whereBetween('BULAN', [$period['from']->month, $period['to']->month]);
            }
        }

        if (filled($period['unit'] ?? null)) {
            $unit = $period['unit'];
            $query->where(function (Builder $builder) use ($columns, $unit): void {
                foreach (['SUBUNIT', 'UNIT', 'INSTALASI', 'RUANGAN'] as $column) {
                    if (in_array($column, $columns, true)) {
                        $builder->orWhere($column, $unit);
                    }
                }
            });
        }

        if (filled($period['payment'] ?? null)) {
            $payment = $period['payment'];
            $paymentColumns = ['CARABAYAR', 'CARA_BAYAR', 'JENIS_BAYAR', 'PENJAMIN'];
            $availablePaymentColumns = collect($paymentColumns)
                ->filter(fn(string $column): bool => in_array($column, $columns, true));

            if ($availablePaymentColumns->isNotEmpty()) {
                $query->where(function (Builder $builder) use ($payment, $availablePaymentColumns): void {
                    foreach ($availablePaymentColumns as $column) {
                        $builder->orWhere($column, $payment);
                    }
                });
            } else {
                // Jangan mengklaim data terfilter jika tabel sumber tidak memiliki
                // kolom metode pembayaran yang dapat digunakan.
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }

    protected function valueTotal(string $table, array $period): float|int
    {
        if (!$this->exists($table)) {
            return 0;
        }

        $query = $this->filteredQuery($table, $period);

        return in_array('VALUE', SimgosData::columns($table), true)
            ? (float) $query->sum('VALUE')
            : (int) $query->count();
    }

    protected function valueTotalMany(array $tables, array $period): float
    {
        return (float) collect($tables)
            ->sum(fn(string $table): float|int => $this->valueTotal($table, $period));
    }

    protected function rowCount(string $table, array $period): int
    {
        return $this->exists($table)
            ? (int) $this->filteredQuery($table, $period)->count()
            : 0;
    }

    protected function rowCountMany(array $tables, array $period): int
    {
        return (int) collect($tables)
            ->sum(fn(string $table): int => $this->rowCount($table, $period));
    }

    protected function topBy(
        string $table,
        ?string $column,
        array $period,
        bool $sumValue = true,
        int $limit = 10
    ): array {
        if (!$column || !$this->exists($table) || !in_array($column, SimgosData::columns($table), true)) {
            return [];
        }

        $columns = SimgosData::columns($table);
        $expression = $sumValue && in_array('VALUE', $columns, true)
            ? 'SUM(`VALUE`)'
            : 'COUNT(*)';

        return $this->filteredQuery($table, $period)
            ->select($column)
            ->selectRaw($expression . ' AS total')
            ->whereNotNull($column)
            ->where($column, '<>', '')
            ->groupBy($column)
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn($row): array => [
                'label' => (string) $row->{$column},
                'total' => (float) $row->total,
            ])
            ->values()
            ->all();
    }

    protected function topDescriptions(array $tables, array $period, int $limit = 10): array
    {
        $values = [];

        foreach ($tables as $table) {
            if (!$this->exists($table) || !in_array('DESKRIPSI', SimgosData::columns($table), true)) {
                continue;
            }

            $expression = in_array('VALUE', SimgosData::columns($table), true)
                ? 'SUM(`VALUE`)'
                : 'COUNT(*)';

            $rows = $this->filteredQuery($table, $period)
                ->select('DESKRIPSI')
                ->selectRaw($expression . ' AS total')
                ->whereNotNull('DESKRIPSI')
                ->where('DESKRIPSI', '<>', '')
                ->groupBy('DESKRIPSI')
                ->get();

            foreach ($rows as $row) {
                $label = (string) $row->DESKRIPSI;
                $values[$label] = ($values[$label] ?? 0) + (float) $row->total;
            }
        }

        arsort($values);

        return collect(array_slice($values, 0, $limit, true))
            ->map(fn(float $total, string $label): array => compact('label', 'total'))
            ->values()
            ->all();
    }

    protected function dailyTotal(string $table, array $period): array
    {
        if (!$this->exists($table) || !in_array('TANGGAL', SimgosData::columns($table), true)) {
            return [];
        }

        $expression = in_array('VALUE', SimgosData::columns($table), true)
            ? 'SUM(`VALUE`)'
            : 'COUNT(*)';

        return $this->filteredQuery($table, $period)
            ->selectRaw('DATE(`TANGGAL`) AS date')
            ->selectRaw($expression . ' AS total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($row): array => [
                'date' => (string) $row->date,
                'total' => (float) $row->total,
            ])
            ->values()
            ->all();
    }

    protected function columnTotals(string $table, array $columns, array $period): array
    {
        if (!$this->exists($table)) {
            return [];
        }

        $available = SimgosData::columns($table);
        $query = $this->filteredQuery($table, $period);
        $result = [];

        foreach ($columns as $column) {
            if (in_array($column, $available, true)) {
                $result[$column] = (float) $query->sum($column);
            }
        }

        return $result;
    }

    protected function groupColumn(string $table): ?string
    {
        foreach (['SUBUNIT', 'UNIT', 'INSTALASI'] as $column) {
            if (in_array($column, SimgosData::columns($table), true)) {
                return $column;
            }
        }

        return null;
    }
}
