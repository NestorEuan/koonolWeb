<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;
use DateInterval;
use DateTime;

class VentasDevMovimientoMdl extends Model
{
    protected $table = 'vtventasdevmovimiento';

    protected $allowedFields = [
        'nIdVentasDevolucion', 'nIdVentas', 'nIdCorte', 'fDevuelto', 'dtDevolucion', 'sObservacion',
    ];

    protected $primaryKey = 'nIdVentasDevMovimiento';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';
    
    // consulta por rango de fechas y por i
    public function getReg($idventa)
    {
        return $this->where(['nIdVentas' => $idventa])->first();
    }

    public function updtDevuelto($id, $fDevuelto)
    {
        $this->set('fDevuelto', $fDevuelto )
        ->where([$this->primaryKey => $id])
        ->update();
    }

}

/** 
 * 

CREATE TABLE `kerlis`.`vtventasdevmovimiento` (
  `nIdVentasDevMovimiento` INT NOT NULL AUTO_INCREMENT,
  `nIdVentasDevolucion` INT NULL,
  `nIdVentas` INT NULL,
  `nIdCorte` INT NULL,
  `dtDevolucion` DATETIME NULL,
  `sObservacion` VARCHAR(280) NULL,
  `dtAlta` DATETIME NULL,
  `dtBaja` DATETIME NULL,
  PRIMARY KEY (`nIdVentasDevMovimiento`));

 * 
 * **/