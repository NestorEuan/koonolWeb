<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class VentasDetRelMdl extends Model
{
    protected $table = 'vtventasdetrel';

    protected $allowedFields = [
        'nIdVentasDet', 'nIdArticulo', 'nCant',
        'nEnvio', 'nEntrega', 'nEntregado'
    ];

    protected $primaryKey = 'nIdVentasDetRel';

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
