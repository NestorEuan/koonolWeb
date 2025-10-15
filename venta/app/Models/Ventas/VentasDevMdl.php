<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;
use DateInterval;
use DateTime;

class VentasDevMdl extends Model
{
    protected $table = 'vtventasdev';

    protected $allowedFields = [
        'nIdVentas', 
    ];

    protected $primaryKey = 'nIdVentasDevolucion';

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

}

/** 
 * 

CREATE TABLE `kerlis`.`vtventasdev` (
  `nIdVentasDevolucion` INT NOT NULL AUTO_INCREMENT,
  `nIdVentas` INT NULL,
  `dtAlta` DATETIME NULL,
  `dtBaja` DATETIME NULL,
  PRIMARY KEY (`nIdVentasDevolucion`));

 * 
 * **/