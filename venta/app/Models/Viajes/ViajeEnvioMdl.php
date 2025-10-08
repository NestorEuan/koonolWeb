<?php

namespace App\Models\Viajes;

use CodeIgniter\Model;
use DateInterval;
use DateTime;

class ViajeEnvioMdl extends Model
{
    protected $table      = 'enviajeenvio';

    protected $allowedFields = [
        'nIdViaje', 'nIdEnvio', 'cEstatus',
        'sObservacion', 'fPeso', 'dtAlta'
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

    public function getArticuloEnEnvio($idEnvio, $idArticulo)
    {
        return $this->select('enviajeenvio.nIdViajeEnvio, ' .
            'enviajeenvio.nIdEnvio, enviajeenvio.nIdViaje, d.cModoEnv, d.fPorRecibir,' .
            'd.nIdViajeEnvioDetalle, v.cEstatus, v.nIdSucursal, s.sDescripcion AS nomSuc')
            ->join('enviajeenviodetalle d', 'enviajeenvio.nIdViajeEnvio = d.nIdViajeEnvio', 'inner')
            ->join('enviaje v', 'enviajeenvio.nIdViaje = v.nIdViaje', 'inner')
            ->join('alsucursal s', 'v.nIdSucursal = s.nIdSucursal', 'inner')
            ->where('enviajeenvio.nIdEnvio', $idEnvio)
            ->where('d.nIdArticulo', $idArticulo)
            ->where('d.fPorRecibir >', 0)
            ->findAll();
    }

    public function getEnviosConModoENV($idArt, $idSuc, DateTime $fIni, DateTime $fFin)
    {
        $aWhere = [
            'enviajeenvio.dtAlta >=' => $fIni->format('Y-m-d'),
            'enviajeenvio.dtAlta <' => $fFin->add(new DateInterval('P1D'))->format('Y-m-d'),
            'enviajeenvio.cEstatus' => '1',
            'enviajeenvio.nIdEnvio >' => 0,
            'b.cModoEnv' => '1',
        ];
        if ($idArt != '') $aWhere['b.nIdArticulo'] = $idArt;
        if ($idSuc != '') $aWhere['c.nIdSucursal'] = $idSuc;

        return $this->select('enviajeenvio.*, c.nIdSucursal, c.cEstatus, b.nIdArticulo, b.fPorRecibir, ' . 
            ' d.cOrigen AS oriDoc, ' .
            'IF(d.cOrigen = \'ventas\', v.nFolioRemision, d.nIdOrigen) as folioDoc, ' .
            'art.sDescripcion AS nomArt '
            , false)
            ->join('enviajeenviodetalle b', 'enviajeenvio.nIdViajeEnvio = b.nIdViajeEnvio', 'inner')
            ->join('enviaje c', 'enviajeenvio.nIdViaje = c.nIdViaje', 'inner')
            ->join('enenvio d', 'enviajeenvio.nIdEnvio = d.nIdEnvio', 'inner')
            ->join('vtventas v', 'd.nIdOrigen = v.nIdVentas', 'left')
            ->join('alarticulo art', 'b.nIdArticulo = art.nIdArticulo', 'inner')
            ->where($aWhere)->findAll();
    }
}
