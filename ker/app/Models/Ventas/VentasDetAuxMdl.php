<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class VentasDetAuxMdl extends Model
{
    protected $table = 'vtventasdetaux';

    protected $allowedFields = ['nIdVentasDet', 
    'nImpComisionTotal', 'nImpDescuentoProd', 'nImpDescuentoGral', 
    'nImporteTapadoFactura'];

    protected $primaryKey = 'nIdVentasDet';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

}
