<?php

namespace App\Models\Catalogos;

use CodeIgniter\Model;

class UsoCfdiMdl extends Model
{
    protected $table = 'satusocfdi';

    protected $allowedFields = ['cIdUsoCfdi', 'sDescripcion'];

    protected $primaryKey = 'cIdUsoCfdi';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

    public function getRegistros($id = false, $listaIds = false, bool $bFisica = null, bool $bMoral = null, $paraListado = false)
    {
        if ($id === false) {
            if($paraListado) {
                $this->select('cIdUsoCfdi, CONCAT_WS(\' | \', cIdUsoCfdi, sDescripcion) as sDescripcion', false);
            }
            if ($listaIds)  $this->select('GROUP_CONCAT(cIdUsoCfdi) AS lst');
            $c = '';
            if($bFisica !== null) {
                $c = 'bPersonaFisica = \'' . ($bFisica ? '1' : '0') . '\'';
            }
            if($bMoral !== null) {
                $c = $c . ($c == '' ? '' : ' OR ') . 'bPersonaMoral = \'' . ($bMoral ? '1' : '0') . '\'';
            }
            if($c !== '') $this->where('(' . $c . ')', null, false);
            //$m = $this->builder()->getCompiledSelect(true);
            return $this->findAll();
        }
        return $this->where(['cIdUsoCfdi' => $id])->first();
    }

    public function getRegistrosByName($val = false)
    {
        return $this->like(['sDescripcion' => $val])->findAll();
    }
}
/****************
 *
 *
 * CREATE TABLE IF NOT EXISTS `satusocfdi` (
  `cIdUsoCfdi` VARCHAR(5) NOT NULL,
  `sDescripcion` VARCHAR(250) NULL,
  PRIMARY KEY (`cIdUsoCfdi`))
ENGINE = InnoDB

 *
 */
