<?php

namespace App\Models\Almacen;

use CodeIgniter\Model;
use DateInterval;
use DateTime;

class MovimientoMdl extends Model
{
    protected $table = 'almovimiento';

    protected $allowedFields = ['nIdOrigen', 'cOrigen', 'nIdUsuario', 'dMovimiento', 'sObservacion',];

    protected $primaryKey = 'nIdMovimiento';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = 0)
    {
        if ($id === 0)
            return $this->findAll();
        return $this->where(['nIdMovimiento' =>  $id])->first();
    }

    public function getRegistrosByField($field = 'nIdMovimiento', $val = false)
    {
        return $this->where([$field => $val])->findAll();
    }

    public function getMovtosArticulo($idArt, $idSuc, DateTime $fIni, DateTime $fFin)
    {
        $this->select(
            'almovimiento.dtAlta, almovimiento.cOrigen, almovimiento.nIdOrigen,almovimiento.nIdUsuario,' .
                'b.nIdSucursal, b.fCantidad, b.fSaldoInicial, ' .

                'IFNULL(v.nFolioRemision, 0) AS nFolioRemisionEntrega,  ' .
                'IFNULL(sv.nIdSucursal, 0) AS nIdSucVOriEntrega, ' .
                'IFNULL(sv.sDescripcion, \'\') AS sucOriEntrega, ' .
                'IFNULL(cl.sNombre, \'\') AS sClienteVEntrega, ' .
                '(SELECT COUNT(vf.nIdFacturas) FROM vtfacturas vf WHERE vf.nIdVentas = IFNULL(v.nIdVentas, 0) LIMIT 1) AS tieneFacturaEntrega, ' .

                'IFNULL(v2.nFolioRemision, 0) AS nFolioRemisionEnvio,  ' .
                'IFNULL(sv2.nIdSucursal, 0) AS nIdSucVOriEnvio, ' .
                'IFNULL(sv2.sDescripcion, \'\') AS sucOriEnvio, ' .
                'IFNULL(cl2.sNombre, \'\') AS sClienteVEnvio, ' .
                '(SELECT COUNT(vf.nIdFacturas) FROM vtfacturas vf WHERE vf.nIdVentas = IFNULL(v2.nIdVentas, 0) LIMIT 1) AS tieneFacturaEnvio, ' .

                'IFNULL(t.nIdTraspaso, 0) AS nIdTraspaso, ' .
                'IFNULL(sv3.nIdSucursal, 0) AS nIdSucVOriTraspaso, ' .
                'IFNULL(sv3.sDescripcion, \'\') AS sucOriTraspaso '
        );
        $this->join('almovimientodetalle b', 'almovimiento.nIdMovimiento = b.nIdMovimiento', 'inner')
            ->join('alentrega e', 'almovimiento.cOrigen = \'entrega\' AND almovimiento.nIdOrigen = e.nIdEntrega', 'left', false)
            ->join('vtventas v', 'e.nIdOrigen = v.nIdVentas', 'left', false)
            ->join('vtcliente cl', 'v.nIdCliente = cl.nIdCliente', 'left')
            ->join('alsucursal sv', 'v.nIdSucursal = sv.nIdSucursal', 'left')

            ->join('enenvio e2', 'almovimiento.cOrigen = \'envio\' AND almovimiento.nIdOrigen = e2.nIdEnvio', 'left', false)
            ->join('vtventas v2', 'e2.cOrigen = \'ventas\' AND e2.nIdOrigen = v2.nIdVentas', 'left', false)
            ->join('vtcliente cl2', 'v2.nIdCliente = cl2.nIdCliente', 'left')
            ->join('alsucursal sv2', 'v2.nIdSucursal = sv2.nIdSucursal', 'left')
            
            ->join('altraspaso t', 'e2.cOrigen = \'traspaso\' AND e2.nIdOrigen = t.nIdTraspaso', 'left', false)
            ->join('alsucursal sv3', 't.nIdSucursal = sv3.nIdSucursal', 'left')

            ->where([
                'almovimiento.dtAlta >=' => $fIni->format('Y-m-d'),
                'almovimiento.dtAlta <' => $fFin->add(new DateInterval('P1D'))->format('Y-m-d'),
                'b.nIdArticulo' => $idArt,
                'b.nIdSucursal' => $idSuc
            ]);
        $this->builder()->from('almovimiento FORCE INDEX (dtAlta)', true);
        $this->orderBy('b.nIdMovimientoDetalle', 'ASC');
        return $this->findAll();
    }
}
/***********
 * 
sub(new DateInterval('P60D'))
CREATE TABLE `almovimiento` (
  `nIdMovimiento` int(11) NOT NULL AUTO_INCREMENT,
  `nIdOrigen` int(11) DEFAULT NULL,
  `cOrigen` varchar(45) DEFAULT NULL COMMENT 'Puede ser de compras/entradas/traspasos almacen',
  `nIdUsuario` int(11) DEFAULT NULL,
  `dMovimento` date DEFAULT NULL,
  `sObservacion` varchar(500) DEFAULT NULL,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdMovimiento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 * 
 ***********/
