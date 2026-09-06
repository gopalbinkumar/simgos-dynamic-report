<?php

namespace App\Http\Controllers;

use App\Support\SimgosData;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    private const RESOURCES = [
        'informasi' => ['table' => 'informasi', 'title' => 'Informasi'],
        'pegawai' => ['table' => 'pegawai', 'title' => 'Pegawai'],
        'dokter' => ['table' => 'dokter', 'title' => 'Dokter'],
        'poli' => ['table' => 'poli', 'title' => 'Poli'],
        'ruangan' => ['table' => 'ruangan', 'title' => 'Ruangan'],
    ];

    public function index(Request $request, string $resource)
    {
        abort_unless(isset(self::RESOURCES[$resource]), 404);

        $definition = self::RESOURCES[$resource];
        $table = $definition['table'];
        $columns = SimgosData::columns($table);
        $query = SimgosData::query($table);
        $search = trim((string) $request->input('search', ''));

        if ($search !== '' && $columns !== []) {
            $query->where(function ($builder) use ($columns, $search): void {
                foreach (array_slice($columns, 0, 10) as $column) {
                    $builder->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }

        $rows = SimgosData::tableExists($table)
            ? $query->select($columns)->paginate(25)->withQueryString()
            : collect();

        return view('master-data.index', [
            'resource' => $resource,
            'title' => $definition['title'],
            'tableName' => $table,
            'columns' => $columns,
            'rows' => $rows,
            'search' => $search,
        ]);
    }
}
