<?php

namespace App\Models\Catalogos;

use CodeIgniter\Model;

class AgenteVentasMdl extends Model
{
    protected $table = 'vtagenteventas';

    protected $allowedFields = ['sNombre',];
    
    protected $primaryKey = 'nIdAgenteVentas';
    
    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = false, $paginado = false, $tipoLst = 0)
    {
        if($tipoLst == 1) {
            $this->select('nIdAgenteVentas as idAgente, sNombre as sDes');
            $this->orderBy('sNombre', 'ASC');
        }
        if ($id === false) {
            if($paginado) {
                return $this->paginate($paginado);
            } else {
                return $this->findAll();
            }
        }
        return $this->where(['nIdAgenteVentas' => $id])->first();
    }
}
