<?php

namespace App\Models\Catalogos;

use CodeIgniter\Model;

class TipoListaMdl extends Model
{
    protected $table = 'altipolista';

    protected $allowedFields = ['bImprimirNota', 'cTipo', 'cNombreTipo'];

    protected $primaryKey = 'nIdTipoLista';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = false, $soloTapado = false, $paginado = false, $tipoLst = 0, $paraListado = false)
    {
        if($tipoLst == 1) {
            $this->select('nIdTipoLista, cNombreTipo, bImprimirNota');
        }
        if ($soloTapado) {
            $a = ['bImprimirNota' => '1'];
        } else {
            if ($id === false) {
                if($paraListado) {
                    $this->select('nIdTipoLista, CONCAT_WS(\' | \', nIdTipoLista, cNombreTipo) as cNombreTipo', false);
                }
                if($paginado) {
                    return $this->paginate($paginado);
                } else {
                    return $this->findAll();
                }
            }
            $a = ['nIdTipoLista' => $id];
        }
        return $this->where($a)->first();
    }

    public function getRegistrosByName($val = false)
    {
        return $this->like(['sNombreTipo' => $val])->findAll();
    }
}
/****************
 *
 *
CREATE TABLE IF NOT EXISTS `satregimenfiscal` (
  `cIdRegimenFiscal` VARCHAR(3) NOT NULL,
  `sDescripcion` VARCHAR(250) NULL,
  PRIMARY KEY (`cIdRegimenFiscal`))
ENGINE = InnoDB

 *
 */
