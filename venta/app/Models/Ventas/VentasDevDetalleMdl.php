<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;
use DateInterval;
use DateTime;

class VentasDevDetalleMdl extends Model
{
    protected $table = 'vtventasdevdetalle';

    protected $allowedFields = [
        'nIdVentasDevolucion', 'nIdVentas', 'nIdArticulo', 'fDevuelto', 
    ];

    protected $primaryKey = 'nIdVentasDevDetalle';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';
    
    public function getRegs($idventa, $idarticulo = 0)
    {
        if($idarticulo == 0)
            return $this->where(['nIdVentas' => $idventa])->findAll();
        else
            return $this->where(['nIdVentas' => $idventa, 'nIdArticulo' => $idarticulo])->first();
    }

    public function updtDevuelto($id, $fDevuelto)
    {
        $this->set('fDevuelto', 'fDevuelto + ' . $fDevuelto, false)
        ->where([$this->primaryKey => $id])
        ->update();

    }

}

/** 
 * 

CREATE TABLE `kerlis`.`vtventasdevdetalle` (
  `nIdVentasDevDetalle` INT NOT NULL AUTO_INCREMENT,
  `nIdVentasDevolucion` INT NULL,
  `nIdVentas` INT NULL,
  `nIdArticulo` INT NULL,
  `fDevuelto` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `dtAlta` DATETIME NULL,
  `dtBaja` DATETIME NULL,
  PRIMARY KEY (`nIdVentasDevDetalle`));

  CREATE TABLE `vtventasdevdetalle` (
  `nIdVentasDevDetalle` int(11) NOT NULL AUTO_INCREMENT,
  `nIdVentasDevolucion` int(11) DEFAULT NULL,
  `nIdVentas` int(11) DEFAULT NULL,
  `nIdArticulo` int(11) DEFAULT NULL,
  `fDevuelto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdVentasDevDetalle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

 * 
 * **/