<?php

namespace App\Models\Almacen;

use CodeIgniter\Model;

class SalidaMdl extends Model
{
    protected $table = 'alsalida';

    protected $allowedFields = [
        'dSolicitud', 'dSalida', 'nIdSucursal', 'nIdProveedor',
        'fTotal', 'nProductos', 'sObservacion', 'cEdoEntrega'
    ];

    protected $primaryKey = 'nIdSalida';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    protected $datefld = 'dSalida';

    public function getRegistros($idSuc = false, $id = 0, $paginado = 0, $aCond = null)
    {
        $join1 = 'cpproveedor p';
        $wjoin1 = 'p.nIdProveedor = alsalida.nIdProveedor';
        $join2 = 'alsucursal a';
        $wjoin2 = 'a.nIdSucursal = alsalida.nIdSucursal';
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
            $model->orderBy('nIdSalida', 'ASC');
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

    public function getRegistrosByField($field = 'nIdSalida', $val = false)
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
        $this->builder()->set('cEdoEntrega', '(SELECT IF( SUM(alsalidadetalle.fCantidad) - SUM(alsalidadetalle.fRecibido) = 0,"3","2") '
            . 'FROM alsalidadetalle where alsalidadetalle.nIdSalida = ' . $id . ')', false)
            //->where('nIdSucursal', intval($idSuc))
            //->where('nIdEntrada',intval($id))
            ->where(['nIdSucursal' => intval($idSuc), 'nIdSalida' => intval($id)])
            ->update()
            //->getCompiledUpdate()
        ;
    }
}
/***********
 * 

CREATE TABLE `alsalida` (
  `nIdSalida` int(11) NOT NULL AUTO_INCREMENT,
  `dSalida` datetime DEFAULT NULL,
  `nIdSucursal` int(11) DEFAULT 0,
  `nIdProveedor` int(11) DEFAULT 0,
  `fTotal` double DEFAULT 0,
  `nProductos` int(11) DEFAULT 0,
  `sObservacion` varchar(45) DEFAULT NULL,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdSalida`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 * 
 ***********/
