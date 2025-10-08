<?php
namespace App\Models\Ventas;

use CodeIgniter\Model;

class VentasNomEntrega extends Model
{
    protected $table = 'vtventasnomentrega';

    protected $allowedFields = [ 'nIdVentas', 'cNomEntrega' ];
 
    protected $primaryKey = 'nIdVentas';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';
    
}
