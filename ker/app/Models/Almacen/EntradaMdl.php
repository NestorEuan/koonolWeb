<?php

namespace App\Models\Almacen;

use CodeIgniter\Model;

class EntradaMdl extends Model
{
    protected $table = 'alentrada';

    protected $allowedFields = [
        'dSolicitud',
        'dEntrada',
        'nIdSucursal',
        'nIdProveedor',
        'fTotal',
        'nProductos',
        'sObservacion',
        'cEdoEntrega',
    ];

    protected $primaryKey = 'nIdEntrada';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    protected $datefld = 'dEntrada';

    public function getRegistros($idSuc = false, $id = 0, $paginado = 0, $aCond = null)
    {
        $join1 = 'cpproveedor p';
        $wjoin1 = 'p.nIdProveedor = alentrada.nIdProveedor';
        $join2 = 'alsucursal a';
        $wjoin2 = 'a.nIdSucursal = alentrada.nIdSucursal';
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
            }

            if ($aCond['dIni'] != null) {
                $aWhere['dSolicitud >='] = $aCond['dIni'];
                $aWhere['dSolicitud <='] = $aCond['dFin'];
            }
        }
        //$bldr = $this->builder()->where($aWhere);
        //echo($bldr->getCompiledSelect(false));

        if ($id === 0) {
            $model->orderBy('nIdEntrada', 'ASC');
            if ($paginado === 0) {
                return $model
                    ->where($aWhere)
                    ->findAll();
            } else {
                if (isset($aCond['pagina']) && intval($aCond['pagina']) > 1) {
                    return $model
                        ->where($aWhere)
                        ->paginate($paginado, 'default', intval($aCond['pagina']));
                } else {
                    return $model
                        ->where($aWhere)
                        ->paginate($paginado);
                }
            }
        }
        $aWhere[$this->primaryKey] = $id;

        return $model->where($aWhere)->first();
    }

    public function getRegistrosByField($field = 'nIdEntrada', $val = false)
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
        $this->builder()->set('cEdoEntrega', '(SELECT IF( SUM(alentradadetalle.fCantidad) - SUM(alentradadetalle.fRecibido) = 0,"3","2") '
            . 'FROM alentradadetalle where alentradadetalle.nIdEntrada = ' . $id . ')', false)
            //->where('nIdSucursal', intval($idSuc))
            //->where('nIdEntrada',intval($id))
            ->where(['nIdSucursal' => intval($idSuc), 'nIdEntrada' => intval($id)])
            ->update()
            //->getCompiledUpdate()
        ;
    }
}
/***********
 * 

CREATE TABLE `alentrada` (
  `nIdEntrada` int(11) NOT NULL AUTO_INCREMENT,
  `dEntrada` datetime DEFAULT NULL,
  `nIdSucursal` int(11) DEFAULT 0,
  `nIdProveedor` int(11) DEFAULT 0,
  `fTotal` double DEFAULT 0,
  `nProductos` int(11) DEFAULT 0,
  `sObservacion` varchar(45) DEFAULT NULL,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdEntrada`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 * 
 ***********/
