<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class VentasEsperaMdl extends Model
{
    protected $table = 'vtventasespera';

    protected $allowedFields = ['nIdUsuario', 'dtAlta', 'nIdSucursal', 'nIdCliente', 'data'];

    protected $primaryKey = 'nIdUsuario';

    protected $useTimestamps = true;

    protected $useSoftDeletes = false;

    protected $createdField = 'dtAlta';

    protected $deletedField = '';

    protected $updatedField = '';

    protected $useAutoIncrement = false;

   
}
