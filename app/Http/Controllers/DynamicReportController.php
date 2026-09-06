<?php

namespace App\Http\Controllers;

use App\Support\SimgosData;
use Illuminate\Http\Request;

class DynamicReportController extends Controller
{
    private const SOURCES = [
        'kunjungan' => 'Kunjungan',
        'diagnosa_rd' => 'Diagnosa Rawat Darurat',
        'diagnosa_ri' => 'Diagnosa Rawat Inap',
        'diagnosa_rj' => 'Diagnosa Rawat Jalan',
        'indikator_rs' => 'Indikator Rumah Sakit',
        'nedocs_igd' => 'NEDOCS IGD',
    ];

    private const COLUMNS = [
        'id' => 'ID',
        'tanggal' => 'Tanggal',
        'nama' => 'Nama / Deskripsi',
        'category' => 'Kategori',
        'status' => 'Status',
        'keterangan' => 'Keterangan',
    ];

    public function index(Request $request)
    {
        $state = $this->state($request);
        $showReport = $request->boolean('report', false);

        return view('reports.dynamic', [
            'state' => $state,
            'showReport' => $showReport,
            'sources' => self::SOURCES,
            'availableColumns' => self::COLUMNS,
            'rows' => $showReport ? $this->rows($state) : [],
        ]);
    }

    public function run(Request $request)
    {
        $state = $this->validatedState($request);
        session(['dynamic_report' => $state]);

        return redirect()->route('reports.dynamic', ['report' => 1])->with('success', 'Report berhasil disiapkan dari sumber data read-only.');
    }

    public function reset()
    {
        session()->forget('dynamic_report');

        return redirect()->route('reports.dynamic');
    }

    public function export(Request $request)
    {
        $state = $this->state($request);
        $rows = $this->rows($state);
        $selected = $state['selected_columns'];

        return response()->streamDownload(function () use ($rows, $selected): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, array_map(fn (string $column): string => self::COLUMNS[$column], $selected));
            foreach ($rows as $row) {
                fputcsv($handle, array_map(fn (string $column): string => (string) ($row[$column] ?? '-'), $selected));
            }
            fclose($handle);
        }, 'simgos-report.csv', ['Content-Type' => 'text/csv']);
    }

    private function state(Request $request): array
    {
        return array_replace_recursive($this->defaultState(), (array) session('dynamic_report', []));
    }

    private function defaultState(): array
    {
        return [
            'source' => 'kunjungan',
            'selected_columns' => ['id', 'tanggal', 'nama', 'category'],
            'filters' => [['field' => 'category', 'operator' => '=', 'value' => 'Semua']],
            'sort_column' => 'tanggal',
            'sort_direction' => 'desc',
            'group_by' => 'none',
        ];
    }

    private function validatedState(Request $request): array
    {
        $state = $request->validate([
            'source' => ['required', 'string', 'in:' . implode(',', array_keys(self::SOURCES))],
            'selected_columns' => ['required', 'array', 'min:1'],
            'selected_columns.*' => ['string', 'in:' . implode(',', array_keys(self::COLUMNS))],
            'filters' => ['nullable', 'array'],
            'filters.*.field' => ['nullable', 'string', 'in:category,tanggal,status'],
            'filters.*.operator' => ['nullable', 'string', 'in:=,>=,<=,contains'],
            'filters.*.value' => ['nullable', 'string', 'max:100'],
            'sort_column' => ['nullable', 'string', 'in:tanggal,id,category'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
            'group_by' => ['nullable', 'string', 'in:none,category,tanggal'],
        ]);

        $state['filters'] = array_values(array_filter($state['filters'] ?? [], fn (array $filter): bool => trim((string) ($filter['value'] ?? '')) !== ''));

        return array_replace($this->defaultState(), $state);
    }

    private function rows(array $state): array
    {
        $source = $state['source'];
        if (! SimgosData::tableExists($source)) {
            return $this->mockRows();
        }

        $rawRows = SimgosData::query($source)->limit(500)->get();
        $rows = $rawRows->map(fn ($row): array => $this->normalizeRow((array) $row->getAttributes()))->all();

        foreach ($state['filters'] as $filter) {
            $value = trim((string) ($filter['value'] ?? ''));
            if ($value === '' || strtolower($value) === 'semua') {
                continue;
            }

            $rows = array_values(array_filter($rows, function (array $row) use ($filter, $value): bool {
                $field = $filter['field'] ?? 'category';
                $actual = (string) ($row[$field] ?? '');
                return match ($filter['operator'] ?? '=') {
                    '>=' => $actual >= $value,
                    '<=' => $actual <= $value,
                    'contains' => stripos($actual, $value) !== false,
                    default => strcasecmp($actual, $value) === 0,
                };
            }));
        }

        $sort = $state['sort_column'] ?? 'tanggal';
        usort($rows, function (array $left, array $right) use ($sort, $state): int {
            $comparison = strnatcasecmp((string) ($left[$sort] ?? ''), (string) ($right[$sort] ?? ''));
            return ($state['sort_direction'] ?? 'desc') === 'asc' ? $comparison : -$comparison;
        });

        return $rows;
    }

    private function normalizeRow(array $raw): array
    {
        return [
            'id' => $this->value($raw, ['ID', 'id', 'NO', 'NOMOR']),
            'tanggal' => $this->value($raw, ['TANGGAL', 'tanggal', 'DATE', 'created_at']),
            'nama' => $this->value($raw, ['DESKRIPSI', 'deskripsi', 'NAMA', 'nama', 'NAME']),
            'category' => $this->value($raw, ['INSTALASI', 'KATEGORI', 'category', 'kategori', 'JENIS']),
            'status' => $this->value($raw, ['STATUS', 'status', 'CARABAYAR']),
            'keterangan' => $this->value($raw, ['SUBUNIT', 'KETERANGAN', 'keterangan', 'UNIT']),
        ];
    }

    private function value(array $row, array $keys): string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null) {
                return (string) $row[$key];
            }
        }

        return '-';
    }

    private function mockRows(): array
    {
        return [
            ['id' => 'K-001', 'tanggal' => '2026-09-02', 'nama' => 'Kunjungan Rawat Jalan', 'category' => 'Pelayanan', 'status' => 'Aktif', 'keterangan' => 'Poli Penyakit Dalam'],
            ['id' => 'K-002', 'tanggal' => '2026-09-01', 'nama' => 'Kunjungan IGD', 'category' => 'Kunjungan', 'status' => 'Aktif', 'keterangan' => 'IGD'],
            ['id' => 'K-003', 'tanggal' => '2026-08-31', 'nama' => 'Pemeriksaan Penunjang', 'category' => 'Diagnosa', 'status' => 'Selesai', 'keterangan' => 'Radiologi'],
        ];
    }
}
