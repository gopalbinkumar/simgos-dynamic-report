<?php

namespace App\Services\Chatbot\Queries;

class VisitsQuery extends BaseQuery
{
    public function summary(array $period): array
    {
        if (filled($period['unit'] ?? null)) {
            return [
                'requested_unit' => $period['unit'],
                'total_kunjungan' => $this->valueTotal('kunjungan', $period),
                'daily_trend' => $this->dailyTotal('kunjungan', $period),
            ];
        }

        return [
            'total_kunjungan' => $this->valueTotal('kunjungan', $period),
            'total_pengunjung' => $this->valueTotal('pengunjung', $period),
            'pasien_rawat_inap' => $this->valueTotal('pasien_rawat_inap', $period),
            'jumlah_baris_kunjungan' => $this->rowCount('kunjungan', $period),
            'top_units' => $this->topBy('kunjungan', $this->groupColumn('kunjungan'), $period),
            'unit_breakdown' => $this->topBy('kunjungan', $this->groupColumn('kunjungan'), $period, true, 100),
            'daily_trend' => $this->dailyTotal('kunjungan', $period),
        ];
    }
}
