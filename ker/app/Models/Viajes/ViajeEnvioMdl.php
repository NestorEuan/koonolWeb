<?php

namespace App\Models\Viajes;

use CodeIgniter\Model;

class ViajeEnvioMdl extends Model
{
    protected $table      = 'enviajeenvio';

    protected $allowedFields = [
        'nIdViaje', 'nIdEnvio', 'cEstatus',
        'sObservacion', 'fPeso'
    ];

    protected $primaryKey = 'nIdViajeEnvio';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getEnviosViajeDet($idViaje)
    {
        $this->select('enviajeenvio.nIdEnvio, enviajeenvio.nIdViajeEnvio, enviajeenvio.sObservacion, ' .
            'ved.nIdArticulo, ved.fPorRecibir, a.fPeso AS pesoArt, ' .
            'ved.cModoEnv, a.cSinExistencia, ved.nIdViajeEnvioDetalle, ' .
            'IFNULL(vedv.nIdViajeEnvioDetalle, 0) AS idDevolucion', false);
        return $this->join('enviajeenviodetalle ved', 'enviajeenvio.nIdViajeEnvio = ved.nIdViajeEnvio', 'inner')
            ->join('enviajeenviodevolucion vedv', 'ved.nIdViajeEnvioDetalle = vedv.nIdViajeEnvioDetalle', 'left')
            ->join('alarticulo a', 'ved.nIdArticulo = a.nIdArticulo', 'inner')
            ->where('enviajeenvio.nIdViaje', $idViaje)
            ->orderBy('enviajeenvio.nIdEnvio', 'asc')->findAll();
    }

    public function getIDsViajeEnvios($idViaje)
    {
        return $this->select('enviajeenvio.nIdViajeEnvio AS id')
            ->where('enviajeenvio.nIdViaje', $idViaje)
            ->findAll();
    }

    public function getIDsViajeEnviosDetalles($idViaje)
    {
        return $this->select('b.nIdViajeEnvioDetalle AS id, b.nIdArticulo, ' .
            'b.fPorRecibir, b.cModoEnv, ar.cSinExistencia, enviajeenvio.nIdEnvio')
            ->join('enviajeenviodetalle b', 'enviajeenvio.nIdViajeEnvio = b.nIdViajeEnvio', 'inner')
            ->join('alarticulo ar', 'b.nIdArticulo = ar.nIdArticulo', 'inner')
            ->where('enviajeenvio.nIdViaje', $idViaje)
            ->findAll();
    }

    public function getRegistrosDetalleParaDevolucion($idEnvio, $idViaje)
    {
        return $this->select('ved.*, a.sDescripcion AS nomArt, a.cSinExistencia, ' .
            'IFNULL(vedv.fDevolver, 0) AS fDevolver', false)
            ->join('enviajeenviodetalle ved', 'enviajeenvio.nIdViajeEnvio = ved.nIdViajeEnvio', 'inner')
            ->join('enviajeenviodevolucion vedv', 'ved.nIdViajeEnvioDetalle = vedv.nIdViajeEnvioDetalle', 'left')
            ->join('alarticulo a', 'ved.nIdArticulo = a.nIdArticulo', 'inner')
            ->where([
                'enviajeenvio.nIdEnvio' => $idEnvio,
                'enviajeenvio.nIdViaje' => $idViaje
            ])->findAll();
    }
}
