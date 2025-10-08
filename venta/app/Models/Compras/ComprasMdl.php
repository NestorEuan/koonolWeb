<?php

namespace App\Models\Compras;

use CodeIgniter\Model;
use DateInterval;
use DateTime;

class ComprasMdl extends Model
{
    protected $table = 'cpcompra';

    protected $allowedFields = [
        'dSolicitud', 'dcompra', 'nIdSucursal', 'nIdProveedor',
        'fTotal', 'fPagado', 'fSaldo', 'nProductos',
        'sObservacion', 'cEdoEntrega', 'cEdoPago'
    ];

    protected $primaryKey = 'nIdCompra';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    protected $datefld = 'dcompra';

    public function getRegistros($idSuc = false, $id = 0, $paginado = 0, $aCond = null)
    {
        $join1 = 'cpproveedor p';
        $wjoin1 = 'p.nIdProveedor = cpcompra.nIdProveedor';
        $join2 = 'alsucursal a';
        $wjoin2 = 'a.nIdSucursal = cpcompra.nIdSucursal';
        $aWhere = [];
        if ($idSuc != false)
            $aWhere = ['a.nIdSucursal' => $idSuc];
        $model = $this
            ->join($join1, $wjoin1, 'left')
            ->join($join2, $wjoin2, 'left');
        //$bldr = $model;
        if ($aCond != null) {
            $aEdo = false;
            $sEdo = 'cEdoEntrega';
            switch ($aCond['Edo']) {
                case '0':
                    $aEdo = ['0', '2', '4'];
                    break;
                case '1':
                    $aEdo = ['3'];
                    break;
                case '50':
                    $sEdo = 'cEdoPago';
                    $aEdo = ['0', '3'];
                    break;
                case '51':
                    $sEdo = 'cEdoPago';
                    $aEdo = ['3'];
                    break;
            }
            if ($aEdo) $model->whereIn($sEdo, $aEdo);
            if ($aCond['dIni'] != null) {
                $aWhere['dSolicitud >='] = $aCond['dIni'];
                $aWhere['dSolicitud <='] = $aCond['dFin'];
            }
        }
        $model->where($aWhere);
        if ($id === 0) {
            if ($paginado === 0)
                return $model
                    ->findAll();
            else
                return $model
                    ->paginate($paginado);
        }

        $aWhere[$this->table . '.' . $this->primaryKey] = $id;
        return $model->where($aWhere)->first();
    }

    public function getRegs($idSuc = false, $id = 0, $aCond = null, $paginado = 0, $conDetalle = false)
    {
        $join1 = 'cpproveedor p';
        $wjoin1 = 'p.nIdProveedor = cpcompra.nIdProveedor';
        $join2 = 'alsucursal a';
        $wjoin2 = 'a.nIdSucursal = cpcompra.nIdSucursal';

        $aWhere = [];
        if ($idSuc != false)
            $aWhere = ['a.nIdSucursal' => $idSuc];
        $selectAdd = '';
        if ($conDetalle)
            $selectAdd = ', cd.nIdArticulo, cd.fCantidad nCant, cd.fImporte nPrecio,' .
                '0 nDescuentoTotal, ar.sDescripcion sDescripcionArt';
        $model = $this
            ->select(
                'cpcompra.nIdCompra, cpcompra.nIdSucursal, cpcompra.nIdProveedor, ' .
                    'cpcompra.fSaldo, dSolicitud, dcompra, ' .
                    'cEdoEntrega, a.sDescripcion,  p.sNombre, ' .
                    'cpcompra.nIdCompra nFolioRemision, cpcompra.fTotal nTotalVenta, ' .
                    'a.sDescripcion nomSucursal' .
                    $selectAdd
            )
            ->join($join1, $wjoin1, 'left')
            ->join($join2, $wjoin2, 'left');
        if ($conDetalle)
            $this->join('cpcompradetalle cd', 'cd.nIdCompra = cpcompra.nIdCompra', 'left')
                ->join('alarticulo ar', 'ar.nIdArticulo = cd.nIdArticulo', 'inner');
        //$sql = $this->builder()->getCompiledSelect(false); // exit;

        if ($aCond != null) {
            if ($aCond['dIni'] != null && $aCond['Edo'] != '0') {
                $aWhere1 = [];
                $aWhere1['cpcompra.dcompra >='] = $aCond['dIni'];
                $aWhere1['cpcompra.dcompra <'] = (new DateTime($aCond['dFin']))
                    ->add(new DateInterval('P1D'))
                    ->format('Y-m-d');
                $this->where($aWhere1);
            }
            switch ($aCond['Edo']) {
                case '0':
                    $aWhere['cpcompra.fSaldo >'] = '0';
                    break;
                case '1':
                    $aWhere['cpcompra.fSaldo <'] = '1';
                    break;
            }
            if ($aCond['nIdProveedor'] != '') $this->where(['cpcompra.nIdProveedor' => $aCond['nIdProveedor']]);
        }

        if ($id === 0) {
            if ($paginado === 0)
                return $model
                    ->where($aWhere)
                    ->findAll();
            else
                return $model
                    ->where($aWhere)
                    ->paginate($paginado);
        }

        $aWhere[$this->table . '.' . $this->primaryKey] = $id;
        return $model->where($aWhere)->first();
    }

    public function getRegistrosByField($field = 'nIdSucursal', $val = false)
    {
        return $this->like([$field => $val])->findAll();
    }

    public function updateFecha($idSuc, $id, $dMovto)
    {
        $this->set($this->datefld, $dMovto)
            ->where(['nIdSucursal' => $idSuc, $this->primaryKey => $id])
            ->update();
    }

    public function updateEdo($idSuc = 0, $id = 0, $campo = 'cEdoEntrega')
    {
        $this->builder()->set($campo, '(SELECT IF( SUM(cpcompradetalle.fCantidad) - SUM(cpcompradetalle.fRecibido) = 0,"3","2") '
            . 'FROM cpcompradetalle where cpcompradetalle.nIdCompra = ' . $id . ')', false)
            //->where('nIdSucursal', intval($idSuc))
            //->where('nIdCompra',intval($id))
            ->where(['nIdSucursal' => intval($idSuc), 'nIdCompra' => intval($id)])
            ->update();
        //->getCompiledUpdate()
    }

    public function updateSaldo($idSuc = 0, $id = 0, $fImporte = 0)
    {
        $this->builder()
            ->set('fSaldo', 'fSaldo + ' . $fImporte, false)
            ->where(['nIdCompra' => intval($id)])
            ->update();
    }
}
/***********
 * 

CREATE TABLE `cpcompra` (
  `nIdCompra` int(11) NOT NULL AUTO_INCREMENT,
  `dcompra` datetime DEFAULT NULL,
  `nIdSucursal` int(11) DEFAULT NULL,
  `nIdProveedor` varchar(45) DEFAULT NULL,
  `fTotal` double DEFAULT NULL,
  `nProductos` int(11) DEFAULT NULL,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdCompra`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 * 
 ***********/
