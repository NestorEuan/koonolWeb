<?php
namespace App\Models\Ventas;

use CodeIgniter\Model;

class VentasCambioMdl extends Model
{
    protected $table = 'vtventascambio';

    protected $allowedFields = [ 'nIdVentas', 'nImporteEntregado', 'nCambio' ];
 
    protected $primaryKey = 'nIdVentas';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

}
