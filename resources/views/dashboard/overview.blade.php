@extends('layouts.app')

@section('title', 'Dashboard Overview | SIMGOS Dynamic Report')

@section('content')
    <div class="page-header">
        <div>
            <div class="breadcrumb"><span>Dashboard</span><i class="fa-solid fa-chevron-right"></i><strong>Overview</strong>
            </div>
            <h1>Dashboard Overview</h1>
            <p>Ringkasan data SIMRS SIMGOS untuk pemantauan operasional.</p>
        </div>
        <a href="{{ route('reports.dynamic') }}" class="button button-primary"><i class="fa-solid fa-pen"></i>
            Buat Dynamic Report</a>
    </div>

    <form class="card filter-card" method="GET" action="{{ route('dashboard') }}">
        <div class="card-heading compact-heading">
            <div><span class="eyebrow">FILTER DATASET</span>
                <h2>Periode & unit pelayanan</h2>
            </div><a class="text-button" href="{{ route('dashboard') }}"><i class="fa-solid fa-rotate-left"></i> Reset</a>
        </div>
        <div class="filter-grid dashboard-filter-grid">
            <label class="field"><span>Dari tanggal</span><input type="date" name="date_from"
                    value="{{ $filters['date_from'] }}"></label>
            <label class="field"><span>Sampai tanggal</span><input type="date" name="date_to"
                    value="{{ $filters['date_to'] }}"></label>
            <label class="field"><span>Unit / Poli</span><select name="unit">
                    <option value="">Semua unit dan poli</option>
                    @foreach ($filterOptions['units'] as $unit)
                        <option value="{{ $unit }}" @selected($filters['unit'] === $unit)>{{ $unit }}</option>
                    @endforeach
                </select></label>
        </div>
        <div class="filter-actions" style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
            <span><i class="fa-solid fa-database"></i> Chart menggunakan agregasi read-only dari database SIMGOS.</span>
            <button type="submit" class="button button-primary">
                <i class="fa-solid fa-filter"></i> Terapkan Filter
            </button>
        </div>
    </form>

    @if ($dataWarning)
        <div class="flash flash-error"><i class="fa-solid fa-triangle-exclamation"></i>{{ $dataWarning }}</div>
    @endif

    <div class="section-heading">
        <div><span class="eyebrow">EXECUTIVE SUMMARY</span>
            <h2>Indikator utama</h2>
        </div><span class="last-updated"><i class="fa-regular fa-clock"></i>
            {{ $lastUpdated ? 'Diperbarui ' . $lastUpdated : 'Belum ada data' }}</span>
    </div>
    <div class="stats-grid">
        @foreach ($stats as $stat)
            <div class="stat-card">
                <div class="stat-top"><span class="stat-label">{{ $stat['label'] }}</span><span
                        class="stat-icon tone-{{ $stat['tone'] }}"><i class="fa-solid {{ $stat['icon'] }}"></i></span></div>
                <div class="stat-value">{{ $stat['value'] }}</div>
                <div class="stat-description"><i class="fa-solid fa-arrow-trend-up"></i> {{ $stat['description'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="section-heading dataset-section-heading">
        <div><span class="eyebrow">BUSINESS DATASETS</span>
            <h2>Cakupan dataset SIMGOS</h2>
        </div><span class="last-updated"><i class="fa-solid fa-layer-group"></i> {{ count($datasetCards) }} kelompok data</span>
    </div>
    <div class="dataset-grid">
        @foreach ($datasetCards as $dataset)
            <div class="card dataset-card">
                <div class="dataset-card-top">
                    <span class="dataset-icon tone-{{ $dataset['tone'] }}"><i class="fa-solid {{ $dataset['icon'] }}"></i></span>
                    <div>
                        <strong>{{ $dataset['label'] }}</strong>
                        <span>{{ $dataset['source_count'] }}/{{ $dataset['table_count'] }} tabel aktif</span>
                    </div>
                </div>
                <p>{{ $dataset['description'] }}</p>
                <div class="dataset-card-bottom"><span>Baris dalam periode</span><strong>{{ $dataset['rows'] }}</strong></div>
            </div>
        @endforeach
    </div>

    <div class="section-heading chart-section-title">
        <div><span class="eyebrow">ANALYTICS</span>
            <h2>Visualisasi data</h2>
        </div><a class="text-button" href="{{ route('reports.dynamic') }}">Lihat laporan lengkap <i
                class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="chart-grid chart-grid-main">
        <div class="card chart-card chart-span-8">
            <div class="card-heading">
                <div>
                    <h2>Trend Data</h2>
                    <p>Volume data per hari</p>
                </div><span class="chart-menu"><i class="fa-solid fa-ellipsis"></i></span>
            </div>
            <div class="chart-container"><canvas data-chart="trend"></canvas></div>
        </div>
        <div class="card chart-card chart-span-4">
            <div class="card-heading">
                <div>
                    <h2>Kunjungan per Instalasi</h2>
                    <p>Rawat jalan, rawat inap, dan layanan lainnya</p>
                </div>
            </div>
            <div class="chart-container"><canvas data-chart="installation"></canvas></div>
        </div>
        <div class="card chart-card chart-span-5">
            <div class="card-heading">
                <div>
                    <h2>Distribusi Cara Bayar</h2>
                    <p>Proporsi kunjungan berdasarkan cara bayar</p>
                </div>
            </div>
            <div class="chart-container doughnut-container"><canvas data-chart="payment"></canvas></div>
        </div>
        <div class="card chart-card chart-span-7">
            <div class="card-heading">
                <div>
                    <h2>Pendapatan vs Penerimaan</h2>
                    <p>Perbandingan nilai keuangan per hari</p>
                </div>
            </div>
            <div class="chart-container"><canvas data-chart="finance"></canvas></div>
        </div>
        <div class="card chart-card chart-span-12">
            <div class="card-heading">
                <div>
                    <h2>Kunjungan Berdasarkan Unit / Poli</h2>
                    <p>Sepuluh unit atau poli dengan aktivitas tertinggi</p>
                </div>
            </div>
            <div class="chart-container"><canvas data-chart="unit"></canvas></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.simgosCharts = @json($charts);
    </script>
@endpush
