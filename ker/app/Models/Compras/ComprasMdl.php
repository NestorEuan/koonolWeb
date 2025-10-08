<?php

namespace App\Models\Compras;

use CodeIgniter\Model;

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
            switch ($aCond['Edo']) {
                case '0':
                    $aWhere['cEdoEntrega <'] = '3';
                    break;
                case '1':
                    $aWhere['cEdoEntrega'] = '3';
                    break;
                case '50':
                    $aWhere['cEdoPago <'] = '3';
                    break;
                case '51':
                    $aWhere['cEdoPago'] = '3';
                    break;
            }

            if ($aCond['dIni'] != null) {
                $aWhere['dSolicitud >='] = $aCond['dIni'];
                $aWhere['dSolicitud <='] = $aCond['dFin'];
            }
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

        $aWhere[$this->primaryKey] = $id;
        return $model->where($aWhere)->first();
    }

    public function getRegs($idSuc = false, $id = 0, $aCond = null, $paginado = 0)
    {
        $join1 = 'cpproveedor p';
        $wjoin1 = 'p.nIdProveedor = cpcompra.nIdProveedor';
        $join2 = 'alsucursal a';
        $wjoin2 = 'a.nIdSucursal = cpcompra.nIdSucursal';

        $aWhere = [];
        if ($idSuc != false)
        $aWhere = ['a.nIdSucursal' => $idSuc];
        $model = $this
            ->select(
                'cpcompra.nIdCompra, cpcompra.nIdSucursal, cpcompra.nIdProveedor, ' .
                'cpcompra.fSaldo, dSolicitud, dcompra, ' .
                'cEdoEntrega, a.sDescripcion,  p.sNombre '
            )
            ->join($join1, $wjoin1, 'left')
            ->join($join2, $wjoin2, 'left');
        //$bldr = $model;

        if ($aCond != null) {
            switch ($aCond['Edo']) {
                case '0':
                    $aWhere['cpcompra.fSaldo >'] = '0';
                    break;
                case '1':
                    $aWhere['cpcompra.fSaldo <'] = '1';
                    break;
            }

            if ($aCond['dIni'] != null) {
                $aWhere['dcompra >='] = $aCond['dIni'];
                $aWhere['dcompra <='] = $aCond['dFin'];
            }
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

        $aWhere[$this->primaryKey] = $id;
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
        $this->builder()->set($campo, '(SELECT IF( SUM(cpcompradetalle.fCantidad) - SUM(cpcompradetalle.fRecibido) = 0,"3","4") '
            . 'FROM cpcompradetalle where cpcompradetalle.nIdCompra = ' . $id . ')', false)
            //->where('nIdSucursal', intval($idSuc))
            //->where('nIdCompra',intval($id))
            ->where(['nIdSucursal' => intval($idSuc), 'nIdCompra' => intval($id)])
            ->update()
            //->getCompiledUpdate()
        ;
    }

    public function updateSaldo($idSuc = 0, $id = 0, $fImporte = 0)
    {
        $this->builder()->set('cEdoPago', '(SELECT IF( SUM(cpcompradetalle.fCantidad) - SUM(cpcompradetalle.fRecibido) = 0,"3","4") '
            . 'FROM cpcompradetalle where cpcompradetalle.nIdCompra = ' . $id . ')', false)
            ->set('fSaldo', 'fSaldo + ' . $fImporte, false)
            //->where('nIdSucursal', intval($idSuc))
            //->where('nIdCompra',intval($id))
            ->where(['nIdSucursal' => intval($idSuc), 'nIdCompra' => intval($id)])
            ->update()
            //->getCompiledUpdate()
        ;
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
