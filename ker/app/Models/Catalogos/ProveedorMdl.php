<?php

namespace App\Models\Catalogos;

use CodeIgniter\Model;

class ProveedorMdl extends Model
{
    protected $table = 'cpproveedor';

    protected $allowedFields = ['sNombre', 'sRepresentante', 'sRFC', 'sDireccion', 'sCelular', 'email', 'fSaldo'];

    protected $primaryKey = 'nIdProveedor';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = false)
    {
        if ($id === false) {
            return $this->findAll();
        }
        return $this->where(['nIdProveedor' => $id])->first();
    }

    public function getRegistrosByName($val = false)
    {
        return $this->like(['sNombre' => $val])->findAll();
    }

    public function updtSaldo($id, $fImporte)
    {
        $this->set('fSaldo', 'fSaldo + ' . $fImporte, false )
            ->where(['nIdProveedor' => $id ])
            ->update();
    }
}
/****************
 *
 *
CREATE TABLE `cpproveedor` (
  `nIdProveedor` int(11) NOT NULL AUTO_INCREMENT,
  `sNombre` varchar(250) DEFAULT NULL,
  `sRepresentante` varchar(250) DEFAULT NULL,
  `sRFC` varchar(45) DEFAULT NULL,
  `sDireccion` varchar(250) DEFAULT NULL,
  `sCelular` varchar(45) DEFAULT NULL,
  `email` varchar(90) DEFAULT NULL,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdProveedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 *
 */
