<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class VentasPagoMdl extends Model
{
  protected $table = 'vtventaspago';

  protected $allowedFields = ['nIdVentas', 'nIdTipoPago', 'nImporte'];

  protected $primaryKey = 'nIdVentas';

  protected $useTimestamps = false;

  protected $useSoftDeletes = false;

  protected $createdField = '';

  protected $deletedField = '';

  protected $updatedField = '';

  protected $useAutoIncrement = false;

  public function getRegistros($id)
  {
    $this->select('vtventaspago.*, b.sLeyenda as nomPago')
      ->join('vttipopago as b', 'b.nIdTipoPago = vtventaspago.nIdTipoPago', 'inner');
    return $this->where(['nIdVentas' => $id])->findAll();
  }
}


/*
CREATE TABLE IF NOT EXISTS `vtventaspago` (
  `nIdVentas` INT NOT NULL,
  `nIdTipoPago` INT NULL,
  `nImporte` DECIMAL(11,2) NULL,
  INDEX `IDXventas` (`nIdVentas` ASC))
ENGINE = InnoDB
*/
