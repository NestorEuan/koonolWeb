<?php

namespace App\Models\Cuentas;

use CodeIgniter\Model;

class CompraPagoMdl extends Model
{
    protected $table = 'cpcomprapago';

    protected $allowedFields = [
        'nIdCompra', 'dtPago',
        'nIdTipoPago', 'fPago', 'nIdUsuario', 
        'cEstadoPago'
    ];

    protected $primaryKey = 'nIdCompraPago';

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
                return $this->paginate(8);
            } else {
                return $this->findAll();
            }
        }
        return $this->where(['nIdArticulo' => $id])->first();
    }

    public function getRegistrosByName($val = false)
    {
        return $this->like(['sDescripcion' => $val])->findAll();
    }

}
/***********************
 *

CREATE TABLE `alarticulo` (
  `nIdArticulo` int(11) NOT NULL AUTO_INCREMENT,
  `nIdArtClasificacion` int(11) DEFAULT NULL,
  `sDescripcion` varchar(450) DEFAULT NULL,
  `fExistencia` double DEFAULT 0,
  `nPrecio` decimal(10,2) DEFAULT 0,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdArticulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 *
 ***********/
