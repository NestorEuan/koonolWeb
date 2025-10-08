<?php

namespace App\Models\Catalogos;

use CodeIgniter\Model;

class ArticuloImpuestosMdl extends Model
{
    protected $table = 'alarticuloimpuestos';

    protected $allowedFields = [ 'nIdArticulo', 'nIdTipoImpuesto' ];

    protected $primaryKey = 'nIdArticuloImpuestos';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = false)
    {
        if ($id === false) {
            return $this->findAll();
        }
        return $this->where(['nIdArticuloImpuestos' =>  $id])->first();
    }

}
/***********************
 *
CREATE TABLE IF NOT EXISTS `alarticuloimpuestos` (
  `nIdArticuloImpuestos` INT NOT NULL AUTO_INCREMENT,
  `nIdArticulo` INT NULL,
  `nIdTipoImpuesto` SMALLINT NULL,
  `dtAlta` DATETIME NULL,
  `dtBaja` DATETIME NULL,
  PRIMARY KEY (`nIdArticuloImpuestos`))
ENGINE = InnoDB

 *
 ***********/
