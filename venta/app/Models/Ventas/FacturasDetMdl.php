<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class FacturasDetMdl extends Model
{
    protected $table = 'vtfacturasdet';

    protected $allowedFields = [
        'nIdFacturas', 'nIdVentasDet', 'nCant'
    ];

    protected $primaryKey = 'nIdFacturas';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';
}
