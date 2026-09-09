<?php

namespace App\Services;

class AiIntentRouter
{
    private const OUT_OF_SCOPE = [
        'cuaca', 'berita', 'politik', 'resep', 'masakan', 'film', 'musik',
        'game', 'olahraga', 'lirik', 'jodoh', 'wisata', 'password', 'coding',
    ];

    private const NOT_AVAILABLE = [
        'data dokter', 'dokter', 'pegawai', 'data ruangan', 'master data',
    ];

    private const INTENTS = [
        'diagnosis' => [
            'diagnosa', 'diagnosis', 'penyakit',
        ],
        'claims' => [
            'klaim', 'ina-cbg', 'inacbg', 'iks',
        ],
        'visits' => [
            'kunjungan', 'pengunjung', 'pasien', 'rawat jalan', 'rawat inap',
            'rawat darurat', 'poli', 'unit', 'instalasi',
        ],
        'services' => [
            'pelayanan', 'penunjang', 'igd', 'gawat darurat', 'tempat tidur',
            'bed', 'fast track', 'nedocs', 'kamar',
        ],
        'finance' => [
            'pendapatan', 'penerimaan', 'keuangan', 'pembayaran', 'cara bayar',
        ],
        'statistics' => [
            'statistik', 'indikator', 'bor', 'alos', 'bto', 'toi', 'ndr', 'gdr',
            'rujukan', 'kematian', 'laboratorium', 'mutu pelayanan',
        ],
        'overview' => [
            'ringkasan', 'dashboard', 'laporan keseluruhan', 'semua data',
            'cakupan data', 'data apa saja', 'simgos', 'simrs', 'rumah sakit',
        ],
    ];

    public function route(string $message): array
    {
        $normalized = $this->normalize($message);

        foreach (self::OUT_OF_SCOPE as $keyword) {
            if ($this->contains($normalized, $keyword)) {
                return [
                    'intent' => 'out_of_scope',
                    'allowed' => false,
                    'message' => 'Maaf, saya hanya dapat membantu pertanyaan terkait data dan analitik rumah sakit.',
                ];
            }
        }

        foreach (self::NOT_AVAILABLE as $keyword) {
            if ($this->contains($normalized, $keyword)) {
                return [
                    'intent' => 'not_available',
                    'allowed' => false,
                    'message' => 'Dataset tersebut belum termasuk sumber data analitik yang tersedia saat ini.',
                ];
            }
        }

        foreach (self::INTENTS as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if ($this->contains($normalized, $keyword)) {
                    return [
                        'intent' => $intent,
                        'allowed' => true,
                        'message' => null,
                    ];
                }
            }
        }

        return [
            'intent' => 'out_of_scope',
            'allowed' => false,
            'message' => 'Maaf, saya hanya dapat membantu pertanyaan terkait data dan analitik rumah sakit.',
        ];
    }

    private function normalize(string $message): string
    {
        return trim((string) preg_replace('/\s+/', ' ', mb_strtolower($message)));
    }

    private function contains(string $message, string $keyword): bool
    {
        return str_contains($message, mb_strtolower($keyword));
    }
}
