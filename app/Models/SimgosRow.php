<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Lightweight read-only model used by reporting pages.
 * The table name is assigned at runtime and never written to.
 */
class SimgosRow extends Model
{
    protected $guarded = [];

    protected $primaryKey = 'TANGGAL';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    public function useTable(string $table): static
    {
        $this->setTable($table);

        return $this;
    }
}
