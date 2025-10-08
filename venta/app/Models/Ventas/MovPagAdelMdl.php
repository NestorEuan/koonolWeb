<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class MovPagAdelMdl extends Model
{
    protected $table = 'vtmovpagosadelantados';

    protected $allowedFields = [
        'nIdCliente', 'nIdVentas', 'nIdPagosAdelantados',
        'nSaldoActual', 'nImporte'
    ];

    protected $primaryKey = 'nIdMovPagosAdelantados';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

    public function getMovtos($id)
    {
        $this->select('vtmovpagosadelantados.*, b.nFolioRemision, c.sNombre');
        $this->join('vtventas b', 'vtmovpagosadelantados.nIdVentas = b.nIdVentas', 'inner');
        $this->join('vtcliente c', 'b.nIdCliente = c.nIdCliente', 'inner');
        $this->where('nIdPagosAdelantados', $id);
        $this->orderBy('nIdMovPagosAdelantados', 'ASC');
        return $this->findAll();
    }

    public function getMovtosPorVentas($idVentas)
    {
        return $this->where('nIdVentas', $idVentas)
            ->orderBy('nIdMovPagosAdelantados', 'ASC')->findAll();
    }
}
