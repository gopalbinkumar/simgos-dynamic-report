<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AnalyticsController extends Controller
{
    private const SECTIONS = ['diagnosa', 'klaim', 'pasien-kunjungan', 'pelayanan-igd', 'keuangan', 'statistik-indikator'];

    public function __construct(private AnalyticsService $analyticsService)
    {
    }

    public function index(Request $request, string $section)
    {
        abort_unless(in_array($section, self::SECTIONS, true), 404);

        $searches = collect((array) $request->input('search', []))
            ->filter(fn (mixed $value, mixed $table): bool => is_string($table) && is_string($value))
            ->map(fn (string $value): string => mb_substr(trim($value), 0, 100))
            ->filter(fn (string $value): bool => $value !== '')
            ->all();

        $filters = [
            'date_from' => $this->dateInput($request->input('date_from'), now()->startOfMonth()),
            'date_to' => $this->dateInput($request->input('date_to'), now()),
            'unit' => (string) $request->input('unit', ''),
            'payment' => (string) $request->input('payment', ''),
            'search' => $searches,
        ];

        if ($filters['date_from'] > $filters['date_to']) {
            [$filters['date_from'], $filters['date_to']] = [$filters['date_to'], $filters['date_from']];
        }

        return view('analytics.index', $this->analyticsService->page($section, $filters));
    }

    private function dateInput(mixed $value, Carbon $fallback): string
    {
        try {
            return Carbon::createFromFormat('Y-m-d', (string) $value)->toDateString();
        } catch (\Throwable) {
            return $fallback->toDateString();
        }
    }
}
