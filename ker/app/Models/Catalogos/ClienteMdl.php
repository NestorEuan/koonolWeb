<?php

namespace App\Models\Catalogos;

use CodeIgniter\Model;

class ClienteMdl extends Model
{
    protected $table = 'vtcliente';

    protected $allowedFields = [
        'sNombre', 'sDireccion', 'sCelular', 'sRFC',
        'email', 'nSaldo', 'cCP', 'cIdRegimenFiscal', 'sEnvEntrega',
        'sEnvDireccion', 'sEnvColonia', 'sEnvTelefono',
        'sEnvReferencia', 'nIdTipoLista', 'cTipoCliente',
        'sNombreConstanciaFiscal', 'cIdUsoCfdi'
    ];

    protected $primaryKey = 'nIdCliente';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = false, $paginate = false, $bTodos = false, $filtro = false)
    {
        if (!$bTodos) $this->whereIn('cTipoCliente', ['N', 'P']);
        if ($id === false) {
            if ($filtro) $this->like('sNombre', $filtro);
            if ($paginate) {
                return $this->paginate($paginate);
            } else {
                return $this->findAll();
            }
        } else {
            $this->join('altipolista t', 'vtcliente.nIdTipoLista = t.nIdTipoLista', 'left')
                ->select('vtcliente.*, t.cNombreTipo AS nomTipoLista');
        }
        return $this->where(['nIdCliente' => $id])->first();
    }

    public function getRegistrosByName($val = false)
    {
        return $this->like(['sNombre' => $val])->whereIn('cTipoCliente', ['N', 'P'])->findAll();
    }

    public function actualizaSaldo($id, $nImporte, $tipo = '+')
    {
        return $this
            ->where('nIdCliente', intval($id))
            ->set(
                'nSaldo',
                'vtcliente.nSaldo' . $tipo . strval(round(floatval($nImporte), 2)),
                false
            )
            ->update();
    }
}
/****************
 *
 *
 * CREATE TABLE IF NOT EXISTS `vtcliente` (
  `nIdCliente` INT NOT NULL AUTO_INCREMENT,
  `sNombre` VARCHAR(250) NULL,
  `sDireccion` VARCHAR(250) NULL,
  `sCelular` VARCHAR(45) NULL,
  `sRFC` VARCHAR(15) NULL,
  `email` VARCHAR(95) NULL,
  `nSaldo` DECIMAL(11,2) NULL DEFAULT 0,
  `cCP` VARCHAR(7) NULL,
  `cRegimenFiscal` VARCHAR(3) NULL,
  `sEnvEntrega` VARCHAR(120) NULL,
  `sEnvDireccion` VARCHAR(240) NULL,
  `sEnvColonia` VARCHAR(70) NULL,
  `sEnvTelefono` VARCHAR(45) NULL,
  `sEnvReferencia` VARCHAR(250) NULL,
  `nIdTipoLista` INT NULL,
  `dtAlta` DATETIME NULL,
  `dtBaja` DATETIME NULL,
  PRIMARY KEY (`nIdCliente`))
ENGINE = InnoDB

 *
 */
