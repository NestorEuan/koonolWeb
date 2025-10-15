<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class CorteCajaArqueoMdl extends Model
{
    protected $table = 'vtcortearqueo';

    protected $allowedFields = [
        'nIdCorte', 'cTipoMov', 'cTipoValor', 'nValor', 'nCant', 'nCantNueva'
    ];

    protected $primaryKey = 'nIdCorte';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

    public function getUltimoArqueo($idUsuario, $idSucursal, $tipoMov)
    {
        $this->select('*, CONCAT(cTipoValor,\'-\', REPLACE(CAST(IF(nValor < 1, nValor, TRUNCATE(nValor, 0)) AS CHAR), \'.\', \'\')) AS cId', false)
        ->where('vtcortearqueo.nIdCorte = (SELECT nIdCorte FROM vtcorte WHERE vtcorte.nIdSucursal = ' . $idSucursal .
            ' AND vtcorte.nIdUsuario = ' . $idUsuario . ' ORDER BY vtcorte.nIdCorte DESC LIMIT 1)' .
            ' AND vtcortearqueo.cTipoMov = \'' . $tipoMov . '\'', null, false);
        return $this->findAll();
    }
}
