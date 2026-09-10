<?php

namespace App\Services\Chatbot\Queries;

use App\Support\SimgosData;

class StatisticsQuery extends BaseQuery
{
    public function summary(array $period): array
    {
        return [
            'latest_indicator' => $this->latestIndicator($period),
            'visit_statistics' => $this->columnTotals('statistik_kunjungan', ['RJ', 'RD', 'RI'], $period),
            'referral_statistics' => $this->columnTotals('statistik_rujukan', ['MASUK', 'KELUAR', 'BALIK'], $period),
            'laboratory_statistics' => $this->columnTotals('statistik_pemeriksaan_laboratorium', ['JUMLAH_PASIEN'], $period),
            'mortality_rows' => $this->rowCount('statistik_jumlah_kematian', $period),
        ];
    }

    private function latestIndicator(array $period): array
    {
        if (!$this->exists('statistik_indikator')) {
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
}
