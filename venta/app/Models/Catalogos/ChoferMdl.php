<?php

namespace App\Models\Catalogos;

use CodeIgniter\Model;

class ChoferMdl extends Model
{
    protected $table = 'enchofer';

    protected $allowedFields = ['sChofer', 'sLicenciaNo', 'sCelular', 'dVencimientoLic', 'nIdVehiculo', ];
    
    protected $primaryKey = 'nIdChofer';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = false, $paginado = false)
    {
        if ($id === false) {
            if ($paginado) {
                return $this->paginate($paginado);
            } else {
                return $this->findAll();
            }
        }
        return $this->where(['nIdChofer' => $id])->first();
    }

    // public function getRegistros($id = 0)
    // {
    //     if ($id === 0) {
    //         return $this->findAll();
    //     }
    //     return $this->where(['nIdChofer' =>  $id])->first();
    // }
    
    public function getRegistrosByName($val = false)
    {
        return $this->like(['sChofer'=> $val])->findAll();
    }
}
/***********
 *

CREATE TABLE `ferromat`.`enchofer` (
  `nIdChofer` INT NOT NULL AUTO_INCREMENT,
  `sChofer` VARCHAR(250) NULL,
  `sLicenciaNo` VARCHAR(85) NULL,
  `sCelular` VARCHAR(45) NULL,
  `dVencimientoLic` DATE NULL,
  `nIdVehiculo` INT NULL,
  `dtAlta` DATETIME NULL,
  `dtBaja` DATETIME NULL,
  PRIMARY KEY (`nIdChofer`));

 *
 ***********/
