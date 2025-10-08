<?php

namespace App\Models\Almacen;

use CodeIgniter\Model;

class EntregaMdl extends Model
{
    protected $table = 'alentrega';

    protected $allowedFields = [
        'dSolicitud', 'dEntrega', 'nIdSucursal', 'nIdOrigen', 'nIdProveedor',
        'fTotal', 'nProductos', 'sObservacion', 'cEdoEntrega'
    ];

    protected $primaryKey = 'nIdEntrega';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    protected $datefld = 'dEntrega';

    public function getRegistros($idSuc = false, $id = 0, $paginado = 0, $aCond = null, $conDetalle = false)
    {
        //agregar nombre de quien recibe si es cliente al publico
        $join1 = 'vtcliente c';
        $wjoin1 = 'c.nIdCliente = alentrega.nIdProveedor';
        $join2 = 'alsucursal a';
        $wjoin2 = 'a.nIdSucursal = alentrega.nIdSucursal';
        $join3 = 'vtventasnomentrega e';
        $wjoin3 = 'e.nIdVentas = alentrega.nIdOrigen and c.cTipoCliente = "P"';
        $aWhere = [];
        if ($idSuc != false)
            $aWhere = ['a.nIdSucursal' => $idSuc];
        $model = $this
            ->join($join1, $wjoin1, 'left')
            ->join($join2, $wjoin2, 'left')
            ->join($join3, $wjoin3, 'left')
            ->join('vtventas v', 'alentrega.nIdOrigen = v.nIdVentas', 'left');
        //$bldr = $model;
        if ($aCond != null) {
            switch ($aCond['Edo']) {
                case '0':
                    $aWhere['alentrega.cEdoEntrega <'] = '3';
                    break;
                case '1':
                    $aWhere['alentrega.cEdoEntrega'] = '3';
                    break;
            }

            if ($aCond['dIni'] != null) {
                $aWhere['alentrega.dEntrega >='] = $aCond['dIni'];
                $aWhere['alentrega.dEntrega <='] = $aCond['dFin'];
            }
        }
        /* 
        $bld = $model->where($aWhere);
        $sql = $bld->builder()->getCompiledSelect(false);
        echo $sql;
        */
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

        if ($conDetalle) {
            $this->select('*.alentrega, ed.nIdArticulo, ed.fCantidad, a.sDescripcion as nomSuc, IFNULL(e.cNomEntrega, \'\') as nomPublico', false);
            $this->join('alentregadet ed', 'alentrega.nIdEntrega = ed.nIdEntrega', 'inner');
            $this->where('nIdEntrega', $id);
            return $this->first();
        } else {
            $aWhere[$this->primaryKey] = $id;
        }


        return $model->where($aWhere)->first();
    }

    public function getRegistrosByField($field = 'nIdEntrega', $val = false)
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
        $this->builder()->set('cEdoEntrega', '(SELECT IF( SUM(alentregadetalle.fCantidad) - SUM(alentregadetalle.fRecibido) = 0,"3","2") '
            . 'FROM alentregadetalle where alentregadetalle.' . $this->primaryKey . '  = ' . $id . ')', false)
            //->where('nIdSucursal', intval($idSuc))
            //->where('nIdEntrada',intval($id))
            ->where(['nIdSucursal' => intval($idSuc), $this->primaryKey => intval($id)])
            ->update()
            //->getCompiledUpdate()
        ;
    }

    public function articulosporentregar($idSuc, $id)
    {
        return $this
            ->select('alentrega.nIdEntrega, nIdOrigen, ed.nIdEntregaDetalle,' .
                'ed.nIdArticulo, ed.fCantidad, ed.fPorRecibir, ed.fRecibido')
            ->join('alentregadetalle ed', 'ed.nIdEntrega = alentrega.nIdEntrega', 'left')
            ->where('ed.fPorRecibir > 0')
            ->where('alentrega.nIdSucursal', $idSuc)
            ->where('alentrega.nIdOrigen', $id)
            ->findAll();
    }
}
/***********
 * 
CREATE TABLE `alentrega` (
	`nIdEntrega` INT(11) NOT NULL AUTO_INCREMENT,
	`dentrega` DATETIME NULL DEFAULT NULL,
	`nIdSucursal` INT(11) NULL DEFAULT NULL,
	`nIdProveedor` VARCHAR(45) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`fTotal` DOUBLE NULL DEFAULT NULL,
	`nProductos` INT(11) NULL DEFAULT NULL,
	`dtAlta` DATETIME NULL DEFAULT NULL,
	`dtBaja` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`nIdEntrega`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB;
 * 
 ***********/
