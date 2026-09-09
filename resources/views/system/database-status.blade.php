@extends('layouts.app')
@section('title', 'Database Status | SIMGOS Dynamic Report')
@section('content')
    <div class="page-header">
        <div>
            <div class="breadcrumb"><span>System</span><i class="fa-solid fa-chevron-right"></i><strong>Database
                    Status</strong></div>
            <h1>Database Status</h1>
            <p>Monitoring koneksi ke database SIMRS SIMGOS.</p>
        </div><span class="page-header-status"><span
                class="status-dot {{ $database['status'] === 'Connected' ? '' : 'offline' }}"></span>
            {{ $database['status'] }}</span>
    </div>
    <div class="connection-grid">
        <div class="card connection-card">
            <div class="connection-card-top">
                <div class="large-status-icon {{ $database['status'] === 'Connected' ? 'connected' : 'offline' }}"><i
                        class="fa-solid fa-database"></i></div>
                <div><span class="eyebrow">CONNECTION STATUS</span>
                    <h2>{{ $database['status'] }}</h2>
                    <p>{{ $database['error'] ?? 'Koneksi database berhasil diverifikasi.' }}</p>
                </div>
            </div>
            <div class="connection-indicator"><span
                    class="status-dot {{ $database['status'] === 'Connected' ? '' : 'offline' }}"></span>
                {{ $database['status'] }}</div>
        </div>
        <div class="card detail-card">
            <div class="card-heading">
                <div><span class="eyebrow">DATABASE CONFIGURATION</span>
                    <h2>Detail koneksi</h2>
                </div>
            </div>
            <dl class="detail-list">
                <div>
                    <dt>Database</dt>
                    <dd>{{ $database['database'] ?? '-' }}</dd>
                </div>
                <div>
                    <dt>DB Driver</dt>
                    <dd>{{ $database['driver'] ?? '-' }}</dd>
                </div>
                <div>
                    <dt>Database Host</dt>
                    <dd>{{ $database['host'] ?? '-' }}</dd>
                </div>
                <div>
                    <dt>Database Port</dt>
                    <dd>{{ $database['port'] ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>
    <section class="card results-card">
        <div class="card-heading">
            <div><span class="eyebrow">SCHEMA INSPECTION</span>
                <h2>Available Tables</h2>
                <p>Jumlah baris dihitung langsung dengan COUNT(*) pada setiap tabel atau view.</p>
            </div><span class="count-label">{{ count($tables) }} tabel</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Table Name</th>
                        <th>Type</th>
                        <th>Rows</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tables as $table)
                        <tr>
                            <td><strong>{{ $table['name'] }}</strong></td>
                            <td>{{ $table['type'] }}</td>
                            <td>{{ $table['rows'] }}</td>
                            <td><span
                                    class="badge {{ $table['status'] === 'Available' ? 'badge-success' : 'badge-neutral' }}"><i
                                        class="fa-solid {{ $table['status'] === 'Available' ? 'fa-check' : 'fa-triangle-exclamation' }}"></i>
                                    {{ $table['status'] }}</span></td>
                    </tr>@empty<tr>
                            <td colspan="4">
                                <div class="empty-state"><i class="fa-solid fa-table"></i><strong>Daftar tabel belum
                                        tersedia</strong><span>Pastikan koneksi database pada file environment dapat
                                        diakses.</span></div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($tables, 'hasPages'))
            @include('partials.paginator', ['paginator' => $tables])
        @endif
    </section>
@endsection
