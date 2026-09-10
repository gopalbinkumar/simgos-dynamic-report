<?php

namespace App\Services\Chatbot\Queries;

class DashboardQuery extends BaseQuery
{
    public function summary(array $period): array
    {
        return [
            'total_kunjungan' => $this->valueTotal('kunjungan', $period),
            'total_pengunjung' => $this->valueTotal('pengunjung', $period),
            'pasien_rawat_inap' => $this->valueTotal('pasien_rawat_inap', $period),
            'total_pendapatan' => $this->valueTotal('pendapatan', $period),
            'total_penerimaan' => $this->valueTotal('penerimaan', $period),
            'total_diagnosa' => $this->valueTotalMany(['diagnosa_rd', 'diagnosa_ri', 'diagnosa_rj'], $period),
            'top_units' => $this->topBy('kunjungan', $this->groupColumn('kunjungan'), $period),
        ];
    }
}
