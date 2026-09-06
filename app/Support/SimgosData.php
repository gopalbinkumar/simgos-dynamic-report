<?php

namespace App\Support;

use App\Models\SimgosRow;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SimgosData
{
    public const FALLBACK_TABLE = 'indikator_rs';

    public static function connection(): Connection
    {
        return DB::connection(config('database.default'));
    }

    public static function tableExists(string $table): bool
    {
        try {
            return self::connection()->getSchemaBuilder()->hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    public static function query(string $table): Builder
    {
        $exists = self::tableExists($table);
        $source = $exists ? $table : self::FALLBACK_TABLE;
        $query = (new SimgosRow())->useTable($source)->newQuery();

        // Keep placeholder pages renderable without ever querying an unknown table.
        if (! $exists && $table !== self::FALLBACK_TABLE) {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public static function availableTables(): array
    {
        try {
            return collect(self::connection()->getSchemaBuilder()->getTables())
                ->map(fn (array $table): string => $table['name'] ?? $table['TABLE_NAME'] ?? '')
                ->filter()
                ->values()
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Return the real column names for a SIMGOS table without changing it.
     */
    public static function columns(string $table): array
    {
        if (! self::tableExists($table)) {
            return [];
        }

        try {
            return self::connection()->getSchemaBuilder()->getColumnListing($table);
        } catch (\Throwable) {
            return [];
        }
    }
}
