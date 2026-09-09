<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportingController extends Controller
{
    public function savedReports(Request $request)
    {
        $records = [
            ['name' => 'Kunjungan Harian', 'source' => 'kunjungan', 'columns' => 5, 'filters' => 2, 'created_at' => '2026-09-01', 'last_used' => '2026-09-02'],
            ['name' => 'Ringkasan Diagnosa', 'source' => 'diagnosa_rj', 'columns' => 4, 'filters' => 1, 'created_at' => '2026-08-28', 'last_used' => '2026-09-01'],
            ['name' => 'Indikator RS Bulanan', 'source' => 'indikator_rs', 'columns' => 7, 'filters' => 3, 'created_at' => '2026-08-25', 'last_used' => '2026-08-30'],
        ];

        return view('reports.saved', ['records' => $this->paginate($records, $request, 'saved_page')]);
    }

    public function history(Request $request)
    {
        $records = [
            ['report' => 'Kunjungan Harian', 'user' => 'Operator Dashboard', 'time' => '2026-09-02 10:42:12', 'duration' => '1,42 detik', 'count' => 248, 'status' => 'Success'],
            ['report' => 'Ringkasan Diagnosa', 'user' => 'Operator Dashboard', 'time' => '2026-09-02 09:17:04', 'duration' => '2,08 detik', 'count' => 184, 'status' => 'Success'],
            ['report' => 'Indikator RS Bulanan', 'user' => 'Operator Dashboard', 'time' => '2026-09-01 16:25:41', 'duration' => '0,86 detik', 'count' => 0, 'status' => 'Failed'],
        ];

        return view('reports.history', ['records' => $this->paginate($records, $request, 'history_page')]);
    }

    private function paginate(array $records, Request $request, string $pageName): LengthAwarePaginator
    {
        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);
        $items = collect($records);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => $pageName,
            ],
        );
    }
}
