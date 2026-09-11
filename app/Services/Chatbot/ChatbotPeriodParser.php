<?php

namespace App\Services\Chatbot;

use App\Support\SimgosData;
use Illuminate\Support\Carbon;

class ChatbotPeriodParser
{
    public function parse(string $question): array
    {
        $now = Carbon::now();
        $normalized = mb_strtolower($question);

        if (str_contains($normalized, 'hari ini')) {
            $from = $now->copy()->startOfDay();
            $to = $now->copy()->endOfDay();
        } elseif (str_contains($normalized, 'kemarin')) {
            $from = $now->copy()->subDay()->startOfDay();
            $to = $now->copy()->subDay()->endOfDay();
        } elseif (str_contains($normalized, 'bulan lalu') || str_contains($normalized, 'bulan sebelumnya')) {
            $from = $now->copy()->subMonthNoOverflow()->startOfMonth();
            $to = $now->copy()->subMonthNoOverflow()->endOfMonth();
        } elseif (str_contains($normalized, 'tahun ini')) {
            $from = $now->copy()->startOfYear();
            $to = $now->copy()->endOfYear();
        } else {
            $from = $now->copy()->startOfMonth();
            $to = $now->copy()->endOfDay();
        }

        $dates = collect();

        if (preg_match_all('/\b(\d{4})-(\d{1,2})-(\d{1,2})\b/', $question, $isoMatches) >= 1) {
            foreach ($isoMatches[0] as $date) {
                $dates->push(Carbon::createFromFormat('Y-m-d', $date));
            }
        }

        if (preg_match_all('/\b(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})\b/', $question, $dmyMatches) >= 1) {
            foreach ($dmyMatches[0] as $date) {
                $dates->push(Carbon::createFromFormat('d/m/Y', str_replace('-', '/', $date)));
            }
        }

        $monthNames = [
            'januari' => 1, 'jan' => 1,
            'februari' => 2, 'feb' => 2,
            'maret' => 3, 'mar' => 3,
            'april' => 4, 'apr' => 4,
            'mei' => 5, 'may' => 5,
            'juni' => 6, 'jun' => 6,
            'juli' => 7, 'jul' => 7,
            'agustus' => 8, 'agu' => 8, 'ags' => 8, 'aug' => 8,
            'september' => 9, 'sep' => 9, 'sept' => 9,
            'oktober' => 10, 'okt' => 10, 'oct' => 10,
            'november' => 11, 'nov' => 11,
            'desember' => 12, 'des' => 12, 'dec' => 12,
        ];

        $monthPattern = implode('|', array_keys($monthNames));
        if (preg_match_all('/\b(\d{1,2})\s+(' . $monthPattern . ')(?:\s+(\d{4}))?\b/i', $normalized, $namedMatches, PREG_SET_ORDER)) {
            foreach ($namedMatches as $match) {
                $year = isset($match[3]) && $match[3] !== '' ? (int) $match[3] : $now->year;
                $month = $monthNames[mb_strtolower($match[2])];
                $day = (int) $match[1];

                if (checkdate($month, $day, $year)) {
                    $dates->push(Carbon::create($year, $month, $day));
                }
            }
        }

        // Mendukung pertanyaan singkat seperti "tgl 1" atau "tanggal 1".
        // Jika bulan dan tahun tidak disebutkan, gunakan bulan dan tahun berjalan.
        $shortDatePattern = '/\b(?:tanggal|tgl)\.?\s*(\d{1,2})(?:\s+('
            . $monthPattern . ')(?:\s+(\d{4}))?)?\b/i';

        if (preg_match_all($shortDatePattern, $normalized, $shortDateMatches, PREG_SET_ORDER)) {
            foreach ($shortDateMatches as $match) {
                $day = (int) $match[1];
                $month = !empty($match[2])
                    ? $monthNames[mb_strtolower($match[2])]
                    : $now->month;
                $year = !empty($match[3]) ? (int) $match[3] : $now->year;

                if (checkdate($month, $day, $year)) {
                    $dates->push(Carbon::create($year, $month, $day));
                }
            }
        }

        if ($dates->isNotEmpty()) {
            $dates = $dates->sort()->values();
            $from = $dates->first()->copy()->startOfDay();
            $to = ($dates->count() > 1 ? $dates->last() : $dates->first())->copy()->endOfDay();
        }

        return [
            'from' => $from,
            'to' => $to,
            'unit' => $this->findUnit($question),
            'payment' => $this->findPayment($question),
            'label' => $from->isSameDay($to)
                ? $from->format('d-m-Y')
                : $from->format('d-m-Y') . ' sampai ' . $to->format('d-m-Y'),
        ];
    }

    private function findUnit(string $question): ?string
    {
        if (!SimgosData::tableExists('kunjungan')) {
            return null;
        }

        $normalized = mb_strtolower($question);
        $candidates = collect();

        foreach (['SUBUNIT', 'UNIT', 'INSTALASI'] as $column) {
            if (in_array($column, SimgosData::columns('kunjungan'), true)) {
                $candidates = $candidates->merge(
                    SimgosData::query('kunjungan')
                        ->whereNotNull($column)
                        ->where($column, '<>', '')
                        ->distinct()
                        ->pluck($column)
                );
            }
        }

        $candidates = $candidates
            ->map(fn($value): string => trim((string) $value))
            ->filter(fn(string $value): bool => $value !== '')
            ->unique()
            ->values();

        $exact = $candidates
            ->filter(fn(string $value): bool => str_contains($normalized, mb_strtolower($value)))
            ->sortByDesc(fn(string $value): int => mb_strlen($value))
            ->first();

        if ($exact) {
            return $exact;
        }

        return $candidates
            ->filter(fn(string $value): bool => $this->fuzzyContains($normalized, $value))
            ->sortByDesc(fn(string $value): int => mb_strlen($value))
            ->first();
    }

    private function findPayment(string $question): ?string
    {
        if (!SimgosData::tableExists('kunjungan')) {
            return null;
        }

        $columns = SimgosData::columns('kunjungan');
        $paymentColumn = collect(['CARABAYAR', 'CARA_BAYAR', 'JENIS_BAYAR', 'PENJAMIN'])
            ->first(fn(string $column): bool => in_array($column, $columns, true));

        if (!$paymentColumn) {
            return null;
        }

        $normalized = mb_strtolower($question);
        $payments = SimgosData::query('kunjungan')
            ->whereNotNull($paymentColumn)
            ->where($paymentColumn, '<>', '')
            ->distinct()
            ->pluck($paymentColumn)
            ->map(fn($value): string => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        return $payments
            ->filter(fn(string $value): bool => str_contains($normalized, mb_strtolower($value)))
            ->sortByDesc(fn(string $value): int => mb_strlen($value))
            ->first();
    }

    private function fuzzyContains(string $question, string $candidate): bool
    {
        $questionTokens = preg_split('/[^\p{L}\p{N}]+/u', $question, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $candidateTokens = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($candidate), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (!$candidateTokens || count($candidateTokens) > count($questionTokens)) {
            return false;
        }

        $candidateText = implode(' ', $candidateTokens);
        $maxDistance = mb_strlen($candidateText) >= 15 ? 2 : 1;

        for ($index = 0; $index <= count($questionTokens) - count($candidateTokens); $index++) {
            $window = implode(' ', array_slice($questionTokens, $index, count($candidateTokens)));

            if (levenshtein($window, $candidateText) <= $maxDistance) {
                return true;
            }
        }

        return false;
    }
}
