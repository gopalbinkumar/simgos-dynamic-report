<?php

namespace App\Services;

use App\Support\SimgosData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class AnalyticsService
{
    private const SECTIONS = [
        'diagnosa' => [
            'title' => 'Diagnosa',
            'eyebrow' => 'DATA KLINIS',
            'description' => 'Analisis diagnosa berdasarkan rawat jalan, rawat inap, rawat darurat, dan rujukan.',
            'icon' => 'fa-stethoscope',
            'tables' => ['diagnosa_rd', 'diagnosa_ri', 'diagnosa_rj', 'statistik_10_besar_penyakit', 'statistik_10_besar_diagnosa_rujukan'],
        ],
        'klaim' => [
            'title' => 'Klaim',
            'eyebrow' => 'DATA PEMBIAYAAN',
            'description' => 'Ringkasan klaim IKS dan INA-CBG berdasarkan pelayanan dan periode.',
            'icon' => 'fa-file-invoice-dollar',
            'tables' => ['klaim_iks', 'klaim_inacbg', 'klaim_inacbg_ri', 'klaim_inacbg_rj'],
        ],
        'pasien-kunjungan' => [
            'title' => 'Pasien & Kunjungan',
            'eyebrow' => 'DATA OPERASIONAL',
            'description' => 'Pemantauan kunjungan, pengunjung, rawat inap, unit, poli, dan cara bayar.',
            'icon' => 'fa-hospital-user',
            'tables' => ['kunjungan', 'pengunjung', 'pasien_rawat_inap'],
        ],
        'pelayanan-igd' => [
            'title' => 'Pelayanan & IGD',
            'eyebrow' => 'DATA PELAYANAN',
            'description' => 'Aktivitas penunjang, kepadatan IGD, tempat tidur, dan fast track.',
            'icon' => 'fa-truck-medical',
            'tables' => ['penunjang', 'nedocs_igd', 'tempat_tidur_kemkes', 'komentar_pasien_fast_track', 'konfirmasi_pasien_fast_track'],
        ],
        'keuangan' => [
            'title' => 'Keuangan',
            'eyebrow' => 'DATA KEUANGAN',
            'description' => 'Perbandingan pendapatan dan penerimaan pelayanan rumah sakit.',
            'icon' => 'fa-wallet',
            'tables' => ['pendapatan', 'penerimaan'],
        ],
        'statistik-indikator' => [
            'title' => 'Statistik & Indikator',
            'eyebrow' => 'DATA KINERJA',
            'description' => 'Indikator rumah sakit, mutu, rujukan, laboratorium, mortalitas, dan statistik kunjungan.',
            'icon' => 'fa-chart-line',
            'tables' => ['indikator_rs', 'statistik_indikator', 'statistik_kunjungan', 'statistik_rujukan', 'statistik_gol_darah', 'statistik_jumlah_kematian', 'statistik_mutu_pelayanan', 'statistik_pemeriksaan_laboratorium'],
        ],
    ];

    private const TABLE_LABELS = [
        'diagnosa_rd' => 'Diagnosa Rawat Darurat',
        'diagnosa_ri' => 'Diagnosa Rawat Inap',
        'diagnosa_rj' => 'Diagnosa Rawat Jalan',
        'statistik_10_besar_penyakit' => '10 Besar Penyakit',
        'statistik_10_besar_diagnosa_rujukan' => '10 Besar Diagnosa Rujukan',
        'klaim_iks' => 'Klaim IKS',
        'klaim_inacbg' => 'Klaim INA-CBG',
        'klaim_inacbg_ri' => 'Klaim INA-CBG Rawat Inap',
        'klaim_inacbg_rj' => 'Klaim INA-CBG Rawat Jalan',
        'kunjungan' => 'Kunjungan',
        'pengunjung' => 'Pengunjung',
        'pasien_rawat_inap' => 'Pasien Rawat Inap',
        'penunjang' => 'Pelayanan Penunjang',
        'nedocs_igd' => 'NEDOCS IGD',
        'tempat_tidur_kemkes' => 'Tempat Tidur',
        'komentar_pasien_fast_track' => 'Komentar Fast Track',
        'konfirmasi_pasien_fast_track' => 'Konfirmasi Fast Track',
        'pendapatan' => 'Pendapatan',
        'penerimaan' => 'Penerimaan',
        'indikator_rs' => 'Indikator Rumah Sakit',
        'statistik_indikator' => 'Statistik Indikator',
        'statistik_kunjungan' => 'Statistik Kunjungan',
        'statistik_rujukan' => 'Statistik Rujukan',
        'statistik_gol_darah' => 'Statistik Golongan Darah',
        'statistik_jumlah_kematian' => 'Statistik Jumlah Kematian',
        'statistik_mutu_pelayanan' => 'Statistik Mutu Pelayanan',
        'statistik_pemeriksaan_laboratorium' => 'Statistik Pemeriksaan Laboratorium',
    ];

    private const TABLE_COLUMNS = [
        'diagnosa_rd' => ['TANGGAL', 'ID', 'DESKRIPSI', 'VALUE', 'LASTUPDATED'],
        'diagnosa_ri' => ['TANGGAL', 'ID', 'DESKRIPSI', 'VALUE', 'LASTUPDATED'],
        'diagnosa_rj' => ['TANGGAL', 'ID', 'DESKRIPSI', 'VALUE', 'LASTUPDATED'],
        'klaim_iks' => ['TANGGAL', 'ID', 'DESKRIPSI', 'CARABAYAR', 'VALUE', 'LASTUPDATED'],
        'klaim_inacbg' => ['TANGGAL', 'ID', 'DESKRIPSI', 'CARABAYAR', 'VALUE', 'LASTUPDATED'],
        'klaim_inacbg_ri' => ['TANGGAL', 'ID', 'DESKRIPSI', 'VALUE', 'LASTUPDATED'],
        'klaim_inacbg_rj' => ['TANGGAL', 'ID', 'DESKRIPSI', 'VALUE', 'LASTUPDATED'],
        'kunjungan' => ['TANGGAL', 'DESKRIPSI', 'INSTALASI', 'UNIT', 'SUBUNIT', 'CARABAYAR', 'VALUE', 'LASTUPDATED'],
        'pengunjung' => ['TANGGAL', 'DESKRIPSI', 'CARABAYAR', 'VALUE', 'LASTUPDATED'],
        'pasien_rawat_inap' => ['TANGGAL', 'DESKRIPSI', 'CARABAYAR', 'VALUE', 'LASTUPDATED'],
        'penunjang' => ['TANGGAL', 'DESKRIPSI', 'INSTALASI', 'UNIT', 'SUBUNIT', 'CARABAYAR', 'VALUE', 'LASTUPDATED'],
        'nedocs_igd' => ['TANGGAL', 'RUANGAN', 'UNIT', 'SKOR', 'LEVEL_DESKRIPSI', 'WARNA', 'STATUS', 'LAST_UPDATE'],
        'tempat_tidur_kemkes' => ['INSTALASI', 'UNIT', 'SUBUNIT', 'KAMAR', 'KELAS', 'TTLAKI', 'TTPEREMPUAN', 'JMLLAKI', 'JMLPEREMPUAN', 'TERPAKAI_KONFIRMASI', 'LASTUPDATED'],
        'komentar_pasien_fast_track' => ['ID', 'KUNJUNGAN', 'KOMENTAR', 'OLEH', 'TANGGAL', 'REPLY_FROM'],
        'konfirmasi_pasien_fast_track' => ['ID', 'KUNJUNGAN', 'STATUS_KONFIRMASI', 'KONFIRMASI_KE', 'CATATAN', 'TANGGAL', 'OLEH', 'UPDATE_TIME', 'STATUS'],
        'pendapatan' => ['TANGGAL', 'ID', 'DESKRIPSI', 'CARABAYAR', 'VALUE', 'LASTUPDATED'],
        'penerimaan' => ['TANGGAL', 'ID', 'DESKRIPSI', 'SHORTDESK', 'CARABAYAR', 'VALUE', 'LASTUPDATED'],
        'indikator_rs' => ['TANGGAL', 'AWAL', 'MASUK', 'SISA', 'TTIDUR', 'HP', 'JMLKLR', 'JMLHARI', 'LASTUPDATED'],
        'statistik_indikator' => ['TAHUN', 'PERIODE', 'BOR', 'ALOS', 'BTO', 'TOI', 'NDR', 'GDR', 'TANGGAL_UPDATED'],
        'statistik_kunjungan' => ['TANGGAL', 'RJ', 'RD', 'RI', 'TANGGAL_UPDATED'],
        'statistik_rujukan' => ['TANGGAL', 'MASUK', 'KELUAR', 'BALIK', 'TANGGAL_UPDATED'],
        'statistik_gol_darah' => ['TAHUN', 'BULAN', 'KODE', 'JUMLAH_PASIEN', 'TANGGAL_UPDATED'],
        'statistik_jumlah_kematian' => ['TAHUN', 'BULAN', 'KONTEN', 'TANGGAL_UPDATED'],
        'statistik_mutu_pelayanan' => ['TAHUN', 'BULAN', 'KODE', 'NILAI', 'MANUAL', 'TANGGAL_UPDATED'],
        'statistik_pemeriksaan_laboratorium' => ['TAHUN', 'BULAN', 'KODE', 'RATA_RATA', 'JUMLAH_PASIEN', 'TANGGAL_UPDATED'],
        'statistik_10_besar_penyakit' => ['TAHUN', 'BULAN', 'JENIS_PELAYANAN', 'KONTEN', 'TANGGAL_UPDATED'],
        'statistik_10_besar_diagnosa_rujukan' => ['TAHUN', 'BULAN', 'JENIS_RUJUKAN', 'KONTEN', 'TANGGAL_UPDATED'],
    ];

    public function page(string $section, array $filters): array
    {
        $config = self::SECTIONS[$section];

        return [
            'section' => $config + ['key' => $section],
            'filters' => $filters,
            'filterOptions' => [
                'units' => $this->options(['kunjungan', 'penunjang', 'tempat_tidur_kemkes'], ['INSTALASI', 'UNIT', 'SUBUNIT']),
                'payments' => $this->options(['kunjungan', 'pendapatan', 'penerimaan', 'klaim_iks'], ['CARABAYAR']),
            ],
            'stats' => $this->stats($section, $filters),
            'charts' => $this->charts($section, $filters),
            'sources' => $this->sources($config['tables'], $filters),
            'navigation' => collect(self::SECTIONS)->map(fn(array $item, string $key): array => ['key' => $key, 'label' => $item['title']])->values()->all(),
        ];
    }

    private function stats(string $section, array $filters): array
    {
        return match ($section) {
            'diagnosa' => [
                $this->stat('Total Diagnosa', $this->number($this->sumValues(['diagnosa_rd', 'diagnosa_ri', 'diagnosa_rj'], $filters)), 'Gabungan tiga layanan', 'fa-stethoscope', 'primary'),
                $this->stat('Rawat Jalan', $this->number($this->sumValues(['diagnosa_rj'], $filters)), 'Dataset diagnosa RJ', 'fa-person-walking', 'info'),
                $this->stat('Rawat Inap', $this->number($this->sumValues(['diagnosa_ri'], $filters)), 'Dataset diagnosa RI', 'fa-bed', 'success'),
                $this->stat('Rawat Darurat', $this->number($this->sumValues(['diagnosa_rd'], $filters)), 'Dataset diagnosa RD', 'fa-truck-medical', 'warning'),
            ],
            'klaim' => [
                $this->stat('Volume Klaim', $this->number($this->sumValues(['klaim_iks', 'klaim_inacbg', 'klaim_inacbg_ri', 'klaim_inacbg_rj'], $filters)), 'Nilai VALUE pada tabel klaim', 'fa-file-invoice-dollar', 'primary'),
                $this->stat('Baris Klaim', $this->number($this->rows(['klaim_iks', 'klaim_inacbg', 'klaim_inacbg_ri', 'klaim_inacbg_rj'], $filters)), 'Data klaim terbaca', 'fa-list-check', 'info'),
                $this->stat('INA-CBG RJ', $this->number($this->sumValues(['klaim_inacbg_rj'], $filters)), 'Klaim rawat jalan', 'fa-person-walking', 'success'),
                $this->stat('INA-CBG RI', $this->number($this->sumValues(['klaim_inacbg_ri'], $filters)), 'Klaim rawat inap', 'fa-bed', 'warning'),
            ],
            'pasien-kunjungan' => [
                $this->stat('Total Kunjungan', $this->number($this->sumValues(['kunjungan'], $filters)), 'Dataset kunjungan', 'fa-hospital-user', 'primary'),
                $this->stat('Total Pengunjung', $this->number($this->sumValues(['pengunjung'], $filters)), 'Dataset pengunjung', 'fa-users', 'success'),
                $this->stat('Pasien Rawat Inap', $this->number($this->sumValues(['pasien_rawat_inap'], $filters)), 'Dataset pasien rawat inap', 'fa-bed', 'info'),
                $this->stat('Unit / Poli', $this->number($this->distinctValues('kunjungan', ['UNIT', 'SUBUNIT'], $filters)), 'Unit yang memiliki aktivitas', 'fa-sitemap', 'warning'),
            ],
            'pelayanan-igd' => [
                $this->stat('Pelayanan Penunjang', $this->number($this->sumValues(['penunjang'], $filters)), 'Volume pelayanan penunjang', 'fa-flask', 'primary'),
                $this->stat('Aktivitas IGD', $this->number($this->rows(['nedocs_igd'], $filters)), 'Pengukuran NEDOCS', 'fa-truck-medical', 'warning'),
                $this->stat('Total Tempat Tidur', $this->number($this->bedTotal($filters)), 'Kapasitas dari tabel tempat tidur', 'fa-bed', 'info'),
                $this->stat('Feedback Fast Track', $this->number($this->rows(['komentar_pasien_fast_track', 'konfirmasi_pasien_fast_track'], $filters)), 'Komentar dan konfirmasi', 'fa-comment-medical', 'success'),
            ],
            'keuangan' => [
                $this->stat('Total Pendapatan', $this->currency($this->sumValues(['pendapatan'], $filters)), 'Tabel pendapatan', 'fa-money-bill-wave', 'primary'),
                $this->stat('Total Penerimaan', $this->currency($this->sumValues(['penerimaan'], $filters)), 'Tabel penerimaan', 'fa-cash-register', 'success'),
                $this->stat('Data Pendapatan', $this->number($this->rows(['pendapatan'], $filters)), 'Baris transaksi terbaca', 'fa-receipt', 'info'),
                $this->stat('Data Penerimaan', $this->number($this->rows(['penerimaan'], $filters)), 'Baris transaksi terbaca', 'fa-file-invoice', 'warning'),
            ],
            'statistik-indikator' => $this->indicatorStats($filters),
        };
    }

    private function indicatorStats(array $filters): array
    {
        $indicator = $this->latest('statistik_indikator', $filters);

        return [
            $this->stat('BOR Terbaru', $this->decimal($indicator?->BOR), 'Statistik indikator terbaru', 'fa-chart-line', 'primary'),
            $this->stat('ALOS Terbaru', $this->decimal($indicator?->ALOS), 'Average Length of Stay', 'fa-calendar-days', 'info'),
            $this->stat('Rujukan Masuk', $this->number($this->sumColumn('statistik_rujukan', 'MASUK', $filters)), 'Agregasi periode terpilih', 'fa-arrow-right-to-bracket', 'success'),
            $this->stat('Jumlah Kematian', $this->number($this->deathTotal($filters)), 'Dari konten statistik kematian', 'fa-heart-pulse', 'warning'),
        ];
    }

    private function charts(string $section, array $filters): array
    {
        return match ($section) {
            'diagnosa' => [
                $this->trendChart(['diagnosa_rd' => 'Rawat Darurat', 'diagnosa_ri' => 'Rawat Inap', 'diagnosa_rj' => 'Rawat Jalan'], $filters, 'Trend Diagnosa'),
                $this->descriptionChart(['diagnosa_rd', 'diagnosa_ri', 'diagnosa_rj'], $filters, 'Diagnosa Terbanyak'),
            ],
            'klaim' => [
                $this->trendChart(['klaim_iks' => 'IKS', 'klaim_inacbg' => 'INA-CBG'], $filters, 'Trend Klaim'),
                $this->descriptionChart(['klaim_iks', 'klaim_inacbg', 'klaim_inacbg_ri', 'klaim_inacbg_rj'], $filters, 'Klaim Berdasarkan Deskripsi'),
            ],
            'pasien-kunjungan' => [
                $this->trendChart(['kunjungan' => 'Kunjungan'], $filters, 'Trend Kunjungan'),
                $this->groupedChart('kunjungan', 'INSTALASI', $filters, 'Kunjungan per Instalasi'),
                $this->groupedChart('kunjungan', 'SUBUNIT', $filters, 'Kunjungan per Poli', 10),
            ],
            'pelayanan-igd' => [
                $this->trendChart(['penunjang' => 'Penunjang'], $filters, 'Trend Pelayanan Penunjang'),
                $this->groupedChart('nedocs_igd', 'LEVEL_DESKRIPSI', $filters, 'Distribusi Level IGD', 8, false),
            ],
            'keuangan' => [
                $this->financeChart($filters),
                $this->groupedChart('pendapatan', 'CARABAYAR', $filters, 'Pendapatan Berdasarkan Cara Bayar', 8),
            ],
            'statistik-indikator' => [
                $this->statisticsVisitChart($filters),
                $this->referralChart($filters),
            ],
        };
    }

    private function sources(array $tables, array $filters): array
    {
        return collect($tables)->map(function (string $table) use ($filters): array {
            $exists = SimgosData::tableExists($table);
            $columns = $exists ? SimgosData::columns($table) : [];
            $search = $filters['search'][$table] ?? '';
            $visible = collect(self::TABLE_COLUMNS[$table] ?? $columns)
                ->filter(fn(string $column): bool => in_array($column, $columns, true))
                ->values()
                ->all();

            return [
                'key' => $table,
                'label' => self::TABLE_LABELS[$table] ?? $table,
                'exists' => $exists,
                'columns' => array_map(fn(string $column): array => ['key' => $column, 'label' => $this->columnLabel($column)], $visible),
                'search' => $search,
                'rows' => $exists ? $this->tableRows($table, $visible, $filters, $search) : [],
                'count' => $exists ? $this->filteredQuery($table, $filters, $search)->count() : 0,
            ];
        })->all();
    }

    private function tableRows(string $table, array $columns, array $filters, string $search = ''): LengthAwarePaginator|array
    {
        if (!$columns) {
            return [];
        }

        $query = $this->filteredQuery($table, $filters, $search)->select($columns);
        $sortColumn = in_array('TANGGAL', $columns, true) ? 'TANGGAL' : (in_array('TANGGAL_UPDATED', $columns, true) ? 'TANGGAL_UPDATED' : (in_array('LASTUPDATED', $columns, true) ? 'LASTUPDATED' : null));
        if ($sortColumn) {
            $query->orderByDesc($sortColumn);
        }

        return $query->paginate(10, $columns, 'page_' . $table)->withQueryString();
    }

    private function trendChart(array $tables, array $filters, string $title): array
    {
        $dates = [];
        $datasets = [];

        foreach ($tables as $table => $label) {
            if (!SimgosData::tableExists($table) || !in_array('TANGGAL', SimgosData::columns($table), true)) {
                continue;
            }
            $columns = SimgosData::columns($table);
            $rows = $this->filteredQuery($table, $filters)->selectRaw('DATE(`TANGGAL`) AS report_date')->selectRaw($this->measureExpression($columns) . ' AS report_total')->groupBy('report_date')->orderBy('report_date')->get();
            $values = [];
            foreach ($rows as $row) {
                $date = (string) $row->report_date;
                $dates[$date] = true;
                $values[$date] = (float) $row->report_total;
            }
            $datasets[] = ['label' => $label, 'values' => $values, 'backgroundColor' => '#027d78', 'borderColor' => '#027d78'];
        }

        $dateKeys = array_keys($dates);
        sort($dateKeys);
        $palette = ['#027d78', '#3d9e99', '#f59e0b', '#64748b'];
        $output = [];
        foreach ($datasets as $index => $dataset) {
            $output[] = ['label' => $dataset['label'], 'data' => array_map(fn(string $date): float => $dataset['values'][$date] ?? 0, $dateKeys), 'borderColor' => $palette[$index % count($palette)], 'backgroundColor' => $index === 0 ? 'rgba(2,125,120,.12)' : $palette[$index % count($palette)], 'fill' => $index === 0, 'tension' => .35];
        }

        return ['id' => 'chart-' . md5($title), 'title' => $title, 'subtitle' => 'Agregasi data aktual berdasarkan periode', 'type' => 'line', 'labels' => array_map(fn(string $date): string => Carbon::parse($date)->format('d M'), $dateKeys), 'datasets' => $output];
    }

    private function descriptionChart(array $tables, array $filters, string $title): array
    {
        $values = [];
        foreach ($tables as $table) {
            if (!SimgosData::tableExists($table) || !in_array('DESKRIPSI', SimgosData::columns($table), true)) {
                continue;
            }
            $columns = SimgosData::columns($table);
            $rows = $this->filteredQuery($table, $filters)->select('DESKRIPSI')->selectRaw($this->measureExpression($columns) . ' AS report_total')->whereNotNull('DESKRIPSI')->where('DESKRIPSI', '<>', '')->groupBy('DESKRIPSI')->get();
            foreach ($rows as $row) {
                $values[(string) $row->DESKRIPSI] = ($values[(string) $row->DESKRIPSI] ?? 0) + (float) $row->report_total;
            }
        }
        arsort($values);
        $values = array_slice($values, 0, 10, true);

        return ['id' => 'chart-' . md5($title), 'title' => $title, 'subtitle' => 'Sepuluh deskripsi dengan volume tertinggi', 'type' => 'bar', 'indexAxis' => 'y', 'labels' => array_keys($values), 'datasets' => [['label' => 'Jumlah', 'data' => array_values($values), 'backgroundColor' => '#3d9e99', 'borderRadius' => 5]]];
    }

    private function groupedChart(string $table, ?string $column, array $filters, string $title, int $limit = 8, bool $sumValue = true): array
    {
        if (!$column || !SimgosData::tableExists($table) || !in_array($column, SimgosData::columns($table), true)) {
            return ['id' => 'chart-' . md5($title), 'title' => $title, 'subtitle' => 'Belum ada data', 'type' => 'bar', 'labels' => [], 'datasets' => []];
        }
        $columns = SimgosData::columns($table);
        $expression = $sumValue ? $this->measureExpression($columns) : 'COUNT(*)';
        $rows = $this->filteredQuery($table, $filters)->select($column)->selectRaw($expression . ' AS report_total')->whereNotNull($column)->where($column, '<>', '')->groupBy($column)->orderByDesc('report_total')->limit($limit)->get();

        return ['id' => 'chart-' . md5($title), 'title' => $title, 'subtitle' => 'Agregasi data aktual', 'type' => 'bar', 'labels' => $rows->pluck($column)->map(fn($value): string => (string) $value)->values()->all(), 'datasets' => [['label' => 'Jumlah', 'data' => $rows->pluck('report_total')->map(fn($value): float => (float) $value)->values()->all(), 'backgroundColor' => '#027d78', 'borderRadius' => 5]]];
    }

    private function financeChart(array $filters): array
    {
        $series = ['Pendapatan' => [], 'Penerimaan' => []];
        foreach (['pendapatan' => 'Pendapatan', 'penerimaan' => 'Penerimaan'] as $table => $label) {
            if (!SimgosData::tableExists($table)) continue;
            foreach ($this->filteredQuery($table, $filters)->selectRaw('DATE(`TANGGAL`) AS report_date')->selectRaw('SUM(`VALUE`) AS report_total')->groupBy('report_date')->orderBy('report_date')->get() as $row) {
                $series[$label][(string) $row->report_date] = (float) $row->report_total;
            }
        }
        $dates = array_unique(array_merge(array_keys($series['Pendapatan']), array_keys($series['Penerimaan'])));
        sort($dates);

        return ['id' => 'chart-' . md5('finance'), 'title' => 'Pendapatan vs Penerimaan', 'subtitle' => 'Nilai aktual per tanggal', 'type' => 'bar', 'labels' => array_map(fn(string $date): string => Carbon::parse($date)->format('d M'), $dates), 'datasets' => [
            ['label' => 'Pendapatan', 'data' => array_map(fn(string $date): float => $series['Pendapatan'][$date] ?? 0, $dates), 'backgroundColor' => '#027d78', 'borderRadius' => 5],
            ['label' => 'Penerimaan', 'data' => array_map(fn(string $date): float => $series['Penerimaan'][$date] ?? 0, $dates), 'backgroundColor' => '#93c5c1', 'borderRadius' => 5],
        ]];
    }

    private function statisticsVisitChart(array $filters): array
    {
        $rows = $this->filteredQuery('statistik_kunjungan', $filters)->select('TANGGAL', 'RJ', 'RD', 'RI')->orderBy('TANGGAL')->get();
        return ['id' => 'chart-' . md5('statistics-visits'), 'title' => 'Statistik Kunjungan', 'subtitle' => 'Rawat jalan, rawat darurat, dan rawat inap', 'type' => 'line', 'labels' => $rows->map(fn($row): string => Carbon::parse($row->TANGGAL)->format('d M'))->all(), 'datasets' => [
            ['label' => 'Rawat Jalan', 'data' => $rows->pluck('RJ')->map(fn($value): float => (float) $value)->all(), 'borderColor' => '#027d78', 'backgroundColor' => 'rgba(2,125,120,.12)', 'fill' => true, 'tension' => .35],
            ['label' => 'Rawat Darurat', 'data' => $rows->pluck('RD')->map(fn($value): float => (float) $value)->all(), 'borderColor' => '#f59e0b', 'backgroundColor' => '#f59e0b', 'tension' => .35],
            ['label' => 'Rawat Inap', 'data' => $rows->pluck('RI')->map(fn($value): float => (float) $value)->all(), 'borderColor' => '#64748b', 'backgroundColor' => '#64748b', 'tension' => .35],
        ]];
    }

    private function referralChart(array $filters): array
    {
        $rows = $this->filteredQuery('statistik_rujukan', $filters)->select('TANGGAL', 'MASUK', 'KELUAR', 'BALIK')->orderBy('TANGGAL')->get();
        return ['id' => 'chart-' . md5('referrals'), 'title' => 'Statistik Rujukan', 'subtitle' => 'Rujukan masuk, keluar, dan balik', 'type' => 'bar', 'labels' => $rows->map(fn($row): string => Carbon::parse($row->TANGGAL)->format('d M'))->all(), 'datasets' => [
            ['label' => 'Masuk', 'data' => $rows->pluck('MASUK')->map(fn($value): float => (float) $value)->all(), 'backgroundColor' => '#027d78', 'borderRadius' => 5],
            ['label' => 'Keluar', 'data' => $rows->pluck('KELUAR')->map(fn($value): float => (float) $value)->all(), 'backgroundColor' => '#f59e0b', 'borderRadius' => 5],
            ['label' => 'Balik', 'data' => $rows->pluck('BALIK')->map(fn($value): float => (float) $value)->all(), 'backgroundColor' => '#94a3b8', 'borderRadius' => 5],
        ]];
    }

    private function filteredQuery(string $table, array $filters, string $search = ''): Builder
    {
        $columns = SimgosData::columns($table);
        $query = SimgosData::query($table);
        if (in_array('TANGGAL', $columns, true)) {
            $query->whereBetween('TANGGAL', [$filters['date_from'], $filters['date_to']]);
        } elseif (in_array('TAHUN', $columns, true)) {
            $from = Carbon::parse($filters['date_from']);
            $to = Carbon::parse($filters['date_to']);
            $query->whereBetween('TAHUN', [$from->year, $to->year]);
            if ($from->year === $to->year && in_array('BULAN', $columns, true)) {
                $query->whereBetween('BULAN', [$from->month, $to->month]);
            }
        }
        if (filled($filters['unit'])) {
            $query->where(function (Builder $builder) use ($filters, $columns): void {
                foreach (['INSTALASI', 'UNIT', 'SUBUNIT', 'RUANGAN'] as $column) {
                    if (in_array($column, $columns, true)) $builder->orWhere($column, $filters['unit']);
                }
            });
        }
        if (filled($filters['payment']) && in_array('CARABAYAR', $columns, true)) {
            $query->where('CARABAYAR', $filters['payment']);
        }
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($columns, $search): void {
                foreach ($columns as $column) {
                    $builder->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }
        return $query;
    }

    private function options(array $tables, array $columns): array
    {
        $values = collect();
        foreach ($tables as $table) {
            if (!SimgosData::tableExists($table)) continue;
            $available = SimgosData::columns($table);
            foreach ($columns as $column) {
                if (in_array($column, $available, true)) $values = $values->merge(SimgosData::query($table)->whereNotNull($column)->where($column, '<>', '')->distinct()->pluck($column));
            }
        }
        return $values->map(fn($value): string => (string) $value)->unique()->sort()->values()->all();
    }

    private function sumValues(array $tables, array $filters): float
    {
        return collect($tables)->sum(fn(string $table): float => $this->sourceValue($table, $filters));
    }

    private function sourceValue(string $table, array $filters): float
    {
        if (!SimgosData::tableExists($table)) return 0;
        $columns = SimgosData::columns($table);
        $query = $this->filteredQuery($table, $filters);
        return in_array('VALUE', $columns, true) ? (float) $query->sum('VALUE') : (float) $query->count();
    }

    private function rows(array $tables, array $filters): int
    {
        return collect($tables)->sum(fn(string $table): int => SimgosData::tableExists($table) ? (int) $this->filteredQuery($table, $filters)->count() : 0);
    }

    private function distinctValues(string $table, array $columns, array $filters): int
    {
        if (!SimgosData::tableExists($table)) return 0;
        $available = SimgosData::columns($table);
        $values = collect();
        foreach ($columns as $column) if (in_array($column, $available, true)) $values = $values->merge($this->filteredQuery($table, $filters)->whereNotNull($column)->where($column, '<>', '')->pluck($column));
        return $values->unique()->count();
    }

    private function bedTotal(array $filters): float
    {
        if (!SimgosData::tableExists('tempat_tidur_kemkes')) return 0;
        return (float) $this->filteredQuery('tempat_tidur_kemkes', $filters)->selectRaw('SUM(COALESCE(`TTLAKI`,0) + COALESCE(`TTPEREMPUAN`,0)) AS total')->value('total');
    }

    private function sumColumn(string $table, string $column, array $filters): float
    {
        return SimgosData::tableExists($table) && in_array($column, SimgosData::columns($table), true) ? (float) $this->filteredQuery($table, $filters)->sum($column) : 0;
    }

    private function deathTotal(array $filters): float
    {
        if (!SimgosData::tableExists('statistik_jumlah_kematian')) return 0;
        return $this->filteredQuery('statistik_jumlah_kematian', $filters)->get()->sum(function ($row): float {
            $content = json_decode((string) $row->KONTEN, true);
            return (float) ($content['jumlah_kematian'] ?? 0);
        });
    }

    private function latest(string $table, array $filters): ?object
    {
        if (!SimgosData::tableExists($table)) return null;
        $query = $this->filteredQuery($table, $filters);
        return $query->orderByDesc('TAHUN')->orderByDesc('PERIODE')->first();
    }

    private function measureExpression(array $columns): string
    {
        return in_array('VALUE', $columns, true) ? 'SUM(`VALUE`)' : 'COUNT(*)';
    }

    private function number(float|int|null $value): string
    {
        return number_format((float) ($value ?? 0), 0, ',', '.');
    }

    private function decimal(mixed $value): string
    {
        return $value === null ? '0,00' : number_format((float) $value, 2, ',', '.');
    }

    private function currency(float|int|null $value): string
    {
        return 'Rp ' . $this->number($value);
    }

    private function stat(string $label, string $value, string $description, string $icon, string $tone): array
    {
        return compact('label', 'value', 'description', 'icon', 'tone');
    }

    private function columnLabel(string $column): string
    {
        return str($column)->replace('_', ' ')->title()->toString();
    }
}
