<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class CotizacionesDetMdl extends Model
{
    protected $table = 'vtcotizacionesdet';

    protected $allowedFields = [
        'nIdCotizaciones', 'nIdArticulo', 'nPrecio', 'nCant',
        'nPorcenDescuento', 'nDescuentoTotal'
    ];

    protected $primaryKey = 'nIdCotizacionesDet';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

    protected $useAutoIncrement = true;

    public function getRegistros($idCotizaciones, $conDescripcion = false, $soloID = false)
    {
        if ($conDescripcion) {
            $this->select('vtcotizacionesdet.*, b.sDescripcion as nomArt')
                ->join('alarticulo as b', 'b.nIdArticulo = vtcotizacionesdet.nIdArticulo');
        }
        if($soloID) $this->select('nIdCotizacionesDet');
        return $this->where(['nIdCotizaciones' => $idCotizaciones])->findAll();
    }
}
/****************
 *
 *
 * 
CREATE TABLE IF NOT EXISTS `vtcotizacionesdet` (
  `nIdCotizaciones` INT NULL,
  `nIdArticulo` INT NULL,
  `nPrecio` DECIMAL(10,2) NULL,
  `nCant` DECIMAL(9,3) NULL,
  `nPorcenDescuento` DECIMAL(5,2) NULL,
  INDEX `IDX_cotizacion` (`nIdCotizaciones` ASC))
  ENGINE = InnoDB
 */
