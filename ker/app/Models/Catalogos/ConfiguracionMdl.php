<?php

namespace App\Models\Catalogos;

use CodeIgniter\Model;

class ConfiguracionMdl extends Model
{
    protected $table = 'grconfiguracion';

    protected $allowedFields = ['sID', 'sDescripcion', 'sValor', 'fValor'];

    protected $primaryKey = 'nIdConfiguracion';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = 0)
    {
        if ($id === 0) {
            return $this->findAll();
        }
        return $this->where(['nIdConfiguracion' =>  $id])->first();
    }

    public function getRegistrosByName($val = false, $valorExacto = false)
    {
        if ($valorExacto)
            $this->where('sID', $val);
        else
            $this->like(['sDescripcion' => $val]);
        return $this->findAll();
    }

    public function bloqueaParaFacturar($bloquearRegistro = true)
    {
        if ($bloquearRegistro) {
            $ret = $this->set('fValor', 1)
                ->where('nIdConfiguracion', 5)
                ->update();
            if ($ret === false) return false;
        }
        return $this->select('sValor')
            ->where('nIdConfiguracion', 5)
            ->first();
        // ->builder->get()->getFirstRow('array');
    }
}
/***********
 *

CREATE TABLE `ferromat`.`grconfiguracion` (
  `nIdConfiguracion` INT NOT NULL AUTO_INCREMENT,
  `sDescripcion` VARCHAR(150) NULL,
  `sValor` VARCHAR(150) NULL,
  `fValor` DOUBLE NULL,
  `dtAlta` DATETIME NULL,
  `dtBaja` DATETIME NULL,
  PRIMARY KEY (`nIdConfiguracion`));

 *
 ***********/
