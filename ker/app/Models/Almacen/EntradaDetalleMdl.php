<?php

namespace App\Models\Almacen;

use CodeIgniter\Model;

class EntradaDetalleMdl extends Model
{
    protected $table = 'alentradadetalle';

    protected $allowedFields = ['nIdEntrada', 'nIdArticulo', 'fCantidad', 'fImporte', 'fRecibido', 'fPorRecibir'];

    protected $primaryKey = 'nIdEntradaDetalle';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function updtRecibidos($id, $idArt, $fRecibido)
    {
        $retVal = $this->set('fRecibido', 'fRecibido + ' . $fRecibido, false)
            ->set('fPorRecibir', 'fPorRecibir - ' . $fRecibido, false)
            ->where(['nIdEntrada' => $id, 'nIdArticulo' => $idArt])
            ->update();
        return $retVal;
    }

    public function getRegistros($id = 0, $paginado = 0)
    {
        $join1 = 'alarticulo a';
        $wjoin1 = 'a.nIdArticulo = alentradadetalle.nIdArticulo';
        if ($paginado === 0)
            return $this
                ->join($join1, $wjoin1, 'left')
                ->where(['nIdEntrada' =>  $id])
                ->findAll();
        else
            return $this
                ->join($join1, $wjoin1, 'left')
                ->where(['nIdEntrada' =>  $id])
                ->paginate($paginado);
    }

    public function getRegistrosByField($field = 'nIdEntrada', $val = false)
    {
        return $this->where([$field => $val])->findAll();
    }
}
/***********
 * 

CREATE TABLE `alentradadetalle` (
  `nIdEntradaDetalle` int(11) NOT NULL AUTO_INCREMENT,
  `nIdEntrada` int(11) DEFAULT NULL,
  `nIdArticulo` int(11) DEFAULT NULL,
  `fCantidad` decimal(10,2) DEFAULT 0.00,
  `fRecibido` decimal(10,2) DEFAULT 0.00,
  `fPorRecibir` decimal(10,2) DEFAULT 0.00,
  `fImporte` decimal(10,2) DEFAULT 0.00,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdEntradaDetalle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 * 
 ***********/
