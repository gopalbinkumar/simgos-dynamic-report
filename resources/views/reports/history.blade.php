@extends('layouts.app')
@section('title', 'Report History | SIMGOS Dynamic Report')
@section('content')
    <div class="page-header">
        <div>
            <div class="breadcrumb"><span>Reporting</span><i class="fa-solid fa-chevron-right"></i><strong>Report
                    History</strong></div>
            <h1>Report History</h1>
            <p>Riwayat report yang pernah dijalankan oleh dashboard.</p>
        </div>
    </div>
    <section class="card results-card">
        <div class="card-heading">
            <div><span class="eyebrow">ACTIVITY LOG</span>
                <h2>Riwayat eksekusi report</h2>
                <p>Riwayat di bawah masih berupa mock dan tidak menulis log ke database SIMGOS.</p>
            </div>
        </div>
        <div class="table-toolbar"><label class="search-field"><i class="fa-solid fa-magnifying-glass"></i><input type="search"
                    data-table-search placeholder="Cari riwayat..."></label></div>
        <div class="table-wrap">
            <table class="data-table" data-report-table>
                <thead>
                    <tr>
                        <th>Report</th>
                        <th>User</th>
                        <th>Waktu</th>
                        <th>Durasi</th>
                        <th>Jumlah Data</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($records as $record)
                        <tr>
                            <td><strong>{{ $record['report'] }}</strong></td>
                            <td>{{ $record['user'] }}</td>
                            <td>{{ $record['time'] }}</td>
                            <td>{{ $record['duration'] }}</td>
                            <td>{{ number_format($record['count']) }}</td>
                            <td><span class="badge badge-{{ strtolower($record['status']) }}">{{ $record['status'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('partials.paginator', ['paginator' => $records])
    </section>
@endsection
