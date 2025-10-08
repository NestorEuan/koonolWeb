<?php

namespace App\Models\Almacen;

use CodeIgniter\Model;
use DateInterval;
use DateTime;

class EntradaCancelMdl extends Model
{
    protected $table = 'alentradacancel';

    protected $allowedFields = ['nIdEntrada', 'nIdUsuario', 'dtCancelacion', 'sObservacion'];

    protected $primaryKey = 'nIdEntradaCancel';

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

    public function getRegistrosByField($field = 'nIdEntradaCancel', $val = false)
    {
        return $this->where([$field => $val])->findAll();
    }

}
/***********
 * 
CREATE TABLE `alentradacancel` (
  `nIdEntradaCancel` int(11) NOT NULL AUTO_INCREMENT,
  `nIdEntrada` int(11) DEFAULT NULL,
  `nIdUsuario` int(11) DEFAULT NULL,
  `dtCancelacion` datetime DEFAULT NULL,
  `sObservacion` varchar(45) DEFAULT NULL,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdEntradaCancel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

 * 
 ***********/
