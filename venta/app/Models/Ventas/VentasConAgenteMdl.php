<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class VentasConAgenteMdl extends Model
{
    protected $table = 'vtventasconagente';

    protected $allowedFields = ['nIdVentas', 'nIdAgenteVentas'];

    protected $primaryKey = 'nidVentas';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

    public function getRegistroAgente($idVenta)
    {
        return $this
            ->select('a.*')
            ->join('vtagenteventas a', 'vtventasconagente.nIdAgenteVentas = a.nIdAgenteVentas', 'inner')
            ->where('nIdVentas', $idVenta)->first();
    }
}
