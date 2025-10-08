<?php

namespace App\Models\Catalogos;

use CodeIgniter\Model;

class RazonSocialMdl extends Model
{
    protected $table = 'satrazonsocial';

    protected $allowedFields = ['sRFC', 'sRazonSocial', 'cIdRegimenFiscal', 'sIdPAC', 'sPswPAC', 'sCertificado', 'bBloqueo', 'sLeyenda'];

    protected $primaryKey = 'nIdRazonSocial';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

    public function getRegistros($id = false, $paginado = false, $paraListado = false)
    {
        if ($id === false) {
            if($paraListado) $this->select('nIdRazonSocial, CONCAT_WS(\' | \', sRFC, sRazonSocial) as sDescripcion', false);
            if($paginado) {
                return $this->paginate($paginado);
            } else {
                return $this->findAll();
            }
        }
        return $this->where(['nIdRazonSocial' => $id])->first();
    }

    public function getRegistrosByName($val = false)
    {
        return $this->like(['sRazonSocial' => $val])->findAll();
    }

    public function bloqueaParaFacturar($id, $bloquearRegistro = true)
    {
        if ($bloquearRegistro) {
            $ret = $this->set('bBloqueo', '1')
                ->where('nIdRazonSocial', $id)
                ->update();
            if ($ret === false) return false;
        }
        return $this->where('nIdRazonSocial', $id)->first();
        // ->builder->get()->getFirstRow('array');
    }
}
