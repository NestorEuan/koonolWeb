<?php

namespace App\Models\Almacen;

use CodeIgniter\Model;
use DateInterval;
use DateTime;

use function PHPUnit\Framework\isEmpty;

class TraspasoMdl extends Model
{
    protected $table = 'altraspaso';

    protected $allowedFields = [
        'dSolicitud', 'dTraspaso', 'nIdSucursal', 'nIdProveedor',
        'fTotal', 'nProductos', 'sObservacion', 'cEdoEnvio', 'cEdoEntrega'
    ];

    protected $primaryKey = 'nIdTraspaso';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    protected $datefld = 'dTraspaso';

    public function getRegistros($idSuc = false, $id = 0, $paginado = 0, $filtro = false)
    {
        $join1 = 'alsucursal p';
        $wjoin1 = 'p.nIdSucursal = altraspaso.nIdProveedor';
        $join2 = 'alsucursal a';
        $wjoin2 = 'a.nIdSucursal = altraspaso.nIdSucursal';
        $sfields = 'altraspaso.nIdTraspaso, altraspaso.cEdoEntrega, altraspaso.nIdSucursal, ' .
            'altraspaso.nIdProveedor, altraspaso.dSolicitud, altraspaso.dtAlta, altraspaso.dTraspaso, altraspaso.fTotal, ' .
            'altraspaso.nProductos, altraspaso.sObservacion, a.sDescripcion, p.sDescripcion ' .
            'sNombre, IFNULL(te.nIdEnvio, 0) as nIdEnvio, IFNULL(ve.nIdViaje, 0) as nIdViaje,' .
            '( ' .
            'SELECT COUNT(aa.nIdEnvio) AS n FROM enviajeenvio aa ' .
            'WHERE aa.nIdEnvio = te.nIdEnvio AND aa.dtBaja IS NULL' .
            ') AS contEnviosViajes ';

        $aWhere = [];
        if ($idSuc != false)
            $aWhere = ['a.nIdSucursal' => $idSuc];

        if ($id === 0) {
            if ($filtro !== false) {
                if (!empty($filtro['dIni']) && !empty($filtro['dFin'])) {
                    $this->where('altraspaso.dtAlta >=', $filtro['dIni']);
                    $this->where(
                        'altraspaso.dtAlta <=',
                        (new DateTime($filtro['dFin']))->add((new DateInterval('P1D')))->format('Y-m-d')
                    );
                }
                $edo = false;
                switch ($filtro['Edo']) {
                    case '0':
                        $edo = ['0', '2'];
                        break;
                    case '1':
                        $edo = ['3'];
                        break;
                }
                if ($edo) $this->whereIn('altraspaso.cEdoEntrega', $edo);
            }
            $this->select($sfields, false)
                ->join($join1, $wjoin1, 'left')
                ->join($join2, $wjoin2, 'left')
                ->join('enenvio te', 'altraspaso.nIdTraspaso = te.nIdOrigen AND te.cOrigen = \'traspaso\'', 'left')
                ->join('enviajeenvio ve', 'te.nIdEnvio = ve.nIdEnvio', 'left')
                ->where($aWhere)
                ->orderBy('nIdTraspaso', 'desc');
            if ($paginado === 0)
                return $this->findAll();
            else
                return $this->paginate($paginado);
        }

        $aWhere['altraspaso.nIdTraspaso'] = $id;
        return $this
            ->select($sfields, false)
            ->join($join1, $wjoin1, 'left')
            ->join($join2, $wjoin2, 'left')
            ->join('enenvio te', 'altraspaso.nIdTraspaso = te.nIdOrigen AND te.cOrigen = \'traspaso\'', 'left')
            ->join('enviajeenvio ve', 'te.nIdEnvio = ve.nIdEnvio', 'left')
            ->where($aWhere)
            ->first();
    }

    public function getRegistrosByField($field = 'nIdTraspaso', $val = false)
    {
        return $this->like([$field => $val])->findAll();
    }

    public function updateFecha($idSuc, $id, $dMovto)
    {
        $this->set($this->datefld, $dMovto)
            ->where(['nIdSucursal' => $idSuc, $this->primaryKey => $id])
            ->update();
    }
    public function updateEdo($idSuc = 0, $id = 0)
    {
        $this->builder()->set('cEdoEntrega', '(SELECT IF( SUM(altraspasodetalle.fCantidad) - SUM(altraspasodetalle.fRecibido) = 0,"3","2") '
            . 'FROM altraspasodetalle where altraspasodetalle.nIdTraspaso = ' . $id . ')', false)
            //->where('nIdSucursal', intval($idSuc))
            //->where('nIdEntrada',intval($id))
            ->where(['nIdSucursal' => intval($idSuc), 'nIdTraspaso' => intval($id)])
            ->update()
            //->getCompiledUpdate()
        ;
    }
}
/***********
 * 

CREATE TABLE `altraspaso` (
  `nIdTraspaso` int(11) NOT NULL AUTO_INCREMENT,
  `dTraspaso` datetime DEFAULT NULL,
  `nIdSucursal` int(11) DEFAULT 0,
  `nIdProveedor` int(11) DEFAULT 0,
  `fTotal` double DEFAULT 0,
  `nProductos` int(11) DEFAULT 0,
  `sObservacion` varchar(45) DEFAULT NULL,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdTraspaso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 * 
 ***********/
