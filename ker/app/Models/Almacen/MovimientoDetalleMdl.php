<?php

namespace App\Models\Almacen;

use CodeIgniter\Model;

class MovimientoDetalleMdl extends Model{
    protected $table = 'almovimientodetalle';

    protected $allowedFields = ['nIdMovimiento', 'nIdSucursal', 'nIdArticulo', 'fCantidad', 'fSaldoInicial' ];

    protected $primaryKey = 'nIdMovimientoDetalle';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = 0)
    {
        if ($id === 0)
            return $this->findAll();
        return $this->where([$this->primaryKey =>  $id])->first();
    }
    
    public function getRegistrosByField($field = 'nIdMovimientoDetalle', $val = false)
    {
        return $this->where([$field => $val])->findAll();
    }
    
}
/***********
 * 

CREATE TABLE `almovimientodetalle` (
  `nIdMovimientoDetalle` int(11) NOT NULL AUTO_INCREMENT,
  `nIdMovimiento` int(11) DEFAULT NULL,
  `nIdSucursal` int(11) DEFAULT NULL,
  `nIdArticulo` int(11) DEFAULT NULL,
  `fCantidad` decimal(10,2) DEFAULT 0.00,
  `dtAlta` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdMovimientoDetalle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 * 
 ***********/
?>