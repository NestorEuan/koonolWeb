<?php

namespace App\Models\Catalogos;

use CodeIgniter\Model;

class TipoRelacionMdl extends Model
{
    protected $table = 'sattiporelacion';

    protected $allowedFields = ['cIdTipoRelacion', 'sDescripcion'];
 
    protected $primaryKey = 'cIdTipoRelacion';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

    public function getRegistros($id = false, $paraListado = false)
    {
        
        if ($id === false) {
            if($paraListado) {
                $this->select('cIdTipoRelacion, CONCAT_WS(\' | \', cIdTipoRelacion, sDescripcion) as sDescripcion', false);
            }
            return $this->findAll();
        }
        return $this->where(['cIdTipoRelacion' => $id])->first();
    }
    
    public function getRegistrosByName($val = false)
    {
        return $this->like(['sDescripcion'=> $val])->findAll();
    }
}