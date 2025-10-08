<?php

namespace App\Models\Catalogos;

use CodeIgniter\Model;

class RegimenFiscalMdl extends Model
{
    protected $table = 'satregimenfiscal';

    protected $allowedFields = ['cIdRegimenFiscal', 'sDescripcion'];
 
    protected $primaryKey = 'cIdRegimenFiscal';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

    public function getRegistros($id = false, bool $bFisica = null, bool $bMoral = null, $paraListado = false)
    {
        
        if ($id === false) {
            if($paraListado) {
                $this->select('cIdRegimenFiscal, CONCAT_WS(\' | \', cIdRegimenFiscal, sDescripcion) as sDescripcion', false);
            }
            if($bFisica !== null) {
                $this->where('bPersonaFisica', $bFisica ? '0' : '1');
            }
            if($bMoral !== null) {
                $this->where('bPersonaMoral', $bMoral ? '0' : '1');
            }
            return $this->findAll();
        }
        return $this->where(['cIdRegimenFiscal' => $id])->first();
    }
    
    public function getRegistrosByName($val = false)
    {
        return $this->like(['sDescripcion'=> $val])->findAll();
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
