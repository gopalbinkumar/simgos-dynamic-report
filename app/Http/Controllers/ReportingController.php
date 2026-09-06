<?php

namespace App\Http\Controllers;

class ReportingController extends Controller
{
    public function savedReports()
    {
        return view('reports.saved', ['records' => [
            ['name' => 'Kunjungan Harian', 'source' => 'kunjungan', 'columns' => 5, 'filters' => 2, 'created_at' => '2026-09-01', 'last_used' => '2026-09-02'],
            ['name' => 'Ringkasan Diagnosa', 'source' => 'diagnosa_rj', 'columns' => 4, 'filters' => 1, 'created_at' => '2026-08-28', 'last_used' => '2026-09-01'],
            ['name' => 'Indikator RS Bulanan', 'source' => 'indikator_rs', 'columns' => 7, 'filters' => 3, 'created_at' => '2026-08-25', 'last_used' => '2026-08-30'],
        ]]);
    }

    public function history()
    {
        return view('reports.history', ['records' => [
            ['report' => 'Kunjungan Harian', 'user' => 'Operator Dashboard', 'time' => '2026-09-02 10:42:12', 'duration' => '1,42 detik', 'count' => 248, 'status' => 'Success'],
            ['report' => 'Ringkasan Diagnosa', 'user' => 'Operator Dashboard', 'time' => '2026-09-02 09:17:04', 'duration' => '2,08 detik', 'count' => 184, 'status' => 'Success'],
            ['report' => 'Indikator RS Bulanan', 'user' => 'Operator Dashboard', 'time' => '2026-09-01 16:25:41', 'duration' => '0,86 detik', 'count' => 0, 'status' => 'Failed'],
        ]]);
    }
}
