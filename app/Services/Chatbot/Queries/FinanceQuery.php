<?php

namespace App\Services\Chatbot\Queries;

class FinanceQuery extends BaseQuery
{
    public function summary(array $period): array
    {
        return [
            'total_pendapatan' => $this->valueTotal('pendapatan', $period),
            'total_penerimaan' => $this->valueTotal('penerimaan', $period),
            'baris_pendapatan' => $this->rowCount('pendapatan', $period),
            'baris_penerimaan' => $this->rowCount('penerimaan', $period),
            'pendapatan_by_payment' => $this->topBy('pendapatan', 'CARABAYAR', $period),
            'penerimaan_by_payment' => $this->topBy('penerimaan', 'CARABAYAR', $period),
            'daily_pendapatan' => $this->dailyTotal('pendapatan', $period),
        ];
    }
}
