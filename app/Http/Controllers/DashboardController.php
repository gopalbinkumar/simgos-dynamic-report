<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'date_from' => $this->dateInput($request->input('date_from'), now()->startOfMonth()),
            'date_to' => $this->dateInput($request->input('date_to'), now()),
            'unit' => (string) $request->input('unit', ''),
        ];

        if ($filters['date_from'] > $filters['date_to']) {
            [$filters['date_from'], $filters['date_to']] = [$filters['date_to'], $filters['date_from']];
        }

        return view('dashboard.overview', ['filters' => $filters] + $this->dashboardService->overview($filters));
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
