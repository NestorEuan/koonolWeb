<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;
use DateInterval;
use DateTime;

class VentasDevMovimientoDetMdl extends Model
{
    protected $table = 'vtventasdevmovimientodet';

    protected $allowedFields = [
            'nIdVentasDevMovimiento', 'nIdVentas', 'nIdArticulo', 
            'fDevuelto', 'fImporte', 'nPrecio',
        ];

    protected $primaryKey = 'nIdVentasDevMovimientoDet';

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

CREATE TABLE `kerlis`.`vtventasdevmovimientodet` (
  `nIdVentasDevMovimientoDet` INT NOT NULL AUTO_INCREMENT,
  `nIdVentasDevMovimiento` INT NULL,
  `nIdVentas` INT NULL,
  `nIdArticulo` INT NULL,
  `fDevuelto` DECIMAL(10,2) NULL,
  `fImporte` DECIMAL(10,2) NULL,
  `nPrecio` DECIMAL(10,2) NULL,
  `dtAlta` DATETIME NULL,
  `dtBaja` DATETIME NULL,
  PRIMARY KEY (`nIdVentasDevMovimientoDet`));

 * 
 * **/