<?php

namespace App\Models\Viajes;

use CodeIgniter\Model;
use DateInterval;
use DateTime;

class EnvioDetalleMdl extends Model
{
    protected $table = 'enenviodetalle';

    protected $allowedFields = [
        'nIdEnvio',
        'nIdArticulo', 'fCantidad', 'fRecibido', 'fPorRecibir', 'cModoEnv'
    ];

    protected $primaryKey = 'nIdEnvioDetalle';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id)
    {
        return $this->select('enenviodetalle.*, a.sDescripcion AS nomArt, a.cSinExistencia, a.fPeso')
            ->join('alarticulo a', 'enenviodetalle.nIdArticulo = a.nIdArticulo', 'inner')
            ->where('nIdEnvio', $id)
            ->findAll();
    }



}
