<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class FacturaEspecialMdl extends Model
{
    protected $table = 'vtfacturaespecial';

    protected $allowedFields = ['nIdVentas'];

    protected $primaryKey = 'nIdVentas';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

}
