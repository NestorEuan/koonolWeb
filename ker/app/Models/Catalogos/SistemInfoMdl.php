<?php

namespace App\Models\Catalogos;

use CodeIgniter\Model;

class SistemInfoMdl extends Model
{
    protected $table = 'grsisteminfo';

    protected $allowedFields = [
        'nomempresa', 'bannerlogin', 'bannermain',
        'bannernavbrand', 'logobottom', 'icono', 'logoimprimir'
    ];

    protected $primaryKey = 'idsisteminfo';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

    public function getRegistro()
    {
        return $this->where(['idsisteminfo' => 1])->first();
    }


}
/***********
 *
CREATE TABLE `grsisteminfo` (
  `idsisteminfo` int(11) NOT NULL,
  `nomempresa` varchar(80) NOT NULL,
  `bannerlogin` varchar(45) DEFAULT NULL,
  `bannermain` varchar(45) DEFAULT NULL,
  `bannernavbrand` varchar(45) DEFAULT NULL,
  `logobottom` varchar(45) DEFAULT NULL,
  `icono` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`idsisteminfo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Información del sistema, nombre de empresa, nombre de los logos para pantalla de inicio, desktop, logo prinpipal, etc';

 *
 ***********/
