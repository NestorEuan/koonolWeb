<?php
namespace App\Models\Ventas;

use CodeIgniter\Model;

class EsCajaMdl extends Model
{
    protected $table = 'vtescaja';

    protected $allowedFields = [ 'nIdSucursal', 'cTipoMov', 'sMotivo', 'nImporte' ];
 
    protected $primaryKey = 'nIdEScaja';

    protected $useTimestamps = true;

    protected $useSoftDeletes = false;

    protected $createdField = 'dtAlta';

    protected $deletedField = '';

    protected $updatedField = '';

    public function getRegistros($id = false, $paginado = false, $idSuc = false)
    {
        $this->select('vtescaja.*, ' .
            'IF(vtescaja.cTipoMov = \'E\', \'Ingreso\', \'Egreso\') as cTipMov');
        if ($id === false) {
            if($idSuc !== false) {
                $this->where(['nIdSucursal' => intval($idSuc)]);
            }
            if ($paginado) {
                return $this->paginate(10);
            } else {
                return $this->findAll();
            }
        }
        return $this->where(['nIdEScaja' => $id])->first();
    }
}
/*
CREATE TABLE IF NOT EXISTS `vtescaja` (
  `nIdEScaja` INT NOT NULL AUTO_INCREMENT,
  `cTipoMov` VARCHAR(1) NULL,
  `sMotivo` VARCHAR(250) NULL,
  `nImporte` DECIMAL(10,2) NULL,
  `dtAlta` DATETIME NULL,
  PRIMARY KEY (`nIdEScaja`))
ENGINE = InnoDB
*/
