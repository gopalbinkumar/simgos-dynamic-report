<?php

namespace App\Http\Controllers;

use App\Exports\DynamicReportExport;
use App\Support\SimgosData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;

class DynamicReportController extends Controller
{
    /**
     * Daftar sumber data yang diizinkan untuk reporting read-only.
     * Nama tabel tidak pernah dipakai langsung dari input user tanpa whitelist.
     */
    private const SOURCES = [
        'diagnosa_rd' => 'Diagnosa Rawat Darurat',
        'diagnosa_ri' => 'Diagnosa Rawat Inap',
        'diagnosa_rj' => 'Diagnosa Rawat Jalan',
        'indikator_rs' => 'Indikator Rumah Sakit',
        'klaim_iks' => 'Klaim IKS',
        'klaim_inacbg' => 'Klaim INA-CBG',
        'klaim_inacbg_ri' => 'Klaim INA-CBG Rawat Inap',
        'klaim_inacbg_rj' => 'Klaim INA-CBG Rawat Jalan',
        'komentar_pasien_fast_track' => 'Komentar Pasien Fast Track',
        'konfirmasi_pasien_fast_track' => 'Konfirmasi Pasien Fast Track',
        'kunjungan' => 'Kunjungan',
        'nedocs_igd' => 'NEDOCS IGD',
        'pasien_rawat_inap' => 'Pasien Rawat Inap',
        'pendapatan' => 'Pendapatan',
        'penerimaan' => 'Penerimaan',
        'pengunjung' => 'Pengunjung',
        'penunjang' => 'Pelayanan Penunjang',
        'statistik_10_besar_diagnosa_rujukan' => '10 Besar Diagnosa Rujukan',
        'statistik_10_besar_penyakit' => '10 Besar Penyakit',
        'statistik_gol_darah' => 'Statistik Golongan Darah',
        'statistik_indikator' => 'Statistik Indikator',
        'statistik_jumlah_kematian' => 'Statistik Jumlah Kematian',
        'statistik_kunjungan' => 'Statistik Kunjungan',
        'statistik_mutu_pelayanan' => 'Statistik Mutu Pelayanan',
        'statistik_pemeriksaan_laboratorium' => 'Statistik Pemeriksaan Laboratorium',
        'statistik_rujukan' => 'Statistik Rujukan',
        'tempat_tidur_kemkes' => 'Tempat Tidur Kemkes',
    ];

    private const OPERATORS = ['=', '>=', '<=', 'contains'];

    private const COLUMN_LABELS = [
        'ID' => 'ID',
        'TANGGAL' => 'Tanggal',
        'DESKRIPSI' => 'Deskripsi',
        'VALUE' => 'Nilai',
        'CARABAYAR' => 'Cara Bayar',
        'LASTUPDATED' => 'Terakhir Diperbarui',
        'TANGGAL_UPDATED' => 'Tanggal Diperbarui',
        'INSTALASI' => 'Instalasi',
        'UNIT' => 'Unit',
        'SUBUNIT' => 'Subunit / Poli',
        'RUANGAN' => 'Ruangan',
        'STATUS' => 'Status',
        'KONTEN' => 'Konten',
    ];

    public function index(Request $request)
    {
        $state = $this->normalizeState($this->state());
        $showReport = $request->boolean('report', false);
        $search = trim((string) $request->query('search', ''));
        $availableColumns = $this->columnOptions($state['source']);

        return view('reports.dynamic', [
            'state' => $state,
            'showReport' => $showReport,
            'sources' => self::SOURCES,
            'availableColumns' => $availableColumns,
            'defaultFilterField' => $this->defaultFilterField($availableColumns),
            'rows' => $showReport
                ? $this->paginateRows($state, $request, $search)
                : collect(),
            'search' => $search,
        ]);
    }

    public function run(Request $request)
    {
        $state = $this->validatedState($request);
        session()->put('dynamic_report', $state);

        return redirect()
            ->route('reports.dynamic', ['report' => 1])
            ->with('success', 'Report berhasil dijalankan menggunakan data SIMGOS read-only.');
    }

    public function reset()
    {
        session()->forget('dynamic_report');

        return redirect()->route('reports.dynamic');
    }

    public function export(Request $request)
    {
        $state = $this->normalizeState($this->state());
        $search = trim((string) $request->query('search', ''));
        $selected = $state['selected_columns'];
        $labels = $this->columnOptions($state['source']);
        $rows = SimgosData::tableExists($state['source'])
            ? $this->reportQuery($state, $search)->get()
            : collect();

        return Excel::download(
            new DynamicReportExport($rows, $selected, $labels),
            'simgos-report.xlsx',
            ExcelWriter::XLSX,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    private function state(): array
    {
        return array_replace_recursive($this->defaultState(), (array) session('dynamic_report', []));
    }

    private function defaultState(): array
    {
        return [
            'source' => 'kunjungan',
            'selected_columns' => ['TANGGAL', 'ID', 'DESKRIPSI', 'SUBUNIT', 'CARABAYAR', 'VALUE'],
            'filters' => [['field' => 'TANGGAL', 'operator' => '>=', 'value' => '']],
            'sort_column' => 'TANGGAL',
            'sort_direction' => 'desc',
            'group_by' => 'none',
        ];
    }

    private function validatedState(Request $request): array
    {
        $payload = $request->validate([
            'source' => ['required', 'string', 'in:' . implode(',', array_keys(self::SOURCES))],
            'selected_columns' => ['required', 'array', 'min:1'],
            'selected_columns.*' => ['required', 'string', 'max:64'],
            'filters' => ['nullable', 'array'],
            'filters.*.field' => ['nullable', 'string', 'max:64'],
            'filters.*.operator' => ['nullable', 'string', 'in:' . implode(',', self::OPERATORS)],
            'filters.*.value' => ['nullable', 'string', 'max:255'],
            'sort_column' => ['nullable', 'string', 'max:64'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
            'group_by' => ['nullable', 'string', 'max:64'],
        ]);

        $columns = array_keys($this->columnOptions($payload['source']));
        $selectedColumns = collect($payload['selected_columns'] ?? [])
            ->filter(fn(string $column): bool => in_array($column, $columns, true))
            ->unique()
            ->values()
            ->all();

        if (!$selectedColumns) {
            throw ValidationException::withMessages([
                'selected_columns' => 'Pilih minimal satu kolom yang tersedia pada sumber data.',
            ]);
        }

        $options = $this->columnOptionsFromKeys($columns);
        $defaultFilterField = $this->defaultFilterField($options);
        $filters = collect($payload['filters'] ?? [])
            ->map(function (array $filter) use ($columns, $defaultFilterField): array {
                return [
                    'field' => in_array($filter['field'] ?? '', $columns, true)
                        ? $filter['field']
                        : $defaultFilterField,
                    'operator' => in_array($filter['operator'] ?? '', self::OPERATORS, true)
                        ? $filter['operator']
                        : '=',
                    'value' => trim((string) ($filter['value'] ?? '')),
                ];
            })
            ->filter(fn(array $filter): bool => $filter['value'] !== '')
            ->values()
            ->all();

        return [
            'source' => $payload['source'],
            'selected_columns' => $selectedColumns,
            'filters' => $filters,
            'sort_column' => in_array($payload['sort_column'] ?? '', $columns, true)
                ? $payload['sort_column']
                : $this->defaultSortColumn($columns),
            'sort_direction' => ($payload['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc',
            'group_by' => ($payload['group_by'] ?? 'none') === 'none'
                ? 'none'
                : (in_array($payload['group_by'] ?? '', $columns, true) ? $payload['group_by'] : 'none'),
        ];
    }

    private function normalizeState(array $state): array
    {
        $source = in_array($state['source'] ?? '', array_keys(self::SOURCES), true)
            ? $state['source']
            : 'kunjungan';
        $options = $this->columnOptions($source);
        $columns = array_keys($options);
        $selected = collect($state['selected_columns'] ?? [])
            ->filter(fn($column): bool => is_string($column) && in_array($column, $columns, true))
            ->unique()
            ->values()
            ->all();

        if (!$selected) {
            $selected = collect($this->defaultState()['selected_columns'])
                ->filter(fn(string $column): bool => in_array($column, $columns, true))
                ->values()
                ->all();
        }

        if (!$selected) {
            $selected = array_slice($columns, 0, 6);
        }

        $filters = collect($state['filters'] ?? [])
            ->filter(fn($filter): bool => is_array($filter))
            ->map(fn(array $filter): array => [
                'field' => in_array($filter['field'] ?? '', $columns, true)
                    ? $filter['field']
                    : $this->defaultFilterField($options),
                'operator' => in_array($filter['operator'] ?? '', self::OPERATORS, true)
                    ? $filter['operator']
                    : '=',
                'value' => trim((string) ($filter['value'] ?? '')),
            ])
            ->values()
            ->all();

        if (!$filters) {
            $filters = [['field' => $this->defaultFilterField($options), 'operator' => '=', 'value' => '']];
        }

        return [
            'source' => $source,
            'selected_columns' => $selected,
            'filters' => $filters,
            'sort_column' => in_array($state['sort_column'] ?? '', $columns, true)
                ? $state['sort_column']
                : $this->defaultSortColumn($columns),
            'sort_direction' => ($state['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc',
            'group_by' => ($state['group_by'] ?? 'none') === 'none'
                ? 'none'
                : (in_array($state['group_by'] ?? '', $columns, true) ? $state['group_by'] : 'none'),
        ];
    }

    private function paginateRows(array $state, Request $request, string $search): LengthAwarePaginator
    {
        if (!SimgosData::tableExists($state['source'])) {
            return new LengthAwarePaginator([], 0, 10, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'report_page',
            ]);
        }

        return $this->reportQuery($state, $search)
            ->paginate(10, ['*'], 'report_page')
            ->withQueryString();
    }

    private function reportQuery(array $state, string $search = ''): Builder
    {
        $columns = array_keys($this->columnOptions($state['source']));
        $query = SimgosData::query($state['source'])->select($state['selected_columns']);

        foreach ($state['filters'] as $filter) {
            $field = $filter['field'] ?? '';
            $operator = $filter['operator'] ?? '=';
            $value = trim((string) ($filter['value'] ?? ''));

            if ($value === '' || strtolower($value) === 'semua' || !in_array($field, $columns, true)) {
                continue;
            }

            if ($operator === 'contains') {
                $query->where($field, 'like', '%' . $value . '%');
            } else {
                $query->where($field, $operator, $value);
            }
        }

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($columns, $search): void {
                foreach ($columns as $column) {
                    $builder->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }

        $sortColumn = in_array($state['sort_column'], $columns, true)
            ? $state['sort_column']
            : $this->defaultSortColumn($columns);
        $direction = $state['sort_direction'] === 'asc' ? 'asc' : 'desc';

        if ($state['group_by'] !== 'none' && in_array($state['group_by'], $columns, true)) {
            $query->orderBy($state['group_by'], $direction);
        }

        return $query->orderBy($sortColumn, $direction);
    }

    private function columnOptions(string $source): array
    {
        if (!in_array($source, array_keys(self::SOURCES), true)) {
            return [];
        }

        return $this->columnOptionsFromKeys(SimgosData::columns($source));
    }

    private function columnOptionsFromKeys(array $columns): array
    {
        return collect($columns)
            ->mapWithKeys(fn(string $column): array => [$column => self::COLUMN_LABELS[$column] ?? $this->columnLabel($column)])
            ->all();
    }

    private function defaultFilterField(array $columns): string
    {
        foreach (['TANGGAL', 'TAHUN', 'DESKRIPSI'] as $preferred) {
            if (array_key_exists($preferred, $columns)) {
                return $preferred;
            }
        }

        return (string) (array_key_first($columns) ?? '');
    }

    private function defaultSortColumn(array $columns): string
    {
        foreach (['TANGGAL', 'TANGGAL_UPDATED', 'LASTUPDATED', 'TAHUN', 'ID'] as $preferred) {
            if (in_array($preferred, $columns, true)) {
                return $preferred;
            }
        }

        return (string) (array_key_first($columns) ?? '');
    }

    private function columnLabel(string $column): string
    {
        return str($column)->replace('_', ' ')->title()->toString();
    }
}
