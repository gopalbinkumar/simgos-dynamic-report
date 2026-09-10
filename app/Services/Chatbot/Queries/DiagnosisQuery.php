<?php

namespace App\Services\Chatbot\Queries;

class DiagnosisQuery extends BaseQuery
{
    public function summary(array $period): array
    {
        return [
            'total_diagnosa' => $this->valueTotalMany(['diagnosa_rd', 'diagnosa_ri', 'diagnosa_rj'], $period),
            'rawat_jalan' => $this->valueTotal('diagnosa_rj', $period),
            'rawat_inap' => $this->valueTotal('diagnosa_ri', $period),
            'rawat_darurat' => $this->valueTotal('diagnosa_rd', $period),
            'top_diagnosis' => $this->topDescriptions(['diagnosa_rd', 'diagnosa_ri', 'diagnosa_rj'], $period),
        ];
    }
}
