<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiService
{
    public function ask(string $question, array $history = [], ?array $databaseContext = null): string
    {
        $contents = collect($history)
            ->map(function (array $message) {
                return [
                    'role' => ($message['role'] ?? 'user') === 'assistant'
                        ? 'model'
                        : 'user',
                    'parts' => [
                        [
                            'text' => (string) ($message['content'] ?? ''),
                        ],
                    ],
                ];
            })
            ->push([
                'role' => 'user',
                'parts' => [
                    [
                        'text' => $question,
                    ],
                ],
            ])
            ->values()
            ->all();

        $url = rtrim(config('services.gemini.endpoint'), '/')
            . '/'
            . config('services.gemini.model')
            . ':generateContent';

        $systemInstruction = 'Anda adalah asisten analitik rumah sakit. '
            . 'Jawab hanya berdasarkan DATA DATABASE yang disediakan aplikasi. '
            . 'Jangan mengarang angka, nama unit, periode, atau kesimpulan yang tidak ada di data. '
            . 'Jika data bernilai nol atau kosong, katakan bahwa tidak ada data pada periode tersebut. '
            . 'Jawab dalam Bahasa Indonesia, singkat, jelas, dan profesional. '
            . 'Jangan menampilkan ID pasien, identitas pribadi, atau data mentah. '
            . 'Jangan memberikan diagnosis medis atau nasihat medis. '
            . 'Jika pertanyaan tidak dapat dijawab dari DATA DATABASE, katakan bahwa data tersebut belum tersedia.';

        if ($databaseContext !== null) {
            $systemInstruction .= "\n\nDATA DATABASE (read-only, hasil agregasi Laravel):\n"
                . json_encode($databaseContext, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        $response = Http::timeout(60)
            ->retry(2, 500)
            ->withHeaders([
                'x-goog-api-key' => config('services.gemini.key'),
                'Content-Type' => 'application/json',
            ])
            ->post($url, [
                'systemInstruction' => [
                    'parts' => [
                        [
                            'text' => $systemInstruction,
                        ],
                    ],
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 512,
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Gemini API error: ' . $response->status()
            );
        }

        $answer = $response->json('candidates.0.content.parts.0.text');

        if (!is_string($answer) || trim($answer) === '') {
            throw new RuntimeException('Gemini tidak memberikan jawaban.');
        }

        return $answer;
    }
}
