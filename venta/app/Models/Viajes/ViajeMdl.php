<?php

namespace App\Models\Viajes;

use CodeIgniter\Model;
use DateInterval;
use DateTime;

class ViajeMdl extends Model
{
    protected $table = 'enviaje';

    protected $allowedFields = [
        'nIdChofer', 'nIdSucursal', 'nViaje',
        'dViaje', 'fPeso', 'sObservacion',
        'cEstatus'
    ];

    protected $primaryKey = 'nIdViaje';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = false, $idSucursal = false, $paginado = false, $aCond = false)
    {
        $this->select(
            'enviaje.*, b.sDescripcion AS nomSuc, IFNULL(c.sChofer, \'pendiente\') AS nomChofer' .
                ', (select count(m.nIdEnvio) as cont from enviajeenvio m where m.nIdViaje = enviaje.nIdViaje and m.nIdEnvio > 0 limit 1) as nEnvios',
            false
        )
            ->join('alsucursal b', 'enviaje.nIdSucursal = b.nIdSucursal', 'inner')
            ->join('enchofer c', 'enviaje.nIdChofer = c.nIdChofer', 'left');
        if ($idSucursal) $this->where('enviaje.nIdSucursal', $idSucursal);
        if ($id) {
            $this->where('enviaje.nIdViaje', $id);
            if ($paginado)
                return $this->paginate($paginado);
            else
                return $this->findAll();
        }

        if ($aCond) {
            if ($aCond['opc'] == '0' || $aCond['opc'] == '1' || $aCond['opc'] == '2') {
                $this->where('(cEstatus = \'' . $aCond['opc'] . '\')', null, false);
            } elseif ($aCond['opc'] == '3') {
                $wFec = 'enviaje.dViaje >= \'' . $aCond['dIni'] . '\' and ' .
                    'enviaje.dViaje < \'' . ((new DateTime($aCond['dFin']))
                        ->add(new DateInterval('P1D'))
                        ->format('Y-m-d')) . '\'';
                $wFec .= ' AND enviaje.dtBaja IS NULL';
                $this->where($wFec, null, false);
            } elseif ($aCond['opc'] == '7') {
                $wFec = 'enviaje.dtAlta >= \'' . $aCond['dIni'] . '\' and ' .
                    'enviaje.dtAlta < \'' . ((new DateTime($aCond['dFin']))
                        ->add(new DateInterval('P1D'))
                        ->format('Y-m-d')) . '\'';
                $wFec .= ' AND enviaje.dtBaja IS NULL';
                if($aCond['tipo'] == '1') {
                    $wFec .= ' AND cEstatus <> \'0\'';
                }
                $this->where($wFec, null, false);
            }
        }

        if ($paginado) {
            $this->orderBy('enviaje.nIdViaje', 'DESC');
            if (isset($aCond['ultimaPagina'])) {
                $this->paginate($paginado);
                $nPaginas = $this->pager->getPageCount();
                return $this->paginate($paginado, 'default', 200);
            } else {
                return $this->paginate($paginado);
            }
        } else
            return  $this->findAll();
    }

    public function getRegsPorIdVentas($idVentas, $idSucursal, $paginado = false)
    {
        $this->select(
            'enviaje.*, b.sDescripcion AS nomSuc, IFNULL(c.sChofer, \'pendiente\') AS nomChofer' .
                ', (select count(m.nIdEnvio) as cont from enviajeenvio m where m.nIdViaje = enviaje.nIdViaje and m.nIdEnvio > 0 limit 1) as nEnvios',
            false
        )
            ->join('alsucursal b', 'enviaje.nIdSucursal = b.nIdSucursal', 'inner')
            ->join('enchofer c', 'enviaje.nIdChofer = c.nIdChofer', 'left');
        $where = 'enviaje.nIdViaje IN ( ' .
            'SELECT en.nIdViaje ' .
            'FROM enviajeenvio en ' .
            'WHERE en.nIdEnvio IN ' .
            '(SELECT ev.nIdEnvio FROM enenvio ev WHERE ev.nIdOrigen = ' . $idVentas . ' AND ev.cOrigen = \'ventas\' AND ev.nIdSucursal = ' . $idSucursal . ') ' .
            'GROUP BY en.nIdViaje ' .
            ')  AND enviaje.dtBaja IS NULL';
        $this->where($where, null, false);
        if ($paginado)
            return $this->paginate($paginado);
        else
            return $this->findAll();
    }

    public function getRegsPorIdEnvio($idEnvio, $idSucursal, $paginado = false)
    {
        $this->select(  
            'enviaje.*, b.sDescripcion AS nomSuc, IFNULL(c.sChofer, \'pendiente\') AS nomChofer' .
                ', (select count(m.nIdEnvio) as cont from enviajeenvio m where m.nIdViaje = enviaje.nIdViaje and m.nIdEnvio > 0 limit 1) as nEnvios',
            false
        )
            ->join('alsucursal b', 'enviaje.nIdSucursal = b.nIdSucursal', 'inner')
            ->join('enchofer c', 'enviaje.nIdChofer = c.nIdChofer', 'left');
        $where = 'enviaje.nIdViaje IN ( ' .
            'SELECT en.nIdViaje ' .
            'FROM enviajeenvio en ' .
            'WHERE en.nIdEnvio = ' . $idEnvio . ' AND en.dtBaja IS NULL' .
            ') AND enviaje.dtBaja IS NULL AND enviaje.nIdSucursal = ' . $idSucursal;
        $this->where($where, null, false);
        if ($paginado)
            return $this->paginate($paginado);
        else
            return $this->findAll();
    }
}
