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
                'b.nIdSucursal, b.fCantidad, b.fSaldoInicial'
        );
        $this->join('almovimientodetalle b', 'almovimiento.nIdMovimiento = b.nIdMovimiento', 'inner')
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
