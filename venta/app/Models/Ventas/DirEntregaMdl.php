<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class DirEntregaMdl extends Model
{
    protected $table = 'vtdirentrega';

    protected $allowedFields = [
        'nIdCliente', 'sEnvEntrega',
        'sEnvDireccion', 'sEnvColonia', 'sEnvTelefono', 'sEnvReferencia'
    ];

    protected $primaryKey = 'nIdDirEntrega';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

    public function getRegistros($idDirEntrega = false, $idCliente = false)
    {
        if ($idDirEntrega === false) {
            if ($idCliente !== false) {
                return $this->where(['nIdCliente' => $idCliente])->findAll();
            } else {
                return [];
            }
        }
        return $this->where(['nIdDirEntrega' => $idDirEntrega])->first();
    }

    public function listaDireccionesCliente($idCliente)
    {
        $this->select('*, nIdDirEntrega as id, ' .
            'REPLACE(CONCAT_WS(\' \', sEnvDireccion, sEnvColonia), CHAR(10), \' \') as direccion', false);
        return $this->where(['nIdCliente' => $idCliente])->findAll();
    }
}
/*

*/
