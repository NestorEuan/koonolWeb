<?php

namespace App\Models\Catalogos;

use CodeIgniter\Model;

class TipoImpuestoMdl extends Model
{
    protected $table = 'sattipoimpuesto';

    protected $allowedFields = ['sDescripcion', 'cTipoImpuesto', 'cTipo', 'cTipoValor', 'fvalor'];
 
    protected $primaryKey = 'nIdTipoImpuesto';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

    public function getRegistros($id = false)
    {
        if ($id === false) {
            return $this->findAll();
        }
        return $this->where(['nIdTipoImpuesto' => $id])->first();
    }
    
    public function getRegistrosByName($val = false)
    {
        return $this->like(['sDescripcion'=> $val])->findAll();
    }
}
/****************
 *
 *
 * 
 CREATE TABLE IF NOT EXISTS `sattipoimpuesto` (
  `nIdTipoImpuesto` SMALLINT NOT NULL,
  `sDescripcion` VARCHAR(45) NULL,
  `cTipoImpuesto` VARCHAR(3) NULL COMMENT 'IVA, ISR, IEPS',
  `cTipo` VARCHAR(1) NULL COMMENT 'Retencion, Traslado',
  `cTipoValor` VARCHAR(1) NULL COMMENT 'tasa, Cuota',
  `fValor` DOUBLE NULL,
  PRIMARY KEY (`nIdTipoImpuesto`))
ENGINE = InnoDB

*/
