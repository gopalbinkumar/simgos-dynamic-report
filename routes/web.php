<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DynamicReportController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\SystemController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.overview');

Route::get('/analytics/{section}', [AnalyticsController::class, 'index'])
    ->whereIn('section', ['diagnosa', 'klaim', 'pasien-kunjungan', 'pelayanan-igd', 'keuangan', 'statistik-indikator'])
    ->name('analytics.index');

Route::get('/dynamic-report', [DynamicReportController::class, 'index'])->name('reports.dynamic');
Route::post('/dynamic-report', [DynamicReportController::class, 'run'])->name('reports.dynamic.run');
Route::get('/dynamic-report/reset', [DynamicReportController::class, 'reset'])->name('reports.dynamic.reset');
Route::get('/dynamic-report/export', [DynamicReportController::class, 'export'])->name('reports.dynamic.export');

Route::get('/master-data/{resource}', [MasterDataController::class, 'index'])
    ->whereIn('resource', ['informasi', 'pegawai', 'dokter', 'poli', 'ruangan'])
    ->name('master-data.index');

Route::get('/saved-reports', [ReportingController::class, 'savedReports'])->name('reports.saved');
Route::get('/report-history', [ReportingController::class, 'history'])->name('reports.history');

Route::get('/database-status', [SystemController::class, 'databaseStatus'])->name('system.database-status');
Route::get('/about', [SystemController::class, 'about'])->name('system.about');

Route::post('/chatbot', ChatbotController::class)
    ->middleware('throttle:20,1')
    ->name('chatbot.send');

