<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class VentasCanceladasMdl extends Model
{
    protected $table = 'vtventascanceladas';

    protected $allowedFields = [
        'nIdVentas', 'sMotivo', 'dtFecCancelacion'
    ];

    protected $primaryKey = 'nIdVentas';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';
}
