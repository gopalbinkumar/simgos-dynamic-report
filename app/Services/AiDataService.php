<?php

namespace App\Services;

use App\Support\SimgosData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class AiDataService
{
    public function context(string $intent, string $question): array
    {
        $period = $this->periodFromQuestion($question);

        $context = match ($intent) {
            'overview' => $this->overview($period),
            'diagnosis' => $this->diagnosis($period),
            'claims' => $this->claims($period),
            'visits' => $this->visits($period),
            'services' => $this->services($period),
            'finance' => $this->finance($period),
            'statistics' => $this->statistics($period),
            default => [],
        };

        $context['period'] = [
            'from' => $period['from']->toDateString(),
            'to' => $period['to']->toDateString(),
            'label' => $this->periodLabel($period),
        ];

        return $context;
    }

    private function overview(array $period): array
    {
        return [
            'dataset' => 'Ringkasan seluruh dataset SIMGOS',
            'metrics' => [
                'total_kunjungan' => $this->valueTotal('kunjungan', $period),
                'total_pengunjung' => $this->valueTotal('pengunjung', $period),
                'pasien_rawat_inap' => $this->valueTotal('pasien_rawat_inap', $period),
                'total_pendapatan' => $this->valueTotal('pendapatan', $period),
                'total_penerimaan' => $this->valueTotal('penerimaan', $period),
                'total_diagnosa' => $this->valueTotalMany(['diagnosa_rd', 'diagnosa_ri', 'diagnosa_rj'], $period),
            ],
            'top_units' => $this->topBy('kunjungan', $this->availableGroupColumn('kunjungan'), $period),
        ];
    }

    private function diagnosis(array $period): array
    {
        return [
            'dataset' => 'Diagnosa rawat jalan, rawat inap, dan rawat darurat',
            'metrics' => [
                'total_diagnosa' => $this->valueTotalMany(['diagnosa_rd', 'diagnosa_ri', 'diagnosa_rj'], $period),
                'rawat_jalan' => $this->valueTotal('diagnosa_rj', $period),
                'rawat_inap' => $this->valueTotal('diagnosa_ri', $period),
                'rawat_darurat' => $this->valueTotal('diagnosa_rd', $period),
            ],
            'top_diagnosis' => $this->topDescriptions(['diagnosa_rd', 'diagnosa_ri', 'diagnosa_rj'], $period),
        ];
    }

    private function claims(array $period): array
    {
        $tables = ['klaim_iks', 'klaim_inacbg', 'klaim_inacbg_ri', 'klaim_inacbg_rj'];

        return [
            'dataset' => 'Klaim IKS dan INA-CBG',
            'metrics' => [
                'total_nilai_klaim' => $this->valueTotalMany($tables, $period),
                'jumlah_baris_klaim' => $this->rowCountMany($tables, $period),
                'klaim_iks' => $this->valueTotal('klaim_iks', $period),
                'klaim_inacbg' => $this->valueTotalMany(['klaim_inacbg', 'klaim_inacbg_ri', 'klaim_inacbg_rj'], $period),
            ],
            'by_payment' => $this->topBy('klaim_iks', 'CARABAYAR', $period),
        ];
    }

    private function visits(array $period): array
    {
        return [
            'dataset' => 'Pasien dan kunjungan',
            'metrics' => [
                'total_kunjungan' => $this->valueTotal('kunjungan', $period),
                'total_pengunjung' => $this->valueTotal('pengunjung', $period),
                'pasien_rawat_inap' => $this->valueTotal('pasien_rawat_inap', $period),
                'jumlah_baris_kunjungan' => $this->rowCount('kunjungan', $period),
            ],
            'top_units' => $this->topBy('kunjungan', $this->availableGroupColumn('kunjungan'), $period),
            'daily_trend' => $this->dailyTotal('kunjungan', $period),
        ];
    }

    private function services(array $period): array
    {
        $beds = $this->bedSummary($period);

        return [
            'dataset' => 'Pelayanan penunjang, IGD, tempat tidur, dan fast track',
            'metrics' => [
                'total_pelayanan_penunjang' => $this->valueTotal('penunjang', $period),
                'jumlah_pengukuran_igd' => $this->rowCount('nedocs_igd', $period),
                'total_tempat_tidur' => $beds['total'],
                'total_tempat_tidur_terpakai' => $beds['used'],
                'feedback_fast_track' => $this->rowCountMany(['komentar_pasien_fast_track', 'konfirmasi_pasien_fast_track'], $period),
            ],
            'igd_by_level' => $this->topBy('nedocs_igd', 'LEVEL_DESKRIPSI', $period, false),
            'beds' => $beds,
        ];
    }

    private function finance(array $period): array
    {
        return [
            'dataset' => 'Pendapatan dan penerimaan',
            'metrics' => [
                'total_pendapatan' => $this->valueTotal('pendapatan', $period),
                'total_penerimaan' => $this->valueTotal('penerimaan', $period),
                'baris_pendapatan' => $this->rowCount('pendapatan', $period),
                'baris_penerimaan' => $this->rowCount('penerimaan', $period),
            ],
            'pendapatan_by_payment' => $this->topBy('pendapatan', 'CARABAYAR', $period),
            'penerimaan_by_payment' => $this->topBy('penerimaan', 'CARABAYAR', $period),
            'daily_pendapatan' => $this->dailyTotal('pendapatan', $period),
        ];
    }

    private function statistics(array $period): array
    {
        $indicator = $this->latestIndicator($period);

        return [
            'dataset' => 'Statistik dan indikator rumah sakit',
            'latest_indicator' => $indicator,
            'visit_statistics' => $this->columnTotals('statistik_kunjungan', ['RJ', 'RD', 'RI'], $period),
            'referral_statistics' => $this->columnTotals('statistik_rujukan', ['MASUK', 'KELUAR', 'BALIK'], $period),
            'laboratory_statistics' => $this->columnTotals('statistik_pemeriksaan_laboratorium', ['JUMLAH_PASIEN'], $period),
            'mortality_rows' => $this->rowCount('statistik_jumlah_kematian', $period),
        ];
    }

    private function valueTotal(string $table, array $period): float|int
    {
        if (!SimgosData::tableExists($table)) {
            return 0;
        }

        $query = $this->filteredQuery($table, $period);

        return in_array('VALUE', SimgosData::columns($table), true)
            ? (float) $query->sum('VALUE')
            : (int) $query->count();
    }

    private function valueTotalMany(array $tables, array $period): float
    {
        return (float) collect($tables)->sum(fn(string $table): float|int => $this->valueTotal($table, $period));
    }

    private function rowCount(string $table, array $period): int
    {
        return SimgosData::tableExists($table) ? (int) $this->filteredQuery($table, $period)->count() : 0;
    }

    private function rowCountMany(array $tables, array $period): int
    {
        return (int) collect($tables)->sum(fn(string $table): int => $this->rowCount($table, $period));
    }

    private function topBy(string $table, ?string $column, array $period, bool $sumValue = true): array
    {
        if (!$column || !SimgosData::tableExists($table) || !in_array($column, SimgosData::columns($table), true)) {
            return [];
        }

        $columns = SimgosData::columns($table);
        $expression = $sumValue && in_array('VALUE', $columns, true) ? 'SUM(`VALUE`)' : 'COUNT(*)';

        return $this->filteredQuery($table, $period)
            ->select($column)
            ->selectRaw($expression . ' AS total')
            ->whereNotNull($column)
            ->where($column, '<>', '')
            ->groupBy($column)
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($row): array => [
                'label' => (string) $row->{$column},
                'total' => (float) $row->total,
            ])
            ->values()
            ->all();
    }

    private function topDescriptions(array $tables, array $period): array
    {
        $values = [];

        foreach ($tables as $table) {
            if (!SimgosData::tableExists($table) || !in_array('DESKRIPSI', SimgosData::columns($table), true)) {
                continue;
            }

            $expression = in_array('VALUE', SimgosData::columns($table), true) ? 'SUM(`VALUE`)' : 'COUNT(*)';
            $rows = $this->filteredQuery($table, $period)
                ->select('DESKRIPSI')
                ->selectRaw($expression . ' AS total')
                ->whereNotNull('DESKRIPSI')
                ->where('DESKRIPSI', '<>', '')
                ->groupBy('DESKRIPSI')
                ->get();

            foreach ($rows as $row) {
                $key = (string) $row->DESKRIPSI;
                $values[$key] = ($values[$key] ?? 0) + (float) $row->total;
            }
        }

        arsort($values);

        return collect(array_slice($values, 0, 10, true))
            ->map(fn(float $total, string $label): array => compact('label', 'total'))
            ->values()
            ->all();
    }

    private function dailyTotal(string $table, array $period): array
    {
        if (!SimgosData::tableExists($table) || !in_array('TANGGAL', SimgosData::columns($table), true)) {
            return [];
        }

        $expression = in_array('VALUE', SimgosData::columns($table), true) ? 'SUM(`VALUE`)' : 'COUNT(*)';

        return $this->filteredQuery($table, $period)
            ->selectRaw('DATE(`TANGGAL`) AS date')
            ->selectRaw($expression . ' AS total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($row): array => ['date' => (string) $row->date, 'total' => (float) $row->total])
            ->values()
            ->all();
    }

    private function columnTotals(string $table, array $columns, array $period): array
    {
        if (!SimgosData::tableExists($table)) {
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

    private function latestIndicator(array $period): array
    {
        if (!SimgosData::tableExists('statistik_indikator')) {
            return [];
        }

        $row = $this->filteredQuery('statistik_indikator', $period)
            ->orderByDesc('TAHUN')
            ->orderByDesc('PERIODE')
            ->first();

        if (!$row) {
            return [];
        }

        return collect(['TAHUN', 'PERIODE', 'JENIS', 'BOR', 'ALOS', 'BTO', 'TOI', 'NDR', 'GDR'])
            ->filter(fn(string $column): bool => in_array($column, SimgosData::columns('statistik_indikator'), true))
            ->mapWithKeys(fn(string $column): array => [strtolower($column) => $row->{$column}])
            ->all();
    }

    private function bedSummary(array $period): array
    {
        if (!SimgosData::tableExists('tempat_tidur_kemkes')) {
            return ['total' => 0, 'used' => 0];
        }

        $query = $this->filteredQuery('tempat_tidur_kemkes', $period);

        return [
            'total' => (float) $query->selectRaw('SUM(COALESCE(`TTLAKI`,0) + COALESCE(`TTPEREMPUAN`,0)) AS total')->value('total'),
            'used' => (float) $query->sum('TERPAKAI_KONFIRMASI'),
        ];
    }

    private function availableGroupColumn(string $table): ?string
    {
        $columns = SimgosData::columns($table);

        foreach (['SUBUNIT', 'UNIT', 'INSTALASI'] as $column) {
            if (in_array($column, $columns, true)) {
                return $column;
            }
        }

        return null;
    }

    private function filteredQuery(string $table, array $period): Builder
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

        return $query;
    }

    private function periodFromQuestion(string $question): array
    {
        $now = Carbon::now();
        $normalized = mb_strtolower($question);

        if (str_contains($normalized, 'hari ini')) {
            $from = $now->copy()->startOfDay();
            $to = $now->copy()->endOfDay();
        } elseif (str_contains($normalized, 'kemarin')) {
            $from = $now->copy()->subDay()->startOfDay();
            $to = $now->copy()->subDay()->endOfDay();
        } elseif (str_contains($normalized, 'bulan lalu') || str_contains($normalized, 'bulan sebelumnya')) {
            $from = $now->copy()->subMonthNoOverflow()->startOfMonth();
            $to = $now->copy()->subMonthNoOverflow()->endOfMonth();
        } elseif (str_contains($normalized, 'tahun ini')) {
            $from = $now->copy()->startOfYear();
            $to = $now->copy()->endOfYear();
        } else {
            $from = $now->copy()->startOfMonth();
            $to = $now->copy()->endOfDay();
        }

        $dates = collect();

        if (preg_match_all('/\b(\d{4})-(\d{1,2})-(\d{1,2})\b/', $question, $isoMatches) >= 1) {
            foreach ($isoMatches[0] as $date) {
                $dates->push(Carbon::createFromFormat('Y-m-d', $date));
            }
        }

        if (preg_match_all('/\b(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})\b/', $question, $dmyMatches) >= 1) {
            foreach ($dmyMatches[0] as $date) {
                $dates->push(Carbon::createFromFormat('d/m/Y', str_replace('-', '/', $date)));
            }
        }

        if ($dates->isNotEmpty()) {
            $dates = $dates->sort()->values();
            $from = $dates->first()->copy()->startOfDay();
            $to = ($dates->count() > 1 ? $dates->last() : $dates->first())->copy()->endOfDay();
        }

        return compact('from', 'to');
    }

    private function periodLabel(array $period): string
    {
        return $period['from']->isSameDay($period['to'])
            ? $period['from']->translatedFormat('d F Y')
            : $period['from']->translatedFormat('d F Y') . ' sampai ' . $period['to']->translatedFormat('d F Y');
    }
}
