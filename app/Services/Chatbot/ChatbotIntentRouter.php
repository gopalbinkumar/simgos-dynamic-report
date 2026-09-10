<?php

namespace App\Services\Chatbot;

class ChatbotIntentRouter
{
    /**
     * Istilah singkat yang umum dipakai saat bertanya melalui chatbot.
     * Normalisasi ini dilakukan sebelum intent dan periode dianalisis.
     */
    private const ALIASES = [
        'brp' => 'berapa',
        'jml' => 'jumlah',
        'tgl' => 'tanggal',
        'pend' => 'pendapatan',
        'pndptn' => 'pendapatan',
        'pendaptan' => 'pendapatan',
        'penerim' => 'penerimaan',
        'kunj' => 'kunjungan',
        'kwnjungan' => 'kunjungan',
        'diag' => 'diagnosa',
        'stat' => 'statistik',
    ];

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
            'rawat darurat', 'poli', 'unit', 'instalasi', 'tren',
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
        $normalized = mb_strtolower($message);

        foreach (self::ALIASES as $alias => $replacement) {
            $normalized = (string) preg_replace(
                '/(?<![\p{L}\p{N}])' . preg_quote($alias, '/') . '(?![\p{L}\p{N}])/u',
                $replacement,
                $normalized
            );
        }

        return trim((string) preg_replace('/\s+/', ' ', $normalized));
    }

    private function contains(string $message, string $keyword): bool
    {
        $keyword = mb_strtolower($keyword);

        if (str_contains($message, $keyword)) {
            return true;
        }

        // Fuzzy match hanya untuk satu kata agar typo ringan tetap terbaca,
        // tanpa membuat frasa seperti "data dokter" terlalu longgar.
        if (str_contains($keyword, ' ') || mb_strlen($keyword) < 5) {
            return false;
        }

        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $message, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $maxDistance = mb_strlen($keyword) >= 8 ? 2 : 1;

        foreach ($tokens as $token) {
            if (abs(mb_strlen($token) - mb_strlen($keyword)) > $maxDistance) {
                continue;
            }

            if (levenshtein($token, $keyword) <= $maxDistance) {
                return true;
            }
        }

        return false;
    }
}
