<?php
namespace App\Models\Viajes;

use CodeIgniter\Model;

class EnvioDireccionMdl extends Model
{
    protected $table = 'enenviodireccion';

    protected $allowedFields = [
        'nIdDirEntrega', 'nIdEnvio', 'sDirecEnvio'
    ];

    protected $primaryKey = 'nIdDirEntrega';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';
}