@extends('layouts.app')

@section('title', $title . ' | SIMGOS Dynamic Report')

@section('content')
    <div class="page-header">
        <div>
            <div class="breadcrumb"><span>Master Data</span><i
                    class="fa-solid fa-chevron-right"></i><strong>{{ $title }}</strong></div>
            <h1>{{ $title }}</h1>
            <p>Data read-only dari tabel <code>{{ $tableName }}</code>. Tidak ada operasi tulis ke database SIMGOS.</p>
        </div><span class="page-header-status"><i class="fa-solid fa-lock"></i> Read-only</span>
    </div>
    <section class="card results-card">
        <div class="card-heading results-heading">
            <div><span class="eyebrow">DATA SOURCE</span>
                <h2>Daftar {{ $title }}</h2>
                <p>{{ count($columns) ? count($columns) . ' kolom terdeteksi dari database.' : 'Tabel belum tersedia atau belum terpetakan.' }}
                </p>
            </div>
        </div>
        <form class="table-toolbar" method="GET"><label class="search-field"><i
                    class="fa-solid fa-magnifying-glass"></i><input type="search" name="search"
                    value="{{ $search }}" placeholder="Cari data..."></label><button type="submit"
                class="button button-primary"><i class="fa-solid fa-search"></i> Cari</button><button type="button"
                class="button button-light" data-toggle-master-columns><i class="fa-solid fa-table-columns"></i> Column
                toggle</button></form>
        @if ($columns && $rows->count())
            <div class="table-wrap">
                <table class="data-table" data-master-table>
                    <thead>
                        <tr>
                            @foreach ($columns as $column)
                                <th>{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                @foreach ($columns as $column)
                                    <td>{{ data_get($row, $column, '-') }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @include('partials.paginator', ['paginator' => $rows])
        @else
            <div class="empty-state large"><i class="fa-solid fa-database"></i><strong>Belum ada data untuk
                    ditampilkan</strong><span>Tabel <code>{{ $tableName }}</code> belum tersedia pada koneksi database
                    aktif atau belum memiliki data.</span></div>
        @endif
    </section>
@endsection
