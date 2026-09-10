<?php

namespace App\Services\Chatbot\Queries;

class ServicesQuery extends BaseQuery
{
    public function summary(array $period): array
    {
        return [
            'total_pelayanan_penunjang' => $this->valueTotal('penunjang', $period),
            'jumlah_pengukuran_igd' => $this->rowCount('nedocs_igd', $period),
            'total_tempat_tidur' => $this->bedTotal($period),
            'total_tempat_tidur_terpakai' => $this->bedUsed($period),
            'feedback_fast_track' => $this->rowCountMany(['komentar_pasien_fast_track', 'konfirmasi_pasien_fast_track'], $period),
            'igd_by_level' => $this->topBy('nedocs_igd', 'LEVEL_DESKRIPSI', $period, false),
        ];
    }

    private function bedTotal(array $period): float
    {
        if (!$this->exists('tempat_tidur_kemkes')) {
            return 0;
        }

        return (float) $this->filteredQuery('tempat_tidur_kemkes', $period)
            ->selectRaw('SUM(COALESCE(`TTLAKI`,0) + COALESCE(`TTPEREMPUAN`,0)) AS total')
            ->value('total');
    }

    private function bedUsed(array $period): float
    {
        if (!$this->exists('tempat_tidur_kemkes') || !in_array('TERPAKAI_KONFIRMASI', \App\Support\SimgosData::columns('tempat_tidur_kemkes'), true)) {
            return 0;
        }

        return (float) $this->filteredQuery('tempat_tidur_kemkes', $period)->sum('TERPAKAI_KONFIRMASI');
    }
}
