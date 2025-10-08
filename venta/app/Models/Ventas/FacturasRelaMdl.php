<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class FacturasRelaMdl extends Model
{
    protected $table = 'vtfacturasrela';

    protected $allowedFields = [
        'nIdFacturas', 'sUUID'
    ];

    protected $primaryKey = 'nIdFacturas';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';
}