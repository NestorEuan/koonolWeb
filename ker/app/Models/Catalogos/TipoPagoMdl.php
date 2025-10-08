<?php

namespace App\Models\Catalogos;

use CodeIgniter\Model;

class TipoPagoMdl extends Model
{
    protected $table = 'vttipopago';

    protected $allowedFields = ['sDescripcion', 'sLeyenda', 'nComision', 'sTipoSAT', 'cTipo'];

    protected $primaryKey = 'nIdTipoPago';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = false, $paginado = false, $filtroIDs = false)
    {
        if ($id === false) {

            if ($paginado) {
                return $this->paginate(10);
            } else {
                if ($filtroIDs) $this->whereIn('nIdTipoPago', explode(',', $filtroIDs));
                return $this->findAll();
            }
        }
        return $this->where(['nIdTipoPago' => $id])->first();
    }

    public function getTipoSAT($tipo)
    {
        return $this->select('sTipoSAT, sLeyenda')
            ->where('sTipoSAT', $tipo)->first();
    }
}
/*
CREATE TABLE IF NOT EXISTS `vttipopago` (
  `nIdTipoPago` INT NOT NULL AUTO_INCREMENT,
  `sLeyenda` VARCHAR(40) NULL,
  `sDescripcion` VARCHAR(100) NULL,
  `nComision` DECIMAL(5,2) NULL,
  `sTipoSAT` VARCHAR(2) NULL,
  `dtAlta` DATETIME NULL,
  `dtBaja` DATETIME NULL,
  PRIMARY KEY (`nIdTipoPago`))
ENGINE = InnoDB

*/
