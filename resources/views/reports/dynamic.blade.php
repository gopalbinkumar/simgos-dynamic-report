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
                @forelse ($availableColumns as $key => $label)
                    <label class="check-card"><input type="checkbox" name="selected_columns[]" value="{{ $key }}"
                            @checked(in_array($key, $state['selected_columns'], true))><span class="custom-check"><i
                                class="fa-solid fa-check"></i></span><span>{{ $label }}</span></label>
                @empty
                    <div class="empty-state"><i class="fa-solid fa-database"></i><strong>Kolom belum tersedia</strong><span>Sumber data belum dapat dibaca dari database SIMGOS.</span></div>
                @endforelse
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
                                @foreach ($availableColumns as $key => $label)
                                    <option value="{{ $key }}" @selected(($filter['field'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
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
                        @foreach ($availableColumns as $key => $label)
                            <option value="{{ $key }}" @selected($state['sort_column'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select></label>
                <label class="field"><span>Urutan</span><select name="sort_direction">
                        <option value="desc" @selected($state['sort_direction'] === 'desc')>Terbaru → Terlama</option>
                        <option value="asc" @selected($state['sort_direction'] === 'asc')>Terlama → Terbaru</option>
                    </select></label>
                <label class="field"><span>Group By</span><select name="group_by">
                        <option value="none" @selected($state['group_by'] === 'none')>Tidak Ada</option>
                        @foreach ($availableColumns as $key => $label)
                            <option value="{{ $key }}" @selected($state['group_by'] === $key)>{{ $label }}</option>
                        @endforeach
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
                    <p>{{ $rows->total() }} baris tersedia. Kolom tabel mengikuti pilihan pada builder.</p>
                </div>
                <div class="table-actions"><a href="{{ route('reports.dynamic.export', ['search' => $search]) }}" class="button button-outline"><i
                            class="fa-solid fa-file-excel"></i> Export Excel</a><button type="button"
                        class="button button-outline" onclick="window.print()"><i class="fa-solid fa-file-pdf"></i> Export
                        PDF</button></div>
            </div>
            <div class="table-toolbar">
                <form method="GET" action="{{ route('reports.dynamic') }}" class="search-field">
                    <input type="hidden" name="report" value="1">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" name="search" value="{{ $search }}" placeholder="Cari di hasil report..." aria-label="Cari di hasil report">
                </form>
                <button class="button button-light" type="button" data-toggle-columns><i
                    class="fa-solid fa-table-columns"></i> Column toggle</button>
            </div>
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
                                        @if ($column === 'STATUS')
                                            <span
                                                class="badge badge-neutral">{{ $row->{$column} ?? '-' }}</span>@else{{ $row->{$column} ?? '-' }}
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
            @include('partials.paginator', ['paginator' => $rows])
        </section>
    @endif
@endsection

@push('scripts')
    <script>
        (() => {
            const repeater = document.querySelector('[data-filter-repeater]');
            const addButton = document.querySelector('[data-add-filter]');
            const resetFilters = document.querySelector('[data-reset-filters]');
            const columnOptions = @json($availableColumns);
            const defaultFilterField = @json($defaultFilterField);

            const fieldOptions = (selected = defaultFilterField) => Object.entries(columnOptions)
                .map(([key, label]) => `<option value="${key}" ${key === selected ? 'selected' : ''}>${label}</option>`)
                .join('');

            const filterRowTemplate = (index, selectedField = defaultFilterField) =>
                `<div class="filter-row" data-filter-row><label class="field"><span>Field</span><select name="filters[${index}][field]">${fieldOptions(selectedField)}</select></label><label class="field"><span>Operator</span><select name="filters[${index}][operator]"><option value="=">Sama Dengan</option><option value=">=">Lebih Besar / Sama Dengan</option><option value="<=">Lebih Kecil / Sama Dengan</option><option value="contains">Mengandung</option></select></label><label class="field"><span>Value</span><input type="text" name="filters[${index}][value]" placeholder="Masukkan nilai"></label><button type="button" class="icon-button remove-filter" data-remove-filter aria-label="Hapus filter"><i class="fa-solid fa-trash-can"></i></button></div>`;

            if (repeater && addButton) {
                addButton.addEventListener('click', () => {
                    repeater.insertAdjacentHTML('beforeend', filterRowTemplate(repeater.querySelectorAll(
                        '[data-filter-row]').length));
                });
                repeater.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-remove-filter]');
                    if (button && repeater.querySelectorAll('[data-filter-row]').length > 1) button.closest(
                        '[data-filter-row]').remove();
                });
            }
            if (resetFilters && repeater) resetFilters.addEventListener('click', () => {
                repeater.innerHTML = filterRowTemplate(0);
            });
            const table = document.querySelector('[data-report-table]');
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
