<?php
namespace App\Models\Ventas;

use CodeIgniter\Model;

class VentasNomEntregaMdl extends Model
{
    protected $table = 'vtventasnomentrega';

    protected $allowedFields = [ 'nIdVentas', 'cNomEntrega' ];
 
    protected $primaryKey = 'nIdVentas';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

    public function getNomEntrega($idVenta)
    {
        return $this->select('cNomEntrega')
        ->where('nIdVentas', $idVenta)
        ->first();
    }
    
}
