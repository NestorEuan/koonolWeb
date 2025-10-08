<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class VentasNCMdl extends Model
{
    protected $table = 'vtventasnc';

    protected $allowedFields = [
        'nIdVentas', 'Fecha', 'sMotivo', 'nImporte'
    ];

    protected $primaryKey = 'nIdVentasNC';

    protected $useTimestamps = true;

    protected $useSoftDeletes = false;

    protected $createdField = 'dtAlta';

    protected $deletedField = '';

    protected $updatedField = '';


}
