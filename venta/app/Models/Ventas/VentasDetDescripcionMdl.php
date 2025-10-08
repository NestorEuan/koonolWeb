<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class VentasDetDescripcionMdl extends Model
{
    protected $table = 'vtventasdetdescripcion';

    protected $allowedFields = [
        'nIdVentasDet', 'sDescripcion'
    ];

    protected $primaryKey = 'nIdVentasDet';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

    public function getRegistros($id = false)
    {
        if ($id === false) {
            return $this->findAll();
        }
        return $this->where(['nIdVentasDetRel' => $id])->first();
    }
}
