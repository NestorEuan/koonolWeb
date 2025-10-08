<?php

namespace App\Models\Catalogos;

use CodeIgniter\Model;

class ArtClasificacionMdl extends Model
{
    protected $table = 'alartclasificacion';

    protected $allowedFields = ['sClasificacion', 
    'sColor', 'nPorcentajeComision',
];
    
    protected $primaryKey = 'nIdArtClasificacion';
    
    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = 0)
    {
        if ($id === 0) {
            return $this->findAll();
        }
        return $this->where(['nIdArtClasificacion' =>  $id])->first();
    }

    public function getRegistrosByName($val = false)
    {
        return $this->like(['sClasificacion'=> $val])->findAll();
    }
}
/***********************
 *

 CREATE TABLE `alartclasificacion` (
  `nIdArtClasificacion` int(11) NOT NULL AUTO_INCREMENT,
  `sClasificacion` varchar(45) DEFAULT NULL,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdArtClasificacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 *
 ***********/
