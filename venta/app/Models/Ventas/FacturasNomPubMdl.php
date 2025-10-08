<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class FacturasNomPubMdl extends Model
{
    protected $table = 'vtfacturasnompub';

    protected $allowedFields = [
        'nIdVentas', 'sNombre'
    ];

    protected $primaryKey = 'nIdVentas';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';
}