<?php

namespace App\Models\Viajes;

use CodeIgniter\Model;

class ViajeEnvioDevolucionMdl extends Model
{
    protected $table      = 'enviajeenviodevolucion';

    protected $allowedFields = [
        'nIdViajeEnvioDetalle', 'nIdViajeEnvio', 'nIdArticulo', 'fDevolver', 'fPeso'
    ];

    protected $primaryKey = 'nIdViajeEnvioDevolucion';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    
}