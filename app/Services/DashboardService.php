<?php

namespace App\Services;

use App\Support\SimgosData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DashboardService
{
    /**
     * Business datasets. These definitions are only a read-only map over the
     * existing SIMGOS tables; they do not create or modify any table.
     */
    private const DATASETS = [
        'operasional' => [
            'label' => 'Kunjungan & Pelayanan',
            'description' => 'Aktivitas kunjungan, pasien, IGD, dan pelayanan penunjang.',
            'icon' => 'fa-hospital-user',
            'tone' => 'primary',
            'tables' => ['kunjungan', 'pengunjung', 'pasien_rawat_inap', 'penunjang', 'nedocs_igd', 'tempat_tidur_kemkes'],
        ],
        'klinis' => [
            'label' => 'Klinis & Statistik',
            'description' => 'Diagnosa, penyakit terbanyak, golongan darah, dan statistik klinis.',
            'icon' => 'fa-stethoscope',
            'tone' => 'info',
            'tables' => ['diagnosa_rd', 'diagnosa_ri', 'diagnosa_rj', 'statistik_10_besar_diagnosa_rujukan', 'statistik_10_besar_penyakit', 'statistik_gol_darah', 'statistik_kunjungan', 'statistik_jumlah_kematian'],
        ],
        'keuangan' => [
            'label' => 'Keuangan',
            'description' => 'Ringkasan pendapatan dan penerimaan pelayanan.',
            'icon' => 'fa-wallet',
            'tone' => 'success',
            'tables' => ['pendapatan', 'penerimaan'],
        ],
        'klaim' => [
            'label' => 'Klaim & Pembiayaan',
            'description' => 'Klaim IKS dan INA-CBG berdasarkan jenis pelayanan.',
            'icon' => 'fa-file-invoice-dollar',
            'tone' => 'warning',
            'tables' => ['klaim_iks', 'klaim_inacbg', 'klaim_inacbg_ri', 'klaim_inacbg_rj'],
        ],
        'mutu' => [
            'label' => 'Mutu & Rujukan',
            'description' => 'Indikator rumah sakit, mutu pelayanan, pemeriksaan, dan rujukan.',
            'icon' => 'fa-chart-line',
            'tone' => 'primary',
            'tables' => ['indikator_rs', 'statistik_indikator', 'statistik_mutu_pelayanan', 'statistik_pemeriksaan_laboratorium', 'statistik_rujukan'],
        ],
        'pengalaman' => [
            'label' => 'Pengalaman Pasien',
            'description' => 'Komentar dan konfirmasi layanan pasien fast track.',
            'icon' => 'fa-comment-medical',
            'tone' => 'info',
            'tables' => ['komentar_pasien_fast_track', 'konfirmasi_pasien_fast_track'],
        ],
    ];

    public function overview(array $filters): array
    {
        $empty = $this->emptyOverview();

        if (!$this->canConnect()) {
            $empty['dataWarning'] = 'Data belum dapat dibaca dari database. Periksa koneksi database pada file .env.';

            return $empty;
        }

        try {
            $empty['filterOptions'] = ['units' => $this->unitOptions()];
            $empty['datasetCards'] = $this->datasetCards($filters);

            $visitTotal = $this->sourceValue('kunjungan', $filters);
            $visitorTotal = $this->sourceValue('pengunjung', $filters);
            $inpatientTotal = $this->sourceValue('pasien_rawat_inap', $filters);
            $revenueTotal = $this->sourceValue('pendapatan', $filters);
            $receiptTotal = $this->sourceValue('penerimaan', $filters);

            $empty['stats'] = [
                $this->stat('Total Kunjungan', $this->formatNumber($visitTotal), 'Dataset Kunjungan', 'fa-hospital-user', 'primary'),
                $this->stat('Total Pengunjung', $this->formatNumber($visitorTotal), 'Dataset Pengunjung', 'fa-users', 'success'),
                $this->stat('Pasien Rawat Inap', $this->formatNumber($inpatientTotal), 'Dataset Pasien Rawat Inap', 'fa-bed', 'info'),
                $this->stat('Total Pendapatan', $this->formatCurrency($revenueTotal), 'Agregasi tabel Pendapatan', 'fa-money-bill-wave', 'warning'),
            ];

            $empty['summary'] = [
                'revenue' => $this->formatCurrency($revenueTotal),
                'receipt' => $this->formatCurrency($receiptTotal),
            ];
            $empty['charts'] = [
                'trend' => $this->trendChart('kunjungan', $filters),
                'installation' => $this->groupedChart('kunjungan', 'INSTALASI', $filters, 8),
                'payment' => $this->groupedChart('kunjungan', 'CARABAYAR', $filters, 8),
                'finance' => $this->financeChart($filters),
                'unit' => $this->groupedChart('kunjungan', $this->availableUnitColumn(), $filters, 10),
            ];
            $empty['lastUpdated'] = $this->lastUpdated($filters);
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

    private function datasetCards(array $filters): array
    {
        return collect(self::DATASETS)->map(function (array $dataset) use ($filters): array {
            $sources = collect($dataset['tables'])
                ->filter(fn(string $table): bool => SimgosData::tableExists($table))
                ->values();
            $rows = $sources->sum(fn(string $table): int => $this->sourceRows($table, $filters));

            return [
                'label' => $dataset['label'],
                'description' => $dataset['description'],
                'icon' => $dataset['icon'],
                'tone' => $dataset['tone'],
                'source_count' => $sources->count(),
                'table_count' => count($dataset['tables']),
                'rows' => $this->formatNumber($rows),
            ];
        })->values()->all();
    }

    private function sourceRows(string $table, array $filters): int
    {
        if (!SimgosData::tableExists($table)) {
            return 0;
        }

        return (int) $this->filteredQuery($table, $filters)->count();
    }

    private function sourceValue(string $table, array $filters): float
    {
        if (!SimgosData::tableExists($table)) {
            return 0;
        }

        $columns = SimgosData::columns($table);
        $query = $this->filteredQuery($table, $filters);

        if (in_array('VALUE', $columns, true)) {
            return (float) $query->sum('VALUE');
        }

        return (float) $query->count();
    }

    private function filteredQuery(string $table, array $filters): Builder
    {
        $columns = SimgosData::columns($table);
        $query = SimgosData::query($table);

        if (in_array('TANGGAL', $columns, true)) {
            $query->whereBetween('TANGGAL', [$filters['date_from'], $filters['date_to']]);
        } elseif (in_array('TAHUN', $columns, true)) {
            $from = Carbon::parse($filters['date_from']);
            $to = Carbon::parse($filters['date_to']);
            $query->whereBetween('TAHUN', [$from->year, $to->year]);
        }

        if ($table === 'kunjungan' && filled($filters['unit'])) {
            $query->where(function (Builder $builder) use ($filters, $columns): void {
                foreach (['INSTALASI', 'UNIT', 'SUBUNIT'] as $column) {
                    if (in_array($column, $columns, true)) {
                        $builder->orWhere($column, $filters['unit']);
                    }
                }
            });
        }

        return $query;
    }

    private function trendChart(string $table, array $filters): array
    {
        if (!SimgosData::tableExists($table) || !in_array('TANGGAL', SimgosData::columns($table), true)) {
            return ['labels' => [], 'data' => []];
        }

        $columns = SimgosData::columns($table);
        $rows = $this->filteredQuery($table, $filters)
            ->selectRaw('DATE(`TANGGAL`) AS report_date')
            ->selectRaw($this->measureExpression($columns) . ' AS report_total')
            ->groupBy('report_date')
            ->orderBy('report_date')
            ->get();

        return [
            'labels' => $rows->map(fn($row): string => Carbon::parse($row->report_date)->format('d M'))->values()->all(),
            'data' => $rows->map(fn($row): float => (float) $row->report_total)->values()->all(),
        ];
    }

    private function groupedChart(string $table, ?string $column, array $filters, int $limit): array
    {
        if (!$column || !SimgosData::tableExists($table)) {
            return ['labels' => [], 'data' => []];
        }

        $columns = SimgosData::columns($table);
        if (!in_array($column, $columns, true)) {
            return ['labels' => [], 'data' => []];
        }

        $rows = $this->filteredQuery($table, $filters)
            ->select($column)
            ->selectRaw($this->measureExpression($columns) . ' AS report_total')
            ->whereNotNull($column)
            ->where($column, '<>', '')
            ->groupBy($column)
            ->orderByDesc('report_total')
            ->limit($limit)
            ->get();

        return [
            'labels' => $rows->pluck($column)->map(fn($value): string => (string) $value)->values()->all(),
            'data' => $rows->pluck('report_total')->map(fn($value): float => (float) $value)->values()->all(),
        ];
    }

    private function financeChart(array $filters): array
    {
        $series = [];

        foreach (['pendapatan' => 'Pendapatan', 'penerimaan' => 'Penerimaan'] as $table => $label) {
            if (!SimgosData::tableExists($table) || !in_array('TANGGAL', SimgosData::columns($table), true)) {
                continue;
            }

            $rows = $this->filteredQuery($table, $filters)
                ->selectRaw('DATE(`TANGGAL`) AS report_date')
                ->selectRaw('SUM(`VALUE`) AS report_total')
                ->groupBy('report_date')
                ->orderBy('report_date')
                ->get();

            foreach ($rows as $row) {
                $date = (string) $row->report_date;
                $series[$date][$label] = (float) $row->report_total;
            }
        }

        $dates = array_keys($series);
        sort($dates);

        return [
            'labels' => array_map(fn(string $date): string => Carbon::parse($date)->format('d M'), $dates),
            'datasets' => [
                [
                    'label' => 'Pendapatan',
                    'data' => array_map(fn(string $date): float => $series[$date]['Pendapatan'] ?? 0, $dates),
                    'backgroundColor' => '#027d78',
                    'borderColor' => '#027d78',
                    'borderRadius' => 5,
                ],
                [
                    'label' => 'Penerimaan',
                    'data' => array_map(fn(string $date): float => $series[$date]['Penerimaan'] ?? 0, $dates),
                    'backgroundColor' => '#93c5c1',
                    'borderColor' => '#93c5c1',
                    'borderRadius' => 5,
                ],
            ],
        ];
    }

    private function unitOptions(): array
    {
        if (!SimgosData::tableExists('kunjungan')) {
            return [];
        }

        $columns = SimgosData::columns('kunjungan');
        $values = collect();

        foreach (['INSTALASI', 'UNIT', 'SUBUNIT'] as $column) {
            if (in_array($column, $columns, true)) {
                $values = $values->merge(SimgosData::query('kunjungan')->whereNotNull($column)->where($column, '<>', '')->distinct()->pluck($column));
            }
        }

        return $values->map(fn($value): string => (string) $value)->unique()->sort()->values()->all();
    }

    private function availableUnitColumn(): ?string
    {
        $columns = SimgosData::columns('kunjungan');

        return in_array('SUBUNIT', $columns, true) ? 'SUBUNIT' : (in_array('UNIT', $columns, true) ? 'UNIT' : 'INSTALASI');
    }

    private function lastUpdated(array $filters): ?string
    {
        $latest = null;

        foreach (self::DATASETS as $dataset) {
            foreach ($dataset['tables'] as $table) {
                if (!SimgosData::tableExists($table)) {
                    continue;
                }

                $columns = SimgosData::columns($table);
                $column = in_array('LASTUPDATED', $columns, true)
                    ? 'LASTUPDATED'
                    : (in_array('TANGGAL_UPDATED', $columns, true) ? 'TANGGAL_UPDATED' : (in_array('TANGGAL', $columns, true) ? 'TANGGAL' : null));

                if ($column) {
                    $value = $this->filteredQuery($table, $filters)->max($column);
                    if ($value && ($latest === null || (string) $value > (string) $latest)) {
                        $latest = (string) $value;
                    }
                }
            }
        }

        return $latest ? Carbon::parse($latest)->format('d M Y H:i') : null;
    }

    private function measureExpression(array $columns): string
    {
        return in_array('VALUE', $columns, true) ? 'SUM(`VALUE`)' : 'COUNT(*)';
    }

    private function formatNumber(float|int $value): string
    {
        return number_format($value, 0, ',', '.');
    }

    private function formatCurrency(float|int $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    private function stat(string $label, string $value, string $description, string $icon, string $tone): array
    {
        return compact('label', 'value', 'description', 'icon', 'tone');
    }

    private function emptyOverview(): array
    {
        return [
            'stats' => [
                $this->stat('Total Kunjungan', '0', 'Belum ada data', 'fa-hospital-user', 'primary'),
                $this->stat('Total Pengunjung', '0', 'Belum ada data', 'fa-users', 'success'),
                $this->stat('Pasien Rawat Inap', '0', 'Belum ada data', 'fa-bed', 'info'),
                $this->stat('Total Pendapatan', 'Rp 0', 'Belum ada data', 'fa-money-bill-wave', 'warning'),
            ],
            'datasetCards' => [],
            'filterOptions' => ['units' => []],
            'summary' => ['revenue' => 'Rp 0', 'receipt' => 'Rp 0'],
            'charts' => [
                'trend' => ['labels' => [], 'data' => []],
                'installation' => ['labels' => [], 'data' => []],
                'payment' => ['labels' => [], 'data' => []],
                'finance' => ['labels' => [], 'datasets' => []],
                'unit' => ['labels' => [], 'data' => []],
            ],
            'lastUpdated' => null,
            'dataWarning' => null,
        ];
    }
}
