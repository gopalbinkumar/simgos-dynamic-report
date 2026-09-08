<aside class="sidebar" data-sidebar>
    <div class="brand-lockup">
        <div class="brand-mark">SG</div>
        <div class="brand-copy">
            <div class="brand-name">SIMGOS</div>
            <div class="brand-subtitle">Dynamic Report</div>
        </div>
        <button type="button" class="icon-button sidebar-close" data-sidebar-toggle aria-label="Tutup menu"><i
                class="fa-solid fa-xmark"></i></button>
    </div>

    <nav class="sidebar-nav" aria-label="Navigasi utama">
        <div class="nav-group">
            <div class="nav-group-label">Dashboard</div>
            <a href="{{ route('dashboard') }}"
                class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}" title="Overview"><i
                    class="fa-solid fa-chart-pie"></i><span>Overview</span></a>
            <a href="{{ route('reports.dynamic') }}"
                class="nav-link {{ request()->routeIs('reports.dynamic*') ? 'active' : '' }}" title="Dynamic Report"><i
                    class="fa-solid fa-sliders"></i><span>Dynamic Report</span></a>
        </div>

        <div class="nav-group">
            <div class="nav-group-label">Master Data</div>
            @foreach (['informasi' => ['Informasi', 'fa-circle-info'], 'pegawai' => ['Pegawai', 'fa-users'], 'dokter' => ['Dokter', 'fa-user-doctor'], 'poli' => ['Poli', 'fa-hospital'], 'ruangan' => ['Ruangan', 'fa-bed']] as $key => $item)
                <a href="{{ route('master-data.index', $key) }}"
                    class="nav-link {{ request()->is('master-data/' . $key) ? 'active' : '' }}" title="{{ $item[0] }}"><i
                        class="fa-solid {{ $item[1] }}"></i><span>{{ $item[0] }}</span></a>
            @endforeach
        </div>

        <div class="nav-group">
            <div class="nav-group-label">Reporting</div>
            <a href="{{ route('reports.dynamic') }}"
                class="nav-link {{ request()->routeIs('reports.dynamic*') ? 'active' : '' }}" title="Dynamic Report"><i
                    class="fa-solid fa-table-list"></i><span>Dynamic Report</span></a>
            <a href="{{ route('reports.saved') }}"
                class="nav-link {{ request()->routeIs('reports.saved') ? 'active' : '' }}" title="Saved Report"><i
                    class="fa-solid fa-bookmark"></i><span>Saved Report</span></a>
            <a href="{{ route('reports.history') }}"
                class="nav-link {{ request()->routeIs('reports.history') ? 'active' : '' }}" title="Report History"><i
                    class="fa-solid fa-clock-rotate-left"></i><span>Report History</span></a>
        </div>

        <div class="nav-group">
            <div class="nav-group-label">System</div>
            <a href="{{ route('system.database-status') }}"
                class="nav-link {{ request()->routeIs('system.database-status') ? 'active' : '' }}" title="Database Status"><i
                    class="fa-solid fa-server"></i><span>Database Status</span></a>
            <a href="{{ route('system.about') }}"
                class="nav-link {{ request()->routeIs('system.about') ? 'active' : '' }}" title="About"><i
                    class="fa-solid fa-circle-question"></i><span>About</span></a>
        </div>
    </nav>

    <div class="sidebar-footer" style="display: none">
        <div class="sidebar-footer-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <div><strong>Read-only access</strong><span>Data SIMGOS aman terjaga</span></div>
    </div>
</aside>
