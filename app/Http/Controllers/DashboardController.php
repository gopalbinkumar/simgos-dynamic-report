<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('dashboard.overview', [
            'filters' => [
                'date_from' => $request->input('date_from', now()->startOfMonth()->toDateString()),
                'date_to' => $request->input('date_to', now()->toDateString()),
                'unit' => $request->input('unit'),
                'room' => $request->input('room'),
                'doctor' => $request->input('doctor'),
                'category' => $request->input('category'),
            ],
            // Dashboard metrics stay mock-driven for this UI phase, ready to be replaced by read-only queries.
            'stats' => [
                ['label' => 'Total Data', 'value' => '12.480', 'description' => 'Seluruh data teragregasi', 'icon' => 'fa-database', 'tone' => 'primary'],
                ['label' => 'Data Hari Ini', 'value' => '248', 'description' => 'Naik 12,5% dari kemarin', 'icon' => 'fa-arrow-trend-up', 'tone' => 'success'],
                ['label' => 'Data Bulan Ini', 'value' => '5.920', 'description' => 'Periode berjalan', 'icon' => 'fa-calendar-days', 'tone' => 'info'],
                ['label' => 'Kategori', 'value' => '18', 'description' => 'Kategori aktif terpantau', 'icon' => 'fa-tags', 'tone' => 'warning'],
            ],
            'charts' => [
                'trend' => ['labels' => ['27 Agu', '28 Agu', '29 Agu', '30 Agu', '31 Agu', '1 Sep', '2 Sep'], 'data' => [120, 185, 164, 232, 208, 276, 248]],
                'category' => ['labels' => ['Pelayanan', 'Kunjungan', 'Diagnosa', 'Klaim', 'Indikator'], 'data' => [420, 305, 260, 198, 152]],
                'distribution' => ['labels' => ['Rawat Jalan', 'Rawat Inap', 'IGD', 'Penunjang'], 'data' => [38, 27, 20, 15]],
                'unit' => ['labels' => ['Penyakit Dalam', 'Jantung', 'Saraf', 'Anak', 'IGD', 'Radiologi'], 'data' => [315, 278, 224, 176, 143, 98]],
            ],
        ]);
    }
}
