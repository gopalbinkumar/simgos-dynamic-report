<?php

namespace App\Services\Chatbot;

class ChatbotResponseService
{
    public function build(string $intent, array $data, array $period, string $question = ''): string
    {
        $prefix = 'Periode ' . $period['label'] . '. ';

        return match ($intent) {
            'overview' => $prefix
                . 'total kunjungan ' . $this->number($data['total_kunjungan'] ?? 0)
                . ', total pengunjung ' . $this->number($data['total_pengunjung'] ?? 0)
                . ', pasien rawat inap ' . $this->number($data['pasien_rawat_inap'] ?? 0)
                . ', dan pendapatan ' . $this->currency($data['total_pendapatan'] ?? 0) . '.',
            'visits' => $this->visits($prefix, $data, $question),
            'diagnosis' => $this->diagnosis($prefix, $data),
            'claims' => $prefix
                . 'total nilai klaim ' . $this->currency($data['total_nilai_klaim'] ?? 0)
                . ' dari ' . $this->number($data['jumlah_baris_klaim'] ?? 0) . ' baris klaim.',
            'services' => $prefix
                . 'pelayanan penunjang ' . $this->number($data['total_pelayanan_penunjang'] ?? 0)
                . ', pengukuran IGD ' . $this->number($data['jumlah_pengukuran_igd'] ?? 0)
                . ', dan kapasitas tempat tidur ' . $this->number($data['total_tempat_tidur'] ?? 0) . '.',
            'finance' => $prefix
                . 'total pendapatan ' . $this->currency($data['total_pendapatan'] ?? 0)
                . ' dan total penerimaan ' . $this->currency($data['total_penerimaan'] ?? 0) . '.',
            'statistics' => $this->statistics($prefix, $data),
            default => 'Data belum tersedia untuk pertanyaan tersebut.',
        };
    }

    private function visits(string $prefix, array $data, string $question): string
    {
        if (!empty($data['requested_unit'])) {
            $answer = $prefix . 'jumlah kunjungan ' . $data['requested_unit']
                . ' adalah ' . $this->number($data['total_kunjungan'] ?? 0) . '.';

            if (($data['total_kunjungan'] ?? 0) == 0) {
                $answer = $prefix . 'tidak tercatat kunjungan untuk ' . $data['requested_unit'] . '.';
            }

            return $answer;
        }

        if ($this->asksUnitBreakdown($question)) {
            $rows = collect($data['unit_breakdown'] ?? $data['top_units'] ?? []);

            if ($this->asksPoliBreakdown($question)) {
                $poliRows = $rows->filter(fn(array $item): bool => str_contains(
                    mb_strtolower((string) ($item['label'] ?? '')),
                    'poli'
                ));

                if ($poliRows->isNotEmpty()) {
                    $rows = $poliRows;
                }
            }

            if ($rows->isEmpty()) {
                return $prefix . 'belum ada data kunjungan per poli yang tersedia.';
            }

            $details = $rows
                ->map(fn(array $item): string => $item['label'] . ': '
                    . $this->number($item['total']) . ' kunjungan')
                ->implode("\n");

            return $prefix . "rincian kunjungan per poli:\n" . $details;
        }

        $answer = $prefix
            . 'total kunjungan ' . $this->number($data['total_kunjungan'] ?? 0)
            . ', total pengunjung ' . $this->number($data['total_pengunjung'] ?? 0)
            . ', dan pasien rawat inap ' . $this->number($data['pasien_rawat_inap'] ?? 0) . '.';

        $top = $data['top_units'][0] ?? null;
        if ($top) {
            $answer .= ' Unit dengan jumlah tertinggi adalah ' . $top['label']
                . ' sebanyak ' . $this->number($top['total']) . '.';
        }

        if (str_contains(mb_strtolower($question), 'tren') && !empty($data['daily_trend'])) {
            $peak = collect($data['daily_trend'])->sortByDesc('total')->first();
            if ($peak) {
                $answer .= ' Tren harian tertinggi terjadi pada ' . $peak['date']
                    . ' sebanyak ' . $this->number($peak['total']) . ' kunjungan.';
            }
        }

        return $answer;
    }

    private function asksUnitBreakdown(string $question): bool
    {
        $normalized = mb_strtolower($question);

        foreach ([
            'per poli',
            'berdasarkan poli',
            'setiap poli',
            'rincian poli',
            'daftar poli',
            'per unit',
            'berdasarkan unit',
        ] as $phrase) {
            if (str_contains($normalized, $phrase)) {
                return true;
            }
        }

        return false;
    }

    private function asksPoliBreakdown(string $question): bool
    {
        $normalized = mb_strtolower($question);

        return str_contains($normalized, 'per poli')
            || str_contains($normalized, 'berdasarkan poli')
            || str_contains($normalized, 'setiap poli')
            || str_contains($normalized, 'rincian poli')
            || str_contains($normalized, 'daftar poli');
    }

    private function diagnosis(string $prefix, array $data): string
    {
        $answer = $prefix
            . 'total diagnosa ' . $this->number($data['total_diagnosa'] ?? 0)
            . ', terdiri dari rawat jalan ' . $this->number($data['rawat_jalan'] ?? 0)
            . ', rawat inap ' . $this->number($data['rawat_inap'] ?? 0)
            . ', dan rawat darurat ' . $this->number($data['rawat_darurat'] ?? 0) . '.';

        $top = collect($data['top_diagnosis'] ?? [])
            ->take(3)
            ->map(fn(array $item): string => $item['label'] . ' (' . $this->number($item['total']) . ')')
            ->implode(', ');

        return $top ? $answer . ' Diagnosa teratas: ' . $top . '.' : $answer;
    }

    private function statistics(string $prefix, array $data): string
    {
        $indicator = $data['latest_indicator'] ?? [];
        $answer = $prefix . 'statistik kunjungan rawat jalan '
            . $this->number($data['visit_statistics']['RJ'] ?? 0)
            . ', rawat darurat ' . $this->number($data['visit_statistics']['RD'] ?? 0)
            . ', dan rawat inap ' . $this->number($data['visit_statistics']['RI'] ?? 0) . '.';

        if ($indicator) {
            $answer .= ' Indikator terbaru yang tersedia: BOR ' . $this->decimal($indicator['bor'] ?? 0)
                . ', ALOS ' . $this->decimal($indicator['alos'] ?? 0)
                . ', BTO ' . $this->decimal($indicator['bto'] ?? 0)
                . ', TOI ' . $this->decimal($indicator['toi'] ?? 0) . '.';
        }

        return $answer;
    }

    private function number(mixed $value): string
    {
        return number_format((float) ($value ?? 0), 0, ',', '.');
    }

    private function decimal(mixed $value): string
    {
        return number_format((float) ($value ?? 0), 2, ',', '.');
    }

    private function currency(mixed $value): string
    {
        return 'Rp ' . $this->number($value);
    }
}
