<?php

namespace App\Services\Chatbot\Queries;

class ClaimsQuery extends BaseQuery
{
    public function summary(array $period): array
    {
        $tables = ['klaim_iks', 'klaim_inacbg', 'klaim_inacbg_ri', 'klaim_inacbg_rj'];

        return [
            'total_nilai_klaim' => $this->valueTotalMany($tables, $period),
            'jumlah_baris_klaim' => $this->rowCountMany($tables, $period),
            'klaim_iks' => $this->valueTotal('klaim_iks', $period),
            'klaim_inacbg' => $this->valueTotalMany(['klaim_inacbg', 'klaim_inacbg_ri', 'klaim_inacbg_rj'], $period),
            'by_payment' => $this->topBy('klaim_iks', 'CARABAYAR', $period),
        ];
    }
}
