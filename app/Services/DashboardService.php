<?php

namespace App\Services;

use App\Support\SimgosData;
use Illuminate\Database\Eloquent\Builder;

class DashboardService
{
    /**
     * Fact tables in the SIMGOS informasi database used for the dashboard.
     * All reads are guarded by the live schema and never write to the database.
     */
    private const SOURCES = [
        'kunjungan' => ['label' => 'Kunjungan', 'value' => 'VALUE'],
        'pengunjung' => ['label' => 'Pengunjung', 'value' => 'VALUE'],
        'penunjang' => ['label' => 'Penunjang', 'value' => 'VALUE'],
        'diagnosa_rd' => ['label' => 'Diagnosa Rawat Darurat', 'value' => 'VALUE'],
        'diagnosa_ri' => ['label' => 'Diagnosa Rawat Inap', 'value' => 'VALUE'],
        'diagnosa_rj' => ['label' => 'Diagnosa Rawat Jalan', 'value' => 'VALUE'],
        'klaim_iks' => ['label' => 'Klaim IKS', 'value' => 'VALUE'],
        'klaim_inacbg' => ['label' => 'Klaim INA-CBG', 'value' => 'VALUE'],
        'klaim_inacbg_ri' => ['label' => 'Klaim INA-CBG RI', 'value' => 'VALUE'],
        'klaim_inacbg_rj' => ['label' => 'Klaim INA-CBG RJ', 'value' => 'VALUE'],
        'pasien_rawat_inap' => ['label' => 'Pasien Rawat Inap', 'value' => 'VALUE'],
        'pendapatan' => ['label' => 'Pendapatan', 'value' => 'VALUE'],
        'penerimaan' => ['label' => 'Penerimaan', 'value' => 'VALUE'],
        'indikator_rs' => ['label' => 'Indikator RS', 'value' => null],
        'nedocs_igd' => ['label' => 'NEDOCS IGD', 'value' => null],
        'statistik_kunjungan' => ['label' => 'Statistik Kunjungan', 'value' => null],
        'statistik_rujukan' => ['label' => 'Statistik Rujukan', 'value' => null],
    ];

    public function overview(array $filters): array
    {
        $empty = $this->emptyOverview();

        if (!$this->canConnect()) {
            $empty['dataWarning'] = 'Data belum dapat dibaca dari database. Periksa koneksi database pada file .env.';

            return $empty;
        }

        try {
            $trend = [];
            $category = [];
            $total = 0;
            $today = 0;
            $month = 0;
            $lastUpdated = null;

            foreach ($this->existingSources($filters) as $table => $definition) {
                $columns = SimgosData::columns($table);
                if (!in_array('TANGGAL', $columns, true)) {
                    continue;
                }

                $base = $this->filteredQuery($table, $filters, $columns);
                $total += $this->measure($base, $definition, $columns);

                $todayQuery = $this->filteredQuery($table, $filters, $columns);
                $today += $this->measure($todayQuery->whereDate('TANGGAL', now()->toDateString()), $definition, $columns);

                $monthQuery = $this->filteredQuery($table, $filters, $columns);
                $month += $this->measure($monthQuery->whereBetween('TANGGAL', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]), $definition, $columns);

                $trendRows = $this->filteredQuery($table, $filters, $columns)
                    ->selectRaw('DATE(`TANGGAL`) AS report_date')
                    ->selectRaw($this->measureExpression($definition, $columns) . ' AS report_total')
                    ->groupBy('report_date')
                    ->get();

                foreach ($trendRows as $row) {
                    $date = (string) $row->report_date;
                    $trend[$date] = ($trend[$date] ?? 0) + (int) $row->report_total;
                }

                $category[] = ['label' => $definition['label'], 'value' => $this->measure($base, $definition, $columns)];

                $updatedColumn = in_array('LASTUPDATED', $columns, true) ? 'LASTUPDATED' : (in_array('LAST_UPDATE', $columns, true) ? 'LAST_UPDATE' : 'TANGGAL');
                $updated = $this->filteredQuery($table, $filters, $columns)->max($updatedColumn);
                if ($updated && ($lastUpdated === null || (string) $updated > (string) $lastUpdated)) {
                    $lastUpdated = (string) $updated;
                }
            }

            ksort($trend);
            $trend = array_slice($trend, -14, null, true);

            $empty['stats'] = [
                $this->stat('Total Data', $total, 'Agregasi seluruh sumber aktif', 'fa-database', 'primary'),
                $this->stat('Data Hari Ini', $today, 'Agregasi tanggal hari ini', 'fa-arrow-trend-up', 'success'),
                $this->stat('Data Bulan Ini', $month, 'Agregasi bulan berjalan', 'fa-calendar-days', 'info'),
                $this->stat('Kategori', count($category), 'Sumber data aktif', 'fa-tags', 'warning'),
            ];
            $empty['charts'] = [
                'trend' => ['labels' => array_map(fn(string $date): string => date('d M', strtotime($date)), array_keys($trend)), 'data' => array_values(array_map('intval', $trend))],
                'category' => ['labels' => array_column($category, 'label'), 'data' => array_map('intval', array_column($category, 'value'))],
                'distribution' => $this->distribution($filters),
                'unit' => $this->units($filters),
            ];
            $empty['lastUpdated'] = $lastUpdated ? date('d M Y H:i', strtotime($lastUpdated)) : null;
        } catch (\Throwable $exception) {
            report($exception);
            $empty['dataWarning'] = 'Data belum dapat dibaca dari database. Periksa struktur tabel dan koneksi database.';
        }

        return $empty;
    }

    private function canConnect(): bool
    {
        try {
            SimgosData::connection()->getPdo();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function existingSources(array $filters): array
    {
        return array_filter(self::SOURCES, function (array $definition, string $table) use ($filters): bool {
            if (!SimgosData::tableExists($table)) {
                return false;
            }

            return match ($filters['category'] ?? '') {
                'diagnosa' => str_starts_with($table, 'diagnosa_'),
                'kunjungan' => in_array($table, ['kunjungan', 'pengunjung'], true),
                'pelayanan' => in_array($table, ['kunjungan', 'penunjang'], true),
                default => true,
            };
        }, ARRAY_FILTER_USE_BOTH);
    }

    private function filteredQuery(string $table, array $filters, array $columns): Builder
    {
        $query = SimgosData::query($table)->whereBetween('TANGGAL', [$filters['date_from'], $filters['date_to']]);

        if ($table === 'kunjungan') {
            $unit = match ($filters['unit'] ?? '') {
                'rawat-jalan' => 'Rawat Jalan',
                'rawat-inap' => 'Rawat Inap',
                'igd' => 'IGD',
                default => null,
            };
            if ($unit !== null) {
                $query->where(function ($builder) use ($unit, $columns): void {
                    foreach (['DESKRIPSI', 'INSTALASI', 'UNIT', 'SUBUNIT'] as $column) {
                        if (in_array($column, $columns, true)) {
                            $builder->orWhere($column, 'like', '%' . $unit . '%');
                        }
                    }
                });
            }

            $room = match ($filters['room'] ?? '') {
                'ruang-a' => 'Ruang A',
                'ruang-b' => 'Ruang B',
                'igd' => 'IGD',
                default => null,
            };
            if ($room !== null) {
                $query->where(function ($builder) use ($room, $columns): void {
                    foreach (['UNIT', 'SUBUNIT'] as $column) {
                        if (in_array($column, $columns, true)) {
                            $builder->orWhere($column, 'like', '%' . $room . '%');
                        }
                    }
                });
            }
        }

        return $query;
    }

    private function measure(Builder $query, array $definition, array $columns): int
    {
        return (int) $query->selectRaw($this->measureExpression($definition, $columns) . ' AS aggregate_total')->value('aggregate_total');
    }

    private function measureExpression(array $definition, array $columns): string
    {
        // Sources contain different meanings for VALUE (volume, amount, score).
        // Counting records keeps cross-source dashboard totals comparable.
        return 'COUNT(*)';
    }

    private function distribution(array $filters): array
    {
        if (!SimgosData::tableExists('kunjungan')) {
            return ['labels' => [], 'data' => []];
        }
        $columns = SimgosData::columns('kunjungan');
        if (!in_array('INSTALASI', $columns, true)) {
            return ['labels' => [], 'data' => []];
        }
        $rows = $this->filteredQuery('kunjungan', $filters, $columns)
            ->select('INSTALASI')->selectRaw($this->measureExpression(self::SOURCES['kunjungan'], $columns) . ' AS aggregate_total')
            ->whereNotNull('INSTALASI')->where('INSTALASI', '<>', '')->groupBy('INSTALASI')->orderByDesc('aggregate_total')->get();

        return ['labels' => $rows->pluck('INSTALASI')->values()->all(), 'data' => $rows->pluck('aggregate_total')->map(fn($value): int => (int) $value)->values()->all()];
    }

    private function units(array $filters): array
    {
        if (!SimgosData::tableExists('kunjungan')) {
            return ['labels' => [], 'data' => []];
        }
        $columns = SimgosData::columns('kunjungan');
        $groupColumn = in_array('SUBUNIT', $columns, true) ? 'SUBUNIT' : (in_array('UNIT', $columns, true) ? 'UNIT' : null);
        if (!$groupColumn) {
            return ['labels' => [], 'data' => []];
        }
        $rows = $this->filteredQuery('kunjungan', $filters, $columns)
            ->select($groupColumn)->selectRaw($this->measureExpression(self::SOURCES['kunjungan'], $columns) . ' AS aggregate_total')
            ->whereNotNull($groupColumn)->where($groupColumn, '<>', '')->groupBy($groupColumn)->orderByDesc('aggregate_total')->limit(10)->get();

        return ['labels' => $rows->pluck($groupColumn)->values()->all(), 'data' => $rows->pluck('aggregate_total')->map(fn($value): int => (int) $value)->values()->all()];
    }

    private function stat(string $label, int $value, string $description, string $icon, string $tone): array
    {
        return ['label' => $label, 'value' => number_format($value, 0, ',', '.'), 'description' => $description, 'icon' => $icon, 'tone' => $tone];
    }

    private function emptyOverview(): array
    {
        return [
            'stats' => [$this->stat('Total Data', 0, 'Belum ada data', 'fa-database', 'primary'), $this->stat('Data Hari Ini', 0, 'Belum ada data', 'fa-arrow-trend-up', 'success'), $this->stat('Data Bulan Ini', 0, 'Belum ada data', 'fa-calendar-days', 'info'), $this->stat('Kategori', 0, 'Belum ada data', 'fa-tags', 'warning')],
            'charts' => ['trend' => ['labels' => [], 'data' => []], 'category' => ['labels' => [], 'data' => []], 'distribution' => ['labels' => [], 'data' => []], 'unit' => ['labels' => [], 'data' => []]],
            'lastUpdated' => null,
            'dataWarning' => null,
        ];
    }
}
