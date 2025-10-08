<?php

namespace App\Models\Seguridad;

use CodeIgniter\Model;

class UsuarioMdl extends Model
{
    protected $table = 'sgusuario';

    protected $allowedFields = [
        'sLogin', 'sNombre', 'sPsw', 'cNuevo', 'nIdPerfil', 'nIdSucursal',
        'bDaDescuento', 'bDaPrecio', 'bPrecioSinRecal', 'bDaVtaSinDisponibles',
        'bDivFacNoValidar', 'bCancelarRemision', 'bMantenerPrecioCotiza',
        'bCambioFechaFactura', 'bPermitirCredito'
    ];

    protected $primaryKey = 'nIdUsuario';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = false, $paginado = false, $idSucursal = false, $esSuper = false)
    {
        if ($id === false) {
            $this->select('sgusuario.*, alsucursal.sDescripcion as nomSucursal, sgperfil.sPerfil as nomPerfil')
                ->join('alsucursal', 'alsucursal.nIdSucursal = sgusuario.nIdSucursal', 'left')
                ->join('sgperfil', 'sgperfil.nIdPerfil = sgusuario.nIdPerfil', 'left');
            if ($idSucursal) $this->where('sgusuario.nIdSucursal', $idSucursal);
            if ($esSuper === false) $this->where('sgusuario.nIdPerfil <>', '-1');
            if ($paginado) {
                return $this->paginate(8);
            } else {
                return $this->findAll();
            }
        }
        return $this->where(['nIdUsuario' => $id])->first();
    }

    public function getRegistroByLogin($val = false)
    {

        return $this->select('sgusuario.*, sc.*')->where(['sLogin' => $val])
            ->join('alsucursal sc', 'sc.nIdSucursal = sgusuario.nIdSucursal', 'left')
            ->join('vtdirentrega de', 'de.nIdCliente = sc.nIdCliente', 'left')
            ->first();
    }

    public function updtPassword($id, $password)
    {
        $this->set('sPsw', $password)
            ->where(['nIdUsuario' => $id])
            ->update();
    }

    public function activaPermiso($id, $activa, $tipo)
    {
        if ($tipo == 'D') {
            $this->set('bDaDescuento', $activa);
        } elseif ($tipo == 'P') {
            $this->set('bDaPrecio', $activa);
        } elseif ($tipo == 'PSC') {
            $this->set('bPrecioSinRecal', $activa);
        } elseif ($tipo == 'V') {
            $this->set('bDaVtaSinDisponibles', $activa);
        } elseif ($tipo == 'NV') {
            $this->set('bDivFacNoValidar', $activa);
        } elseif ($tipo == 'CR') {
            $this->set('bCancelarRemision', $activa);
        } elseif ($tipo == 'MPC') {
            $this->set('bMantenerPrecioCotiza', $activa);
        } elseif ($tipo == 'CFF') {
            $this->set('bCambioFechaFactura', $activa);
        } elseif ($tipo == 'PC') {
            $this->set('bPermitirCredito', $activa);
        }
        $this->where(['nIdUsuario' => $id])
            ->update();
    }

    public function checkPass($data)
    {
        echo 'Hello';
        var_dump($data);
        exit;
    }
}
/***********
 *

CREATE TABLE `sgusuario` (
  `nIdUsuario` int(11) NOT NULL AUTO_INCREMENT,
  `sLogin` varchar(45) DEFAULT NULL,
  `sNombre` varchar(450) DEFAULT NULL,
  `sPsw` varchar(100) DEFAULT NULL,
  `cNuevo` varchar(45) DEFAULT NULL,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ferromat`.`sgusuario` (`sLogin`, `sNombre`, `dtAlta`) VALUES ('admin', 'administrador', '2022-07-14');

 *
 ***********/
