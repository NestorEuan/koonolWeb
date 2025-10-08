<?php

namespace App\Models\Viajes;

use CodeIgniter\Model;

class ViajeEnvioDetalleMdl extends Model
{
    protected $table      = 'enviajeenviodetalle';

    protected $allowedFields = [
        'nIdViajeEnvio',
        'nIdArticulo',
        'fPorRecibir',
        'fPeso',
        'cModoEnv',
        'dtAlta',
        'dtBaja'
    ];

    protected $primaryKey = 'nIdViajeEnvioDetalle';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

}
