<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class VentasImpuestosMdl extends Model
{
    protected $table = 'vtventasimpuestos';

    protected $allowedFields = ['nIdVentasDet', 'nIdTipoImpuesto', 'nBase', 'nImporte', 'fValor'];

    protected $primaryKey = 'nIdVentasDet';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

    protected $useAutoIncrement = false;

    public function getRegistros($id)
    {
        return $this->where(['nIdVentasDet' => $id])->findAll();
    }

    public function getImpuestoBase($id)
    {
        return $this->select('vtventasimpuestos.nBase, vtventasimpuestos.nImporte, i.*')
            ->join(
                'sattipoimpuesto as i',
                'i.nIdTipoImpuesto = vtventasimpuestos.nIdTipoImpuesto'
            )->where(['nIdVentasDet' => $id])->findAll();
    }
}
