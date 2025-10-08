<?php

namespace App\Models\Almacen;

use CodeIgniter\Model;

class InventarioCapturaDetalleMdl extends Model
{
    protected $table = 'alinventariocapturadetalle';

    protected $allowedFields = [
        'idInventarioCaptura', 'nIdArticulo', 'nIdSucursal',
        'fCantidad', 'fRecibido', 'fPorRecibir',
        'fExistencia', 'fPorAjustar', 'fImporte',
        'nEstado'
    ];

    protected $primaryKey = 'idInventarioCapturaDetalle';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    protected $datefld = 'dAplicacion';

    public function getRegistros($id = 0, $paginado = 0)
    {
        /*
        
        $join1 = 'alarticulo a';
        $wjoin1 = "a.nIdArticulo = $this->table.nIdArticulo";
        $join2 = 'alinventario i';
        $wjoin2 = "i.nIdArticulo = $this->table.nIdArticulo AND i.nIdSucursal =  $idSuc";
        if ($paginado === 0)
            return $this
                ->select($sselect)
                ->join($join1, $wjoin1, 'left')
                ->join($join2, $wjoin2, 'left')
                ->where(['idInventarioCaptura' =>  $id])
                ->findAll();
        else
            return $this
                ->select($sselect)
                ->join($join1, $wjoin1, 'left')
                ->join($join2, $wjoin2, 'left')
                ->where(['idInventarioCaptura' =>  $id])
                ->paginate($paginado);
        */
        $sselect = 'idInventarioCapturaDetalle, idInventarioCaptura, a.nIdArticulo, fCantidad, ' .
            '0 fRecibido, fPorAjustar, fImporte, a.sDescripcion, ' .
            'alinventariocapturadetalle.fExistencia, 0 fComprometido, ';
        $join1 = 'alarticulo a';
        $wjoin1 = "a.nIdArticulo = $this->table.nIdArticulo";

         /*
         $bldr = $this->builder()
            ->select($sselect)
            ->join($join1, $wjoin1, 'left')
            ->where(['idInventarioCaptura' =>  $id]);
         echo($bldr->getCompiledSelect(false)); exit;
         */

        if ($paginado === 0)
            return $this
                ->select($sselect)
                ->join($join1, $wjoin1, 'left')
                ->where(['idInventarioCaptura' =>  $id])
                ->findAll();
        else
            return $this
                ->select($sselect)
                ->join($join1, $wjoin1, 'left')
                ->where(['idInventarioCaptura' =>  $id])
                ->paginate($paginado);
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
