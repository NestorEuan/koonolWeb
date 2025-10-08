<?php

namespace App\Models\Seguridad;

use CodeIgniter\Model;

class PerfilMdl extends Model
{
    protected $table = 'sgperfil';

    protected $allowedFields = ['sPerfil'];

    protected $primaryKey = 'nIdPerfil';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';


    public function getRegistros($id = false, $paginado = false)
    {
        if ($id === false) {
            if ($paginado)
                return $this->paginate($paginado);
            else
                return $this->findAll();
        }
        return $this->where(['nIdPerfil' =>  $id])->first();
    }

    public function getRegistroByFld($sFld, $val = false)
    {
        return $this->where([$sFld => $val])->first();
    }
}

/***********
 * 

CREATE TABLE `sgmenu` (
  `nIdMenu` int(11) NOT NULL AUTO_INCREMENT,
  `nIdPadre` int(11) DEFAULT 0,
  `sDescripcion` varchar(85) DEFAULT NULL,
  `sAbrev` varchar(45) DEFAULT NULL,
  `sLink` varchar(45) DEFAULT NULL,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdMenu`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

 *
 ***********/
