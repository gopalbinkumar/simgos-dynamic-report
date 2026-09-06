@extends('layouts.app')

@section('title', 'Dynamic Report | SIMGOS Dynamic Report')

@section('content')
    <div class="page-header">
        <div>
            <div class="breadcrumb"><span>Reporting</span><i class="fa-solid fa-chevron-right"></i><strong>Dynamic
                    Report</strong></div>
            <h1>Dynamic Report</h1>
            <p>Susun laporan sesuai kebutuhan dari sumber data SIMRS SIMGOS.</p>
        </div>
        <div class="page-header-status"><span class="status-dot"></span> Read-only query</div>
    </div>

    <form action="{{ route('reports.dynamic.run') }}" method="POST" class="report-builder">
        @csrf
        <div class="builder-card card">
            <div class="card-heading">
                <div><span class="eyebrow">REPORT BUILDER</span>
                    <h2>Sumber data</h2>
                    <p>Pilih tabel reporting yang menjadi sumber laporan.</p>
                </div><span class="step-number">01</span>
            </div>
            <label class="field"><span>Pilih Sumber Data</span><select name="source">
                    <option value="">Pilih sumber data</option>
                    @foreach ($sources as $key => $label)
                        <option value="{{ $key }}" @selected($state['source'] === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="builder-card card">
            <div class="card-heading">
                <div><span class="eyebrow">REPORT BUILDER</span>
                    <h2>Pilih kolom</h2>
                    <p>Kolom terpilih akan menjadi kolom pada hasil report.</p>
                </div><span class="step-number">02</span>
            </div>
            <div class="checkbox-grid">
                @foreach ($availableColumns as $key => $label)
                    <label class="check-card"><input type="checkbox" name="selected_columns[]" value="{{ $key }}"
                            @checked(in_array($key, $state['selected_columns'], true))><span class="custom-check"><i
                                class="fa-solid fa-check"></i></span><span>{{ $label }}</span></label>
                @endforeach
            </div>
        </div>

        <div class="builder-card card">
            <div class="card-heading">
                <div><span class="eyebrow">REPORT BUILDER</span>
                    <h2>Filter</h2>
                    <p>Tambahkan satu atau beberapa filter untuk mempersempit hasil.</p>
                </div><span class="step-number">03</span>
            </div>
            <div class="filter-repeater" data-filter-repeater>
                @foreach ($state['filters'] as $index => $filter)
                    <div class="filter-row" data-filter-row>
                        <label class="field"><span>Field</span><select name="filters[{{ $index }}][field]">
                                <option value="category" @selected(($filter['field'] ?? '') === 'category')>Kategori</option>
                                <option value="tanggal" @selected(($filter['field'] ?? '') === 'tanggal')>Tanggal</option>
                                <option value="status" @selected(($filter['field'] ?? '') === 'status')>Status</option>
                            </select></label>
                        <label class="field"><span>Operator</span><select name="filters[{{ $index }}][operator]">
                                <option value="=" @selected(($filter['operator'] ?? '') === '=')>Sama Dengan</option>
                                <option value=">=" @selected(($filter['operator'] ?? '') === '>=')>Lebih Besar / Sama Dengan</option>
                                <option value="<=" @selected(($filter['operator'] ?? '') === '<=')>Lebih Kecil / Sama Dengan</option>
                                <option value="contains" @selected(($filter['operator'] ?? '') === 'contains')>Mengandung</option>
                            </select></label>
                        <label class="field"><span>Value</span><input type="text"
                                name="filters[{{ $index }}][value]" value="{{ $filter['value'] ?? '' }}"
                                placeholder="Semua"></label>
                        <button type="button" class="icon-button remove-filter" data-remove-filter
                            aria-label="Hapus filter"><i class="fa-solid fa-trash-can"></i></button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="button button-outline add-filter" data-add-filter><i class="fa-solid fa-plus"></i>
                Tambah Filter</button>
        </div>

        <div class="builder-card card">
            <div class="card-heading">
                <div><span class="eyebrow">REPORT BUILDER</span>
                    <h2>Pengurutan & grouping</h2>
                    <p>Atur urutan data dan kelompokkan hasil laporan.</p>
                </div><span class="step-number">04</span>
            </div>
            <div class="form-grid-3">
                <label class="field"><span>Kolom</span><select name="sort_column">
                        <option value="tanggal" @selected($state['sort_column'] === 'tanggal')>Tanggal</option>
                        <option value="id" @selected($state['sort_column'] === 'id')>ID</option>
                        <option value="category" @selected($state['sort_column'] === 'category')>Kategori</option>
                    </select></label>
                <label class="field"><span>Urutan</span><select name="sort_direction">
                        <option value="desc" @selected($state['sort_direction'] === 'desc')>Terbaru → Terlama</option>
                        <option value="asc" @selected($state['sort_direction'] === 'asc')>Terlama → Terbaru</option>
                    </select></label>
                <label class="field"><span>Group By</span><select name="group_by">
                        <option value="none" @selected($state['group_by'] === 'none')>Tidak Ada</option>
                        <option value="category" @selected($state['group_by'] === 'category')>Kategori</option>
                        <option value="tanggal" @selected($state['group_by'] === 'tanggal')>Tanggal</option>
                    </select></label>
            </div>
        </div>

        <div class="builder-actions"><button type="submit" class="button button-primary"><i class="fa-solid fa-play"></i>
                Tampilkan Report</button><button type="button" class="button button-light" data-reset-filters><i
                    class="fa-solid fa-filter-circle-xmark"></i> Reset Filter</button><a
                href="{{ route('reports.dynamic.reset') }}" class="button button-light"><i
                    class="fa-solid fa-rotate-left"></i> Reset Builder</a></div>
    </form>

    @if ($showReport)
        <section class="card results-card">
            <div class="card-heading results-heading">
                <div><span class="eyebrow">REPORT RESULT</span>
                    <h2>Hasil Report</h2>
                    <p>{{ count($rows) }} baris ditampilkan. Kolom tabel mengikuti pilihan pada builder.</p>
                </div>
                <div class="table-actions"><a href="{{ route('reports.dynamic.export') }}" class="button button-outline"><i
                            class="fa-solid fa-file-csv"></i> Export Excel</a><button type="button"
                        class="button button-outline" onclick="window.print()"><i class="fa-solid fa-file-pdf"></i> Export
                        PDF</button></div>
            </div>
            <div class="table-toolbar"><label class="search-field"><i class="fa-solid fa-magnifying-glass"></i><input
                        type="search" data-table-search placeholder="Cari di hasil report..."></label><button
                    class="button button-light" type="button" data-toggle-columns><i
                        class="fa-solid fa-table-columns"></i> Column toggle</button></div>
            <div class="column-toggle-panel" data-column-toggle-panel>
                @foreach ($state['selected_columns'] as $column)
                    <label><input type="checkbox" checked data-column-toggle="{{ $loop->index }}">
                        {{ $availableColumns[$column] }}</label>
                @endforeach
            </div>
            <div class="table-wrap">
                <table class="data-table" data-report-table>
                    <thead>
                        <tr>
                            @foreach ($state['selected_columns'] as $column)
                                <th>{{ $availableColumns[$column] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                @foreach ($state['selected_columns'] as $column)
                                    <td>
                                        @if ($column === 'status')
                                            <span
                                                class="badge badge-neutral">{{ $row[$column] ?? '-' }}</span>@else{{ $row[$column] ?? '-' }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty<tr>
                                <td colspan="{{ max(count($state['selected_columns']), 1) }}">
                                    <div class="empty-state"><i class="fa-solid fa-inbox"></i><strong>Tidak ada
                                            data</strong><span>Belum ada baris yang sesuai konfigurasi report.</span></div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection

@push('scripts')
    <script>
        (() => {
            const repeater = document.querySelector('[data-filter-repeater]');
            const addButton = document.querySelector('[data-add-filter]');
            const resetFilters = document.querySelector('[data-reset-filters]');
            if (repeater && addButton) {
                const rowTemplate = (index) =>
                    `<div class="filter-row" data-filter-row><label class="field"><span>Field</span><select name="filters[${index}][field]"><option value="category">Kategori</option><option value="tanggal">Tanggal</option><option value="status">Status</option></select></label><label class="field"><span>Operator</span><select name="filters[${index}][operator]"><option value="=">Sama Dengan</option><option value=">=">Lebih Besar / Sama Dengan</option><option value="<=">Lebih Kecil / Sama Dengan</option><option value="contains">Mengandung</option></select></label><label class="field"><span>Value</span><input type="text" name="filters[${index}][value]" placeholder="Semua"></label><button type="button" class="icon-button remove-filter" data-remove-filter aria-label="Hapus filter"><i class="fa-solid fa-trash-can"></i></button></div>`;
                addButton.addEventListener('click', () => {
                    repeater.insertAdjacentHTML('beforeend', rowTemplate(repeater.querySelectorAll(
                        '[data-filter-row]').length));
                });
                repeater.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-remove-filter]');
                    if (button && repeater.querySelectorAll('[data-filter-row]').length > 1) button.closest(
                        '[data-filter-row]').remove();
                });
            }
            if (resetFilters && repeater) resetFilters.addEventListener('click', () => {
                repeater.innerHTML =
                    `<div class="filter-row" data-filter-row><label class="field"><span>Field</span><select name="filters[0][field]"><option value="category">Kategori</option><option value="tanggal">Tanggal</option><option value="status">Status</option></select></label><label class="field"><span>Operator</span><select name="filters[0][operator]"><option value="=">Sama Dengan</option><option value=">=">Lebih Besar / Sama Dengan</option><option value="<=">Lebih Kecil / Sama Dengan</option><option value="contains">Mengandung</option></select></label><label class="field"><span>Value</span><input type="text" name="filters[0][value]" placeholder="Semua"></label><button type="button" class="icon-button remove-filter" data-remove-filter aria-label="Hapus filter"><i class="fa-solid fa-trash-can"></i></button></div>`;
            });
            const table = document.querySelector('[data-report-table]');
            const search = document.querySelector('[data-table-search]');
            if (table && search) search.addEventListener('input', () => {
                const needle = search.value.toLowerCase();
                table.querySelectorAll('tbody tr').forEach(row => row.style.display = row.innerText
                .toLowerCase().includes(needle) ? '' : 'none');
            });
            const togglePanel = document.querySelector('[data-column-toggle-panel]');
            document.querySelector('[data-toggle-columns]')?.addEventListener('click', () => togglePanel?.classList
                .toggle('open'));
            togglePanel?.addEventListener('change', event => {
                if (!event.target.dataset.columnToggle) return;
                const index = Number(event.target.dataset.columnToggle);
                table?.querySelectorAll(`tr > *:nth-child(${index + 1})`).forEach(cell => cell.classList.toggle(
                    'column-hidden', !event.target.checked));
            });
        })();
    </script>
@endpush
