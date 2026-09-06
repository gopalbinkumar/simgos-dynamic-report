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
        <a href="{{ route('reports.dynamic') }}" class="button button-primary"><i class="fa-solid fa-wand-magic-sparkles"></i>
            Buat Dynamic Report</a>
    </div>

    <form class="card filter-card" method="GET" action="{{ route('dashboard') }}">
        <div class="card-heading compact-heading">
            <div><span class="eyebrow">FILTER DATA</span>
                <h2>Periode & dimensi</h2>
            </div><button class="text-button" type="reset"><i class="fa-solid fa-rotate-left"></i> Reset</button>
        </div>
        <div class="filter-grid">
            <label class="field"><span>Dari tanggal</span><input type="date" name="date_from"
                    value="{{ $filters['date_from'] }}"></label>
            <label class="field"><span>Sampai tanggal</span><input type="date" name="date_to"
                    value="{{ $filters['date_to'] }}"></label>
            <label class="field"><span>Unit / Poli</span><select name="unit">
                    <option value="">Semua unit</option>
                    <option value="rawat-jalan" @selected($filters['unit'] === 'rawat-jalan')>Rawat Jalan</option>
                    <option value="rawat-inap" @selected($filters['unit'] === 'rawat-inap')>Rawat Inap</option>
                    <option value="igd" @selected($filters['unit'] === 'igd')>IGD</option>
                </select></label>
            <label class="field"><span>Ruangan</span><select name="room">
                    <option value="">Semua ruangan</option>
                    <option value="ruang-a" @selected($filters['room'] === 'ruang-a')>Ruang A</option>
                    <option value="ruang-b" @selected($filters['room'] === 'ruang-b')>Ruang B</option>
                    <option value="igd" @selected($filters['room'] === 'igd')>IGD</option>
                </select></label>
            <label class="field"><span>Dokter</span><select name="doctor">
                    <option value="">Semua dokter</option>
                    <option value="dr-ahmad" @selected($filters['doctor'] === 'dr-ahmad')>dr. Ahmad</option>
                    <option value="dr-siti" @selected($filters['doctor'] === 'dr-siti')>dr. Siti</option>
                    <option value="dr-budi" @selected($filters['doctor'] === 'dr-budi')>dr. Budi</option>
                </select></label>
            <label class="field"><span>Kategori</span><select name="category">
                    <option value="">Semua kategori</option>
                    <option value="pelayanan" @selected($filters['category'] === 'pelayanan')>Pelayanan</option>
                    <option value="kunjungan" @selected($filters['category'] === 'kunjungan')>Kunjungan</option>
                    <option value="diagnosa" @selected($filters['category'] === 'diagnosa')>Diagnosa</option>
                </select></label>
        </div>
        <div class="filter-actions" style="display: flex; align-items: center; gap: 12px;">
            <button type="submit" class="button button-primary">
                <i class="fa-solid fa-filter"></i> Terapkan Filter
            </button>
        </div>
    </form>

    <div class="section-heading">
        <div><span class="eyebrow">EXECUTIVE SUMMARY</span>
            <h2>Indikator utama</h2>
        </div><span class="last-updated"><i class="fa-regular fa-clock"></i> Diperbarui hari ini</span>
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
                    <h2>Data Berdasarkan Kategori</h2>
                    <p>Perbandingan volume kategori</p>
                </div>
            </div>
            <div class="chart-container"><canvas data-chart="category"></canvas></div>
        </div>
        <div class="card chart-card chart-span-5">
            <div class="card-heading">
                <div>
                    <h2>Distribusi Data</h2>
                    <p>Proporsi data pada dashboard</p>
                </div>
            </div>
            <div class="chart-container doughnut-container"><canvas data-chart="distribution"></canvas></div>
        </div>
        <div class="card chart-card chart-span-7">
            <div class="card-heading">
                <div>
                    <h2>Data Berdasarkan Unit / Poli</h2>
                    <p>Unit dengan aktivitas tertinggi</p>
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
