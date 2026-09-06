@extends('layouts.app')
@section('title', 'Saved Report | SIMGOS Dynamic Report')
@section('content')
    <div class="page-header">
        <div>
            <div class="breadcrumb"><span>Reporting</span><i class="fa-solid fa-chevron-right"></i><strong>Saved
                    Report</strong></div>
            <h1>Saved Report</h1>
            <p>Daftar konfigurasi report yang pernah disimpan.</p>
        </div><a href="{{ route('reports.dynamic') }}" class="button button-primary"><i class="fa-solid fa-plus"></i> Buat
            Report Baru</a>
    </div>
    <section class="card results-card">
        <div class="card-heading">
            <div><span class="eyebrow">REPORT LIBRARY</span>
                <h2>Daftar Saved Report</h2>
                <p>Data di bawah masih berupa mock untuk tahap UI dan belum disimpan ke tabel baru.</p>
            </div>
        </div>
        <div class="table-toolbar"><label class="search-field"><i class="fa-solid fa-magnifying-glass"></i><input
                    type="search" data-table-search placeholder="Cari report..."></label></div>
        <div class="table-wrap">
            <table class="data-table" data-report-table>
                <thead>
                    <tr>
                        <th>Nama Report</th>
                        <th>Sumber Data</th>
                        <th>Jumlah Kolom</th>
                        <th>Jumlah Filter</th>
                        <th>Dibuat Pada</th>
                        <th>Terakhir Digunakan</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($records as $record)
                        <tr>
                            <td><strong>{{ $record['name'] }}</strong></td>
                            <td><span class="badge badge-primary">{{ $record['source'] }}</span></td>
                            <td>{{ $record['columns'] }}</td>
                            <td>{{ $record['filters'] }}</td>
                            <td>{{ $record['created_at'] }}</td>
                            <td>{{ $record['last_used'] }}</td>
                            <td>
                                <div class="action-links"><a href="{{ route('reports.dynamic') }}" title="View"><i
                                            class="fa-regular fa-eye"></i></a><a href="{{ route('reports.dynamic') }}"
                                        title="Edit"><i class="fa-regular fa-pen-to-square"></i></a><button type="button"
                                        title="Duplicate"><i class="fa-regular fa-copy"></i></button><button type="button"
                                        title="Delete"><i class="fa-regular fa-trash-can"></i></button></div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
