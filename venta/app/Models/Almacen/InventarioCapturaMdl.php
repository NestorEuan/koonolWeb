<?php

namespace App\Models\Almacen;

use CodeIgniter\Model;

class InventarioCapturaMdl extends Model
{
    protected $table = 'alinventariocaptura';

    protected $allowedFields = [
        'nIdSucursal', 'nIdProveedor', 'dSolicitud', 'dAplicacion',
        'sObservacion', 'sFiltroUtilizado', 
        'nProductos', 'fTotal', 'cEdoEntrega',
    ];

    protected $primaryKey = 'idInventarioCaptura';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    protected $datefld = 'dAplicacion';

    public function getRegistros($idSuc = false, $id = 0, $paginado = 0, $aCond = null)
    {
        $join1 = 'cpproveedor p';
        $wjoin1 = "p.nIdProveedor = $this->table.nIdProveedor";
        $join2 = 'alsucursal a';
        $wjoin2 = "a.nIdSucursal = $this->table.nIdSucursal";
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

    public function getRegistrosByField($field = 'idInventarioCaptura', $val = false)
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
        $tabledet = 'alinventariocapturadetalle';
        $this->builder()->set('cEdoEntrega', '3')
            //->where('nIdSucursal', intval($idSuc))
            //->where('nIdEntrada',intval($id))
            ->where([$this->primaryKey => intval($id), 'nIdSucursal' => intval($idSuc)])
            ->update()
            //->getCompiledUpdate()
        ;
    }
}
/***********
 * 

CREATE TABLE `pov`.`alinventariocaptura` (
  `idInventarioCaptura` INT NOT NULL DEFAULT 0,
  `nIdSucursal` INT NOT NULL,
  `nIdUsuario` INT NOT NULL,
  `sObservacion` VARCHAR(150) NULL,
  `sFiltroUtilizado` VARCHAR(150) NULL,
  `nEstatus` INT NULL,
  `dtAlta` DATETIME NULL,
  `dtBaja` DATETIME NULL,
  PRIMARY KEY (`idInventarioCaptura`));

  ALTER TABLE `pov`.`alinventariocaptura` 
ADD COLUMN `dSolicitud` DATETIME NULL AFTER `nIdUsuario`,
ADD COLUMN `dAplicacion` DATETIME NULL AFTER `dSolicitud`,
ADD COLUMN `nProductos` INT NULL AFTER `sFiltroUtilizado`,
ADD COLUMN `fTotal` DECIMAL(10,2) NULL AFTER `nProductos`,
CHANGE COLUMN `nEstatus` `cEdoEntrega` VARCHAR(5) NULL DEFAULT NULL ;

 * 
 ***********/
