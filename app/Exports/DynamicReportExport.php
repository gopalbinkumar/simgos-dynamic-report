<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DynamicReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        private readonly Collection $rows,
        private readonly array $selectedColumns,
        private readonly array $columnLabels,
    ) {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return array_map(
            fn(string $column): string => $this->columnLabels[$column] ?? $column,
            $this->selectedColumns
        );
    }

    public function map($row): array
    {
        return array_map(
            fn(string $column): string => (string) ($row->{$column} ?? '-'),
            $this->selectedColumns
        );
    }
}
