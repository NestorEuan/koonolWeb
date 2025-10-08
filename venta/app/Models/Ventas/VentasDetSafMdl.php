<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class VentasDetSafMdl extends Model
{
    protected $table = 'vtventasdetsaf';

    protected $allowedFields = [
        'nIdVentasDet', 'dtAlta', 'nCantParaSaldo'
    ];

    protected $primaryKey = 'nIdVentasDet';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

}
