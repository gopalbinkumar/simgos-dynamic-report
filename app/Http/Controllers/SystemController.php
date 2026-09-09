<?php

namespace App\Http\Controllers;

use App\Support\SimgosData;

class SystemController extends Controller
{
    public function databaseStatus()
    {
        $database = [];
        $tables = [];

        try {
            $connection = SimgosData::connection();
            $connection->getPdo();
            $databaseName = $connection->getDatabaseName();

            $database = [
                'status' => 'Connected',
                'database' => $databaseName,
                'driver' => strtoupper($connection->getDriverName()),
                'host' => config('database.connections.' . config('database.default') . '.host'),
                'port' => config('database.connections.' . config('database.default') . '.port'),
            ];

            $tables = $connection->table('information_schema.tables')
                ->select(['TABLE_NAME', 'TABLE_TYPE'])
                ->where('TABLE_SCHEMA', $databaseName)
                ->orderBy('TABLE_NAME')
                ->paginate(10, ['*'], 'table_page')
                ->withQueryString();

            $tables->getCollection()->transform(function (object $table) use ($connection): array {
                $tableName = (string) $table->TABLE_NAME;

                try {
                    // TABLE_ROWS is only an estimate for InnoDB. COUNT(*) gives
                    // the current committed row count for this request.
                    $rowCount = $connection->table($tableName)->count();

                    return [
                        'name' => $tableName,
                        'type' => $table->TABLE_TYPE,
                        'rows' => number_format($rowCount),
                        'status' => 'Available',
                    ];
                } catch (\Throwable) {
                    return [
                        'name' => $tableName,
                        'type' => $table->TABLE_TYPE,
                        'rows' => 'N/A',
                        'status' => 'Read error',
                    ];
                }
            });
        } catch (\Throwable) {
            $database = [
                'status' => 'Unavailable',
                'database' => config('database.connections.' . config('database.default') . '.database'),
                'driver' => strtoupper(config('database.default', 'mysql')),
                'host' => config('database.connections.' . config('database.default') . '.host'),
                'port' => config('database.connections.' . config('database.default') . '.port'),
                'error' => 'Koneksi belum dapat diverifikasi dari environment saat ini.',
            ];
        }

        return view('system.database-status', compact('database', 'tables'));
    }

    public function about()
    {
        return view('system.about');
    }
}
