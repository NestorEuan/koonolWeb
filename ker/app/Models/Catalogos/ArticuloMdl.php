<?php

namespace App\Models\Catalogos;

use CodeIgniter\Model;

class ArticuloMdl extends Model
{
    protected $table = 'alarticulo';

    protected $allowedFields = [
        'nIdArtClasificacion', 'sDescripcion',
        'sCveProdSer', 'sCveUnidad', 'cUnidad',
        'cSinExistencia', 'fPeso', 'cImporteManual',
        'nCosto', 'cConArticuloRelacionado',
        'nIdArticuloAcumulador', 'nMedida', 'sCodigo',
        'cConDescripcionAdicional', 'sFotoArchivo', 'sRutaFoto'
    ];

    protected $primaryKey = 'nIdArticulo';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = false, $paginado = false, $conClasificacion = false, $filtro = false)
    {
        if ($conClasificacion) {
            $this->select('alarticulo.*, c.sClasificacion')->join(
                'alartclasificacion c',
                'c.nIdArtClasificacion = alarticulo.nIdArtClasificacion',
                'inner'
            );
        }
        if ($id === false) {
            if ($filtro) $this->like('sDescripcion', $filtro);
            if ($paginado) {
                return $this->paginate($paginado);
            } else {
                return $this->findAll();
            }
        }
        return $this->where(['nIdArticulo' => $id])->first();
    }

    public function getRegistrosPorCod($id, $valDuplicado = false)
    {
        if ($valDuplicado) {
            $a = [
                'sCodigo' => $id,
                'nIdArticulo <>' => $valDuplicado
            ];
        } else {
            $a = ['sCodigo' => $id];
        }
        return $this->where($a)->first();
    }

    public function getRegistrosRelacionados($id)
    {
        return $this->select('nMedida, nIdArticulo')
            ->where('nIdArticuloAcumulador', $id)
            ->orderBy('nMedida', 'ASC')
            ->findAll();
    }

    public function getRegistrosByName($val = false)
    {
        return $this->like(['sDescripcion' => $val])->findAll();
    }

    public function updtCosto($idArt, $fImporte)
    {
        $this->set('nCosto', $fImporte)
            ->where(['nIdArticulo' => $idArt])
            ->update();
        ///->getCompiledSelect()
        //var_dump(  $fRecibido . ' ' . $sql);
        //exit;
    }

    public function getRegXIdXCod($key)
    {
        return $this->where(['nIdArticulo' => $key])->orWhere(['sCodigo' => $key])->first();
    }

    public function updtPrecio($idArt, $fImporte)
    {
        $this->set('nCosto', $fImporte)
            ->where(['nIdArticulo' => $idArt])
            ->update();
        ///->getCompiledSelect()
        //var_dump(  $fRecibido . ' ' . $sql);
        //exit;
    }
}
/***********************
 *

CREATE TABLE `alarticulo` (
  `nIdArticulo` int(11) NOT NULL AUTO_INCREMENT,
  `nIdArtClasificacion` int(11) DEFAULT NULL,
  `sDescripcion` varchar(450) DEFAULT NULL,
  `fExistencia` double DEFAULT 0,
  `nCosto` decimal(10,2) DEFAULT 0,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdArticulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 *
 ***********/
