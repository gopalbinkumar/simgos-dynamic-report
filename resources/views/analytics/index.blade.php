@extends('layouts.app')

@section('title', $section['title'] . ' | SIMGOS Dynamic Report')

@section('content')
    <div class="page-header">
        <div>
            <div class="breadcrumb"><span>Data & Analitik</span><i
                    class="fa-solid fa-chevron-right"></i><strong>{{ $section['title'] }}</strong></div>
            <h1>{{ $section['title'] }}</h1>
            <p>{{ $section['description'] }}</p>
        </div>
        <span class="page-header-status"><i class="fa-solid fa-database"></i> Read-only database</span>
    </div>

    <nav class="analytics-nav" aria-label="Data dan analitik">
        @foreach ($navigation as $item)
            <a href="{{ route('analytics.index', $item['key']) }}"
                class="analytics-nav-link {{ $item['key'] === $section['key'] ? 'active' : '' }}">
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    <form class="card filter-card" method="GET" action="{{ route('analytics.index', $section['key']) }}">
        <div class="card-heading compact-heading">
            <div><span class="eyebrow">FILTER DATA</span>
                <h2>Periode dan dimensi</h2>
            </div>
            <a class="text-button" href="{{ route('analytics.index', $section['key']) }}"><i
                    class="fa-solid fa-rotate-left"></i> Reset</a>
        </div>
        <div class="filter-grid analytics-filter-grid">
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
            <label class="field"><span>Cara Bayar</span><select name="payment">
                    <option value="">Semua cara bayar</option>
                    @foreach ($filterOptions['payments'] as $payment)
                        <option value="{{ $payment }}" @selected($filters['payment'] === $payment)>{{ $payment }}</option>
                    @endforeach
                </select></label>
        </div>
        <div class="filter-actions"><span><i class="fa-solid fa-circle-info"></i> Seluruh angka dan tabel halaman ini
                berasal dari database SIMGOS.</span><button type="submit" class="button button-primary"><i
                    class="fa-solid fa-filter"></i> Terapkan Filter</button></div>
    </form>

    <div class="stats-grid">
        @foreach ($stats as $stat)
            <div class="stat-card">
                <div class="stat-top"><span class="stat-label">{{ $stat['label'] }}</span>
                    <span
                        class="stat-icon tone-{{ $stat['tone'] }}" style="display: none;"><i class="fa-solid {{ $stat['icon'] }}"></i></span>
                </div>
                <div class="stat-value">{{ $stat['value'] }}</div>
                <div class="stat-description"><i class="fa-solid fa-database"></i> {{ $stat['description'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="section-heading chart-section-title">
        <div><span class="eyebrow">ANALYTICS</span>
            <h2>Visualisasi {{ $section['title'] }}</h2>
        </div>
        <span class="last-updated"><i class="fa-regular fa-clock"></i> Data diperbarui mengikuti sumber SIMGOS</span>
    </div>
    <div class="chart-grid chart-grid-main analytics-chart-grid">
        @foreach ($charts as $chart)
            <div class="card chart-card chart-span-6">
                <div class="card-heading">
                    <div>
                        <h2>{{ $chart['title'] }}</h2>
                        <p>{{ $chart['subtitle'] }}</p>
                    </div>
                </div>
                <div class="chart-container"><canvas id="{{ $chart['id'] }}"
                        data-analytics-chart="{{ $chart['id'] }}"></canvas></div>
            </div>
        @endforeach
    </div>

    <div class="section-heading analytics-data-heading">
        <div><span class="eyebrow">DATA DETAIL</span>
            <h2>Sumber data halaman</h2>
        </div>
        <span class="count-label">10 baris per halaman untuk setiap sumber</span>
    </div>
    <div class="card analytics-data-card">
        <div class="analytics-tabs" role="tablist" aria-label="Sumber data">
            @foreach ($sources as $index => $source)
                <button type="button" class="analytics-tab {{ $index === 0 ? 'active' : '' }}"
                    data-analytics-tab="table-{{ $source['key'] }}" role="tab"
                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                    {{ $source['label'] }} <span>{{ number_format($source['count'], 0, ',', '.') }}</span>
                </button>
            @endforeach
        </div>

        @foreach ($sources as $index => $source)
            <section id="table-{{ $source['key'] }}" class="analytics-panel {{ $index === 0 ? 'active' : '' }}"
                data-analytics-panel="table-{{ $source['key'] }}" role="tabpanel">
                <div class="table-toolbar">
                    <label class="search-field"><i class="fa-solid fa-magnifying-glass"></i><input type="search"
                            placeholder="Cari pada {{ $source['label'] }}..."
                            value="{{ $source['search'] }}" data-analytics-search="{{ $source['key'] }}"></label>
                    <span class="count-label">{{ number_format($source['count'], 0, ',', '.') }} baris pada periode
                        terpilih</span>
                </div>
                @if (!$source['exists'])
                    <div class="empty-state"><i class="fa-solid fa-table"></i><strong>Tabel belum
                            tersedia</strong><span>{{ $source['label'] }} belum ditemukan pada database aktif.</span></div>
                @elseif (count($source['rows']) === 0)
                    <div class="empty-state"><i class="fa-solid fa-database"></i><strong>Tidak ada data</strong><span>Tidak
                            ada baris pada periode atau filter yang dipilih.</span></div>
                @else
                    <div class="table-wrap">
                        <table class="data-table analytics-table" data-analytics-table="{{ $source['key'] }}">
                            <thead>
                                <tr>
                                    @foreach ($source['columns'] as $column)
                                        <th>{{ $column['label'] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($source['rows'] as $row)
                                    <tr>
                                        @foreach ($source['columns'] as $column)
                                            @php($value = $row[$column['key']] ?? '')
                                            <td
                                                class="{{ in_array($column['key'], ['KONTEN', 'DESKRIPSI', 'KOMENTAR', 'CATATAN'], true) ? 'table-cell-wrap' : '' }}">
                                                @if ($value === null || $value === '')
                                                    <span class="text-muted">—</span>
                                                @elseif ($column['key'] === 'VALUE' && $section['key'] === 'keuangan')
                                                    <strong>Rp {{ number_format((float) $value, 0, ',', '.') }}</strong>
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @include('partials.paginator', ['paginator' => $source['rows'], 'anchor' => 'table-' . $source['key']])
                @endif
            </section>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        const analyticsCharts = @json($charts);
        const chartAxis = {
            color: '#9ca3af',
            grid: {
                color: '#eef2f2',
                drawBorder: false
            },
            ticks: {
                font: {
                    size: 10
                },
                color: '#9ca3af'
            }
        };
        analyticsCharts.forEach((chart) => {
            const canvas = document.getElementById(chart.id);
            if (!canvas || !window.Chart || !chart.datasets?.length) return;
            new Chart(canvas, {
                type: chart.type,
                data: {
                    labels: chart.labels,
                    datasets: chart.datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: chart.indexAxis || 'x',
                    plugins: {
                        legend: {
                            display: chart.datasets.length > 1,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                padding: 12,
                                font: {
                                    size: 10
                                }
                            }
                        }
                    },
                    scales: chart.type === 'doughnut' ? {} : {
                        x: chart.indexAxis === 'y' ? {
                            ...chartAxis,
                            grid: {
                                display: false
                            }
                        } : chartAxis,
                        y: {
                            ...chartAxis,
                            beginAtZero: true
                        }
                    },
                },
            });
        });

        const activateAnalyticsTab = (target, updateUrl = false) => {
            const tab = document.querySelector(`[data-analytics-tab="${target}"]`);
            if (!tab) return;
            document.querySelectorAll('[data-analytics-tab]').forEach((item) => {
                item.classList.toggle('active', item === tab);
                item.setAttribute('aria-selected', item === tab ? 'true' : 'false');
            });
            document.querySelectorAll('[data-analytics-panel]').forEach((panel) => panel.classList
                .toggle('active', panel.dataset.analyticsPanel === target));
            if (updateUrl) window.history.replaceState(null, '', `${window.location.pathname}${window.location.search}#${target}`);
        };

        const hashTarget = window.location.hash.replace('#', '');
        activateAnalyticsTab(hashTarget || document.querySelector('[data-analytics-tab]')?.dataset.analyticsTab);
        document.querySelectorAll('[data-analytics-tab]').forEach((tab) => tab.addEventListener('click', () => activateAnalyticsTab(tab.dataset.analyticsTab, true)));
        window.addEventListener('hashchange', () => activateAnalyticsTab(window.location.hash.replace('#', '')));

        document.querySelectorAll('[data-analytics-search]').forEach((input) => {
            let timer;
            const submitSearch = () => {
                const url = new URL(window.location.href);
                const table = input.dataset.analyticsSearch;
                const parameter = `search[${table}]`;
                const value = input.value.trim();

                if (value) url.searchParams.set(parameter, value);
                else url.searchParams.delete(parameter);
                url.searchParams.delete(`page_${table}`);
                url.hash = `table-${table}`;
                window.location.assign(url.toString());
            };

            input.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    clearTimeout(timer);
                    submitSearch();
                }
            });
            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = window.setTimeout(submitSearch, 600);
            });
        });
    </script>
@endpush
