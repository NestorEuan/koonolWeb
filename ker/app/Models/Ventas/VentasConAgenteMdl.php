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
  
}
