<?php

namespace App\Models\Catalogos;

use CodeIgniter\Model;

class SucursalMdl extends Model
{
    protected $table = 'alsucursal';

    protected $allowedFields = [
        'nIdSucursal', 'sDescripcion', 'sDireccion',
        'sClave', 'sCelular', 'sEncargado', 'sSerie', 'sCP',
        'nFolioRemision', 'nFolioCotizacion', 'nFolioFactura',
        'nIdCliente', 'sEmail', 'nIdRazonSocial'
    ];

    protected $primaryKey = 'nIdSucursal';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = false, $paginado = false, $excluirSucursal = false, $conCliente = false)
    {
        if($conCliente) {
            $this->join('vtdirentrega de', 'de.nIdCliente = alsucursal.nIdCliente', 'left');
        }
        if ($id === false) {
            if ($paginado) {
                return $this->paginate(10);
            } else {
                if ($excluirSucursal) {
                    $this->where('nIdSucursal <>', $excluirSucursal);
                }
                return $this->findAll();
            }
        }
        return $this->where(['nIdSucursal' => $id])->first();
    }

    public function getRegistrosByName($val = false, $idsucExcluida = false)
    {
        if($idsucExcluida) $this->where('nIdSucursal <>', $idsucExcluida);
        return $this
            //->select('')
            ->join('vtdirentrega de', 'de.nIdCliente = alsucursal.nIdCliente','left')
            ->like(['sDescripcion' => $val])->findAll();
    }

    public function leeFolio($idSuc, $bloquear = true)
    {
        // hago un update para bloquear
        if ($bloquear) {
            $res = $this->set('sSerie', 'alsucursal.sSerie', false)
                ->where('nIdSucursal', $idSuc)->update();
            if ($res === false) return false;
        }
        return $this->select('alsucursal.nIdSucursal, alsucursal.nFolioRemision, ' .
            'alsucursal.nFolioFactura, alsucursal.nFolioCotizacion, alsucursal.sSerie, ' .
            'IFNULL(rz.sIdPAC, \'\') AS sIdPAC, IFNULL(rz.sPswPAC, \'\') AS sPswPAC', false)
            ->join('satrazonsocial rz', 'alsucursal.nIdRazonSocial = rz.nIdRazonSocial', 'left')
            ->where('nIdSucursal', $idSuc)
            ->first();
    }

    public function addFolioRem($idSuc)
    {
        return $this->where('nIdSucursal', $idSuc)
            ->set('nFolioRemision', 'nFolioRemision+1', false)
            ->update();
    }

    public function addFolioFac($idSuc, $nuevoFolio = false)
    {
        if ($nuevoFolio)
            $this->set('nFolioFactura', $nuevoFolio);
        else
            $this->set('nFolioFactura', 'nFolioFactura+1', false);

        return $this->where('nIdSucursal', $idSuc)->update();
    }

    public function addFolioCot($idSuc)
    {
        return $this->where('nIdSucursal', $idSuc)
            ->set('nFolioCotizacion', 'nFolioCotizacion+1', false)
            ->update();
    }

    public function getRegistroParaFacturar($id)
    {
        return $this->select('alsucursal.*, rz.sRFC, rz.sRazonSocial, rz.sIdPAC, rz.sPswPAC, rz.sCertificado, rz.sLeyenda, rf.*')
            ->join('satrazonsocial rz', 'alsucursal.nIdRazonSocial = rz.nIdRazonSocial', 'inner')
            ->join('satregimenfiscal rf', 'rz.cIdRegimenFiscal = rf.cIdRegimenFiscal', 'inner')
            ->where('alsucursal.nIdSucursal', $id)
            ->find();
    }
}
/***********
 *
 * CREATE TABLE IF NOT EXISTS `alsucursal` (
  `nIdSucursal` INT NOT NULL AUTO_INCREMENT,
  `sDescripcion` VARCHAR(250) NULL,
  `sDireccion` VARCHAR(250) NULL,
  `sClave` VARCHAR(80) NULL,
  `sCelular` VARCHAR(45) NULL,
  `sEncargado` VARCHAR(250) NULL,
  `sSerie` VARCHAR(3) NULL,
  `sCP` VARCHAR(7) NULL DEFAULT '',
  `nFolioRemision` INT NULL,
  `nFolioCotizacion` INT NULL,
  `nFolioFactura` INT NULL,
  `dtAlta` DATETIME NULL,
  `dtBaja` DATETIME NULL,
  PRIMARY KEY (`nIdSucursal`))
ENGINE = InnoDB

 *
 ***********/
